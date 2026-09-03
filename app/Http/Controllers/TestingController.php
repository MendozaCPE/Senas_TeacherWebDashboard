<?php

namespace App\Http\Controllers;

use App\Models\Gesture;
use App\Models\GestureModule;
use App\Models\TestTrial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestingController extends Controller
{
    /**
     * Dynamic (LSTM/motion) sign names.
     */
    private const DYNAMIC_SIGN_NAMES = ['J', 'Z'];

    private const NUMBER_WORD_TO_DIGIT = [
        'ONE' => '1', 'TWO' => '2', 'THREE' => '3', 'FOUR' => '4', 'FIVE' => '5',
        'SIX' => '6', 'SEVEN' => '7', 'EIGHT' => '8', 'NINE' => '9', 'TEN' => '10',
    ];

    private function signType(string $name, ?string $moduleName = null): string
    {
        if (in_array(strtoupper(trim($name)), self::DYNAMIC_SIGN_NAMES, true)) {
            return 'dynamic';
        }
        if ($moduleName && in_array($moduleName, ['level1_numbers', 'level2_greetings', 'level3_survival'], true)) {
            return 'dynamic';
        }
        return 'static';
    }

    private function normalizeLabel(?string $label): ?string
    {
        if ($label === null || $label === '' || $label === '✋' || $label === '...') {
            return null;
        }

        $label = trim($label);
        $label = str_replace(["’", "‘", "`"], "'", $label);
        $upper = strtoupper($label);

        if (isset(self::NUMBER_WORD_TO_DIGIT[$upper])) {
            return self::NUMBER_WORD_TO_DIGIT[$upper];
        }

        return $upper;
    }

    private function applyModuleFilter($query, ?string $moduleSlug)
    {
        if (!$moduleSlug || $moduleSlug === 'all') {
            return $query;
        }

        if ($moduleSlug === 'alphabets') {
            return $query->whereIn('module', ['alphabet_part1', 'alphabet_part2']);
        } elseif ($moduleSlug === 'numbers') {
            return $query->where('module', 'level1_numbers');
        } elseif ($moduleSlug === 'greetings') {
            return $query->where('module', 'level2_greetings');
        } elseif ($moduleSlug === 'survival') {
            return $query->where('module', 'level3_survival');
        } else {
            return $query->where('module', $moduleSlug);
        }
    }

    /**
     * Build signs payload for testing grid.
     */
    private function buildSignsPayload(?string $moduleSlug = 'alphabets'): array
    {
        $query = Gesture::with(['media' => function ($q) {
            $q->orderByDesc('is_primary')->orderBy('order');
        }, 'module']);

        if ($moduleSlug && $moduleSlug !== 'all') {
            if ($moduleSlug === 'alphabets') {
                $moduleIds = GestureModule::whereIn('name', ['alphabet_part1', 'alphabet_part2'])->pluck('module_id');
                $query->whereIn('module_id', $moduleIds);
            } elseif ($moduleSlug === 'numbers') {
                $module = GestureModule::where('name', 'level1_numbers')->first();
                if ($module) {
                    $query->where('module_id', $module->module_id);
                }
            } elseif ($moduleSlug === 'greetings') {
                $module = GestureModule::where('name', 'level2_greetings')->first();
                if ($module) {
                    $query->where('module_id', $module->module_id);
                }
            } elseif ($moduleSlug === 'survival') {
                $module = GestureModule::where('name', 'level3_survival')->first();
                if ($module) {
                    $query->where('module_id', $module->module_id);
                }
            } else {
                $module = GestureModule::where('name', $moduleSlug)->first();
                if ($module) {
                    $query->where('module_id', $module->module_id);
                }
            }
        }

        return $query->orderBy('gesture_id')->get()->map(function ($g) {
            $trialCount = TestTrial::where('gesture_id', $g->gesture_id)->count();

            $primaryMedia = $g->media->firstWhere('is_primary', true) ?? $g->media->first();
            $mediaUrl = $primaryMedia
                ? asset('storage/' . $primaryMedia->file_path)
                : ($g->video_url ?: $g->image_url);
            $isVideo = $primaryMedia
                ? $primaryMedia->media_type === 'video'
                : (bool) $g->video_url && !$g->image_url;

            $moduleName = $g->module?->name;
            $signType = $this->signType($g->name, $moduleName);
            $label = $g->name;

            return [
                'gesture_id' => $g->gesture_id,
                'name' => $label,
                'sign_label' => $label,
                'display_name' => $g->display_name ?: $label,
                'sign_type' => $signType,
                'reference_media_path' => $mediaUrl,
                'reference_is_video' => $isVideo,
                'trial_count' => $trialCount,
                'module_id' => $g->module_id,
                'module_name' => $moduleName,
                'module_title' => $g->module?->display_name,
            ];
        })->values()->toArray();
    }

    /**
     * GET /admin/testing/alphabet
     */
    public function alphabetPage(Request $request)
    {
        $currentModule = $request->query('module', 'alphabets');
        $totalTrials = TestTrial::count();

        return view('testing.alphabet-test', [
            'initialSigns' => $this->buildSignsPayload($currentModule),
            'currentModule' => $currentModule,
            'totalTrials' => $totalTrials,
        ]);
    }

    /**
     * GET /admin/api/testing/signs?module=alphabets
     */
    public function signs(Request $request)
    {
        $moduleSlug = $request->query('module', 'alphabets');
        return response()->json(['signs' => $this->buildSignsPayload($moduleSlug)]);
    }

    /**
     * GET /admin/api/testing/trials?gesture_id=1
     */
    public function trials(Request $request)
    {
        $request->validate(['gesture_id' => 'required|integer|exists:gestures,gesture_id']);

        $trials = TestTrial::where('gesture_id', $request->query('gesture_id'))
            ->orderBy('signer_id')
            ->orderBy('trial_number')
            ->get([
                'id', 'signer_id', 'trial_number', 'predicted_label',
                'confidence_score', 'is_correct', 'response_latency_ms', 'created_at',
            ]);

        return response()->json(['trials' => $trials]);
    }

    /**
     * POST /admin/api/testing/trials
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gesture_id' => 'required|integer|exists:gestures,gesture_id',
            'signer_id' => 'required|string|max:100',
            'landmark_data' => 'required|array',
            'predicted_label' => 'nullable|string|max:100',
            'confidence_score' => 'nullable|numeric|min:0|max:1',
            'response_latency_ms' => 'nullable|integer|min:0',
            'capture_started_at' => 'nullable|date',
            'feedback_received_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $gesture = Gesture::findOrFail($request->gesture_id);
        $module = $gesture->module;

        $nextTrialNumber = TestTrial::where('gesture_id', $gesture->gesture_id)
            ->where('signer_id', $request->signer_id)
            ->max('trial_number');
        $nextTrialNumber = ($nextTrialNumber ?? 0) + 1;

        $cleanedPredicted = $request->predicted_label ? trim($request->predicted_label) : null;
        $normalizedPred = $this->normalizeLabel($cleanedPredicted);
        $normalizedTrue = $this->normalizeLabel($gesture->name);

        $isCorrect = $normalizedPred !== null
            && $normalizedTrue !== null
            && $normalizedPred === $normalizedTrue;

        $trial = TestTrial::create([
            'gesture_id' => $gesture->gesture_id,
            'true_label' => $normalizedTrue ?? $gesture->name,
            'module' => $module->name ?? null,
            'sign_type' => $this->signType($gesture->name, $module->name ?? null),
            'signer_id' => $request->signer_id,
            'trial_number' => $nextTrialNumber,
            'landmark_data' => $request->landmark_data,
            'predicted_label' => $normalizedPred,
            'confidence_score' => $request->confidence_score,
            'is_correct' => $isCorrect,
            'response_latency_ms' => $request->response_latency_ms,
            'capture_started_at' => $request->capture_started_at,
            'feedback_received_at' => $request->feedback_received_at,
        ]);

        return response()->json(['trial' => $trial], 201);
    }

    /**
     * GET /admin/api/testing/export?module=alphabets
     */
    public function export(Request $request)
    {
        $moduleSlug = $request->query('module', 'all');

        $query = TestTrial::query();
        $this->applyModuleFilter($query, $moduleSlug);

        $rows = $query->orderBy('gesture_id')
            ->orderBy('signer_id')
            ->orderBy('trial_number')
            ->get([
                'id', 'gesture_id', 'true_label', 'module', 'sign_type', 'signer_id', 'trial_number',
                'predicted_label', 'confidence_score', 'is_correct',
                'response_latency_ms', 'created_at',
            ]);

        return response()->json(['module' => $moduleSlug, 'trials' => $rows]);
    }

    /**
     * GET /admin/api/testing/metrics
     * Calculates Accuracy, Precision, Recall, F1-Score, Latencies, and Confusion Matrix.
     */
    public function metrics(Request $request)
    {
        $moduleSlug = $request->query('module', 'all');
        $query = TestTrial::query();
        $this->applyModuleFilter($query, $moduleSlug);

        $trials = $query->get();
        $totalTrials = $trials->count();

        if ($totalTrials === 0) {
            return response()->json([
                'total_trials' => 0,
                'accuracy' => 0,
                'latency' => ['overall_avg' => 0, 'min' => 0, 'max' => 0, 'static_avg' => 0, 'dynamic_avg' => 0],
                'per_class' => [],
                'macro' => ['precision' => 0, 'recall' => 0, 'f1' => 0],
                'confusion_matrix' => ['labels' => [], 'matrix' => []],
            ]);
        }

        $correctCount = $trials->where('is_correct', true)->count();
        $overallAccuracy = round(($correctCount / $totalTrials) * 100, 2);

        // Latency calculations
        $validLatencies = $trials->whereNotNull('response_latency_ms')->pluck('response_latency_ms');
        $overallAvgLatency = $validLatencies->count() > 0 ? round($validLatencies->avg(), 1) : 0;
        $minLatency = $validLatencies->count() > 0 ? $validLatencies->min() : 0;
        $maxLatency = $validLatencies->count() > 0 ? $validLatencies->max() : 0;

        $staticLatencies = $trials->where('sign_type', 'static')->whereNotNull('response_latency_ms')->pluck('response_latency_ms');
        $staticAvgLatency = $staticLatencies->count() > 0 ? round($staticLatencies->avg(), 1) : 0;

        $dynamicLatencies = $trials->where('sign_type', 'dynamic')->whereNotNull('response_latency_ms')->pluck('response_latency_ms');
        $dynamicAvgLatency = $dynamicLatencies->count() > 0 ? round($dynamicLatencies->avg(), 1) : 0;

        // Distinct labels
        $trueLabels = $trials->pluck('true_label')->filter()->unique()->values()->all();
        $predLabels = $trials->pluck('predicted_label')->filter()->unique()->values()->all();
        $allLabels = array_values(array_unique(array_merge($trueLabels, $predLabels)));
        sort($allLabels);

        // Confusion Matrix initialization
        $confusionMatrix = [];
        foreach ($allLabels as $true) {
            $confusionMatrix[$true] = [];
            foreach ($allLabels as $pred) {
                $confusionMatrix[$true][$pred] = 0;
            }
            $confusionMatrix[$true]['No Detection'] = 0;
        }

        foreach ($trials as $t) {
            $true = $t->true_label;
            $pred = $t->predicted_label ?? 'No Detection';
            if (!isset($confusionMatrix[$true][$pred])) {
                $confusionMatrix[$true][$pred] = 0;
            }
            $confusionMatrix[$true][$pred]++;
        }

        // Per-class Precision, Recall, F1
        $perClass = [];
        $precisionSum = 0;
        $recallSum = 0;
        $f1Sum = 0;
        $classCount = count($trueLabels);

        foreach ($trueLabels as $label) {
            $tp = $trials->filter(fn($t) => $t->true_label === $label && $t->predicted_label === $label)->count();
            $fp = $trials->filter(fn($t) => $t->true_label !== $label && $t->predicted_label === $label)->count();
            $fn = $trials->filter(fn($t) => $t->true_label === $label && $t->predicted_label !== $label)->count();
            $support = $trials->where('true_label', $label)->count();

            $precision = ($tp + $fp) > 0 ? round(($tp / ($tp + $fp)) * 100, 2) : 0;
            $recall = ($tp + $fn) > 0 ? round(($tp / ($tp + $fn)) * 100, 2) : 0;
            $f1 = ($precision + $recall) > 0 ? round((2 * ($precision * $recall)) / ($precision + $recall), 2) : 0;

            $perClass[$label] = [
                'tp' => $tp,
                'fp' => $fp,
                'fn' => $fn,
                'support' => $support,
                'precision' => $precision,
                'recall' => $recall,
                'f1' => $f1,
            ];

            $precisionSum += $precision;
            $recallSum += $recall;
            $f1Sum += $f1;
        }

        $macroPrecision = $classCount > 0 ? round($precisionSum / $classCount, 2) : 0;
        $macroRecall = $classCount > 0 ? round($recallSum / $classCount, 2) : 0;
        $macroF1 = $classCount > 0 ? round($f1Sum / $classCount, 2) : 0;

        return response()->json([
            'total_trials' => $totalTrials,
            'correct_count' => $correctCount,
            'accuracy' => $overallAccuracy,
            'latency' => [
                'overall_avg' => $overallAvgLatency,
                'min' => $minLatency,
                'max' => $maxLatency,
                'static_avg' => $staticAvgLatency,
                'dynamic_avg' => $dynamicAvgLatency,
            ],
            'macro' => [
                'precision' => $macroPrecision,
                'recall' => $macroRecall,
                'f1' => $macroF1,
            ],
            'per_class' => $perClass,
            'confusion_matrix' => [
                'labels' => $allLabels,
                'matrix' => $confusionMatrix,
            ],
        ]);
    }

    /**
     * GET /admin/api/testing/export-csv
     */
    public function exportCsv(Request $request)
    {
        $moduleSlug = $request->query('module', 'all');
        $query = TestTrial::query();
        $this->applyModuleFilter($query, $moduleSlug);

        $trials = $query->orderBy('gesture_id')
            ->orderBy('signer_id')
            ->orderBy('trial_number')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="senas_testing_trials_' . $moduleSlug . '_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function () use ($trials) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Trial ID',
                'Gesture ID',
                'True Label',
                'Module',
                'Sign Type',
                'Signer ID',
                'Trial Number',
                'Predicted Label',
                'Confidence Score',
                'Is Correct',
                'Response Latency (ms)',
                'Recorded At',
            ]);

            foreach ($trials as $t) {
                fputcsv($file, [
                    $t->id,
                    $t->gesture_id,
                    $t->true_label,
                    $t->module,
                    $t->sign_type,
                    $t->signer_id,
                    $t->trial_number,
                    $t->predicted_label ?? 'None',
                    $t->confidence_score,
                    $t->is_correct ? 'TRUE' : 'FALSE',
                    $t->response_latency_ms,
                    $t->created_at,
                ]);
            }
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}