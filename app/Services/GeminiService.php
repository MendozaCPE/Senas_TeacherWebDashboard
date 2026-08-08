<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * GeminiService
 *
 * Drop-in replacement for DeepSeekService that calls Google Gemini
 * via the REST API (generateContent endpoint).
 *
 * Get your API key at: https://aistudio.google.com/app/apikey
 *
 * .env keys:
 *   GEMINI_API_KEY=your_key_here
 *   GEMINI_MODEL=gemini-1.5-flash          (default)
 */
class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', '');
        $this->model  = config('services.gemini.model', 'gemini-2.0-flash');
    }

    // -------------------------------------------------------------------------
    // Public API — same signatures as DeepSeekService
    // -------------------------------------------------------------------------

    /**
     * Generate a structured lesson plan via Gemini.
     *
     * @param  array  $params  Keys: topic, difficulty, lesson_type, num_slides, special_instructions
     * @return array  Structured lesson array
     * @throws \RuntimeException
     */
    public function generate(array $params): array
    {
        $systemPrompt = $this->buildSystemPrompt($params);
        $userPrompt   = $this->buildUserPrompt($params);

        $rawContent = $this->callGemini($systemPrompt, $userPrompt, 8192, 90);

        $lesson = $this->parseJson($rawContent, 'Gemini');
        $this->validateLessonStructure($lesson, $params['num_slides']);

        return $lesson;
    }

    /**
     * Generate quiz questions ONLY from lesson content text.
     *
     * @param  string  $contentText
     * @param  int     $numMc
     * @param  int     $numTf
     * @param  int     $numDd
     * @param  int     $numGt
     * @return array   Array of quiz question objects
     */
    public function generateQuizOnly(string $contentText, int $numMc, int $numTf, int $numDd = 0, int $numGt = 0): array
    {
        $total = $numMc + $numTf + $numDd + $numGt;

        $systemPrompt = $this->buildQuizOnlySystemPrompt($numMc, $numTf, $numDd, $numGt, $total);
        $userPrompt   = "Generate {$total} quiz questions ({$numMc} multiple choice, {$numTf} true/false, {$numDd} drag/drop, {$numGt} gesture) based on the following lesson content:\n\n---\n{$contentText}\n---";

        $rawContent = $this->callGemini($systemPrompt, $userPrompt, 3000, 60);
        $data = $this->parseJson($rawContent, 'Gemini');

        if (empty($data['quiz']) || !is_array($data['quiz'])) {
            throw new \RuntimeException('Gemini returned no quiz questions. Please try again.');
        }

        return $data['quiz'];
    }

    /**
     * Generate a lesson from raw PDF text.
     *
     * @param  string  $pdfText
     * @param  array   $params    Keys: difficulty, lesson_type, num_slides, instructions
     * @return array   Structured lesson array
     */
    public function generateFromPdfText(string $pdfText, array $params): array
    {
        $pdfText = mb_substr($pdfText, 0, 12000);

        $systemPrompt = $this->buildSystemPrompt($params);

        $extra = !empty($params['instructions']) ? $params['instructions'] : 'None';

        $numMc = (int) ($params['num_mc'] ?? 3);
        $numTf = (int) ($params['num_tf'] ?? 2);
        $numDd = (int) ($params['num_dd'] ?? 0);
        $numGt = (int) ($params['num_gt'] ?? 0);
        $totalQuestions = $numMc + $numTf + $numDd + $numGt;

        $userPrompt = <<<TEXT
Generate a complete lesson from the following document content.
Adapt it for learners — keep language simple, friendly, and encouraging.

Difficulty: {$params['difficulty']}
Lesson Type: {$params['lesson_type']}
Number of slides: {$params['num_slides']}
Additional instructions: {$extra}

--- DOCUMENT CONTENT START ---
{$pdfText}
--- DOCUMENT CONTENT END ---

Capture the key ideas from the document and turn them into engaging lesson slides and {$totalQuestions} quiz questions.
TEXT;

        $rawContent = $this->callGemini($systemPrompt, $userPrompt, 4096, 90);
        $lesson = $this->parseJson($rawContent, 'Gemini');
        $this->validateLessonStructure($lesson, (int) $params['num_slides']);

        return $lesson;
    }

    // -------------------------------------------------------------------------
    // Prompt builders
    // -------------------------------------------------------------------------

    private function buildSystemPrompt(array $params = []): string
    {
        $numMc = isset($params['num_mc']) ? (int) $params['num_mc'] : 3;
        $numTf = isset($params['num_tf']) ? (int) $params['num_tf'] : 2;
        $numDd = isset($params['num_dd']) ? (int) $params['num_dd'] : 0;
        $numGt = isset($params['num_gt']) ? (int) $params['num_gt'] : 0;
        $totalQuestions = $numMc + $numTf + $numDd + $numGt;

        return <<<PROMPT
You are an expert curriculum designer for the SENAS learning app. Generate complete, structured lesson plans on any topic requested by the teacher.

You can generate lessons about ANY subject: Filipino Sign Language (FSL), Science, Math, History, English, Arts, Health, Technology, or any other topic. Do not limit yourself to FSL — create rich educational content for whatever topic is given.

If the topic is FSL or Sign Language related, include gesture_name values in snake_case (e.g. "letter_a", "hello"). For all other topics, set gesture_name to null.

Respond with ONLY valid JSON — no markdown, no explanation, just raw JSON.

Schema:
{
  "title": string,
  "description": string,
  "lesson_type": "gesture"|"text"|"video"|"interactive",
  "difficulty": "beginner"|"intermediate"|"advanced",
  "contents": [
    {
      "step_number": number,
      "content_type": "text"|"image"|"video"|"gesture_demo",
      "title": string,
      "content_text": string,
      "gesture_name": string|null
    }
  ],
  "quiz": [
    {
      "question": string,
      "type": "multiple_choice"|"true_false"|"drag_drop"|"gesture",
      "options": [string, string, string, string],
      "correct_index": number,
      "drag_drop_pairs": [
        {
          "left_text": string,
          "right_text": string
        }
      ],
      "gesture_names": [string]
    }
  ]
}

Rules:
- content_text must be educational, encouraging, and clear — use simple words, short sentences, positive tone
- difficulty: beginner = foundational concepts, intermediate = applied knowledge, advanced = complex topics/synthesis
- Generate EXACTLY the number of content steps requested.
- Generate EXACTLY {$totalQuestions} quiz questions:
  - Generate EXACTLY {$numMc} of type "multiple_choice" (each must have exactly 4 options, and correct_index must be the 0-based index of correct option)
  - Generate EXACTLY {$numTf} of type "true_false" (options must be exactly ["True", "False"], and correct_index must be 0 or 1)
  - Generate EXACTLY {$numDd} of type "drag_drop" (each must have a "drag_drop_pairs" array with at least 2 pairs and up to 5 pairs. Options/correct_index must be null/empty)
  - Generate EXACTLY {$numGt} of type "gesture" (each must have a "gesture_names" array containing FSL letters A-Z or numbers 1-10 that students need to perform, e.g. ["A", "B"] or ["5"]. Options/correct_index must be null/empty)
- Make quizzes fun and age-appropriate for school children
PROMPT;
    }

    private function buildUserPrompt(array $params): string
    {
        $special = !empty($params['special_instructions'])
            ? $params['special_instructions']
            : 'None';

        return implode("\n", [
            "Topic: {$params['topic']}",
            "Difficulty: {$params['difficulty']}",
            "Lesson Type: {$params['lesson_type']}",
            "Number of slides: {$params['num_slides']}",
            "Special instructions: {$special}",
        ]);
    }

    private function buildQuizOnlySystemPrompt(int $numMc, int $numTf, int $numDd, int $numGt, int $total): string
    {
        return <<<PROMPT
You are an expert quiz designer for the SENAS learning app.
You will receive lesson content written by a teacher, and your job is to generate quiz questions based ONLY on that content.

CRITICAL RULES:
- Only generate questions about concepts present in the content.
- Do NOT invent information not present in the lesson content.
- All questions must be age-appropriate for school children.
- Respond with ONLY valid JSON — no markdown, no explanation.

Output schema:
{
  "quiz": [
    {
      "question": string,
      "type": "multiple_choice"|"true_false"|"drag_drop"|"gesture",
      "options": [string, ...],
      "correct_index": number,
      "drag_drop_pairs": [
        {
          "left_text": string,
          "right_text": string
        }
      ],
      "gesture_names": [string]
    }
  ]
}

Rules:
- Generate EXACTLY {$total} quiz questions.
- Generate EXACTLY {$numMc} of type "multiple_choice" (each must have exactly 4 options, correct_index must be 0-3).
- Generate EXACTLY {$numTf} of type "true_false" (options must be exactly ["True","False"], correct_index must be 0 or 1).
- Generate EXACTLY {$numDd} of type "drag_drop" (each must have a "drag_drop_pairs" array with at least 2 pairs and up to 5 pairs).
- Generate EXACTLY {$numGt} of type "gesture" (each must have a "gesture_names" array containing FSL letters A-Z or numbers 1-10 that students need to perform, e.g. ["A", "B"] or ["5"]). Options/correct_index must be null/empty
- Make questions test understanding of the lesson content, not just memorization.
PROMPT;
    }

    // -------------------------------------------------------------------------
    // HTTP call to Gemini REST API
    // -------------------------------------------------------------------------

    /**
     * Call Gemini generateContent endpoint and return the raw text response.
     */
    private function callGemini(string $systemPrompt, string $userPrompt, int $maxTokens, int $timeout): string
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file. Get your key at: https://aistudio.google.com/app/apikey');
        }

        $endpoint = "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        $response = Http::timeout($timeout)
            ->post($endpoint, [
                'system_instruction' => [
                    'parts' => [['text' => $systemPrompt]],
                ],
                'contents' => [
                    [
                        'role'  => 'user',
                        'parts' => [['text' => $userPrompt]],
                    ],
                ],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => $maxTokens,
                    'responseMimeType' => 'application/json',
                ],
            ]);

        if ($response->failed()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            $this->handleApiError($response);
        }

        $data = $response->json();

        // Gemini response: candidates[0].content.parts[0].text
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (empty($text)) {
            // Check for safety block
            $finishReason = $data['candidates'][0]['finishReason'] ?? '';
            if ($finishReason === 'SAFETY') {
                throw new \RuntimeException('Gemini blocked the response due to safety filters. Please modify your topic or instructions and try again.');
            }
            throw new \RuntimeException('Gemini returned an empty response. Please try again.');
        }

        return $text;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function parseJson(string $rawContent, string $provider): array
    {
        // Strip markdown code fences if present
        $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
        $rawContent = preg_replace('/\s*```$/', '', $rawContent);

        $data = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("{$provider} JSON parse error", ['raw' => $rawContent]);
            throw new \RuntimeException("Could not parse AI response as JSON: " . json_last_error_msg());
        }

        return $data;
    }

    private function validateLessonStructure(array $lesson, int $expectedSlides): void
    {
        $required = ['title', 'description', 'lesson_type', 'difficulty', 'contents', 'quiz'];

        foreach ($required as $key) {
            if (!isset($lesson[$key])) {
                throw new \RuntimeException("AI response missing required field: '{$key}'.");
            }
        }

        if (!is_array($lesson['contents']) || count($lesson['contents']) < 1) {
            throw new \RuntimeException('AI response has no content slides.');
        }

        if (!is_array($lesson['quiz']) || count($lesson['quiz']) < 1) {
            throw new \RuntimeException('AI response has no quiz questions.');
        }
    }

    private function handleApiError($response): void
    {
        $status = $response->status();
        $json   = $response->json();
        $errMsg = $json['error']['message'] ?? '';

        if ($status === 400) {
            throw new \RuntimeException('Gemini API bad request (HTTP 400): ' . ($errMsg ?: 'Check your request parameters.'));
        }

        if ($status === 401 || $status === 403) {
            throw new \RuntimeException('Gemini API key is invalid or unauthorized (HTTP ' . $status . '). Please check your GEMINI_API_KEY in .env. Get your key at: https://aistudio.google.com/app/apikey');
        }

        if ($status === 429) {
            throw new \RuntimeException('Gemini API rate limit reached (HTTP 429). Please wait a moment before trying again.');
        }

        $msg = !empty($errMsg) ? $errMsg : 'HTTP ' . $status;
        throw new \RuntimeException("Gemini API error ({$msg}). Please try again.");
    }
}
