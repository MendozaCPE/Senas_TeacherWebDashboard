<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('services.deepseek.api_key');
        $this->model   = config('services.deepseek.model', 'deepseek/deepseek-chat');
        $this->baseUrl = rtrim(config('services.deepseek.base_url', 'https://openrouter.ai/api/v1'), '/');
    }

    /**
     * Generate a structured FSL lesson plan via DeepSeek.
     *
     * @param  array  $params  Keys: topic, difficulty, lesson_type, num_slides, special_instructions
     * @return array  Structured lesson array
     * @throws \RuntimeException
     */
    public function generate(array $params): array
    {
        $systemPrompt = $this->buildSystemPrompt($params);
        $userPrompt   = $this->buildUserPrompt($params);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url'),
            'X-Title'       => 'SENAS Teacher Dashboard',
        ])
        ->timeout(90)
        ->post("{$this->baseUrl}/chat/completions", [
            'model'           => $this->model,
            'messages'        => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.7,
            'max_tokens'      => 8000,
        ]);

        if ($response->failed()) {
            Log::error('DeepSeek API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            $this->handleApiError($response);
        }

        $responseData = $response->json();
        $rawContent   = $responseData['choices'][0]['message']['content'] ?? null;

        if (empty($rawContent)) {
            throw new \RuntimeException('DeepSeek returned an empty response. Please try again.');
        }

        // Strip markdown code fences if present
        $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
        $rawContent = preg_replace('/\s*```$/', '', $rawContent);

        $lesson = json_decode($rawContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('DeepSeek JSON parse error', ['raw' => $rawContent]);
            throw new \RuntimeException('Could not parse AI response as JSON: ' . json_last_error_msg());
        }

        $this->validateLessonStructure($lesson, $params['num_slides']);

        return $lesson;
    }

    private function buildSystemPrompt(array $params = []): string
    {
        $numMc = isset($params['num_mc']) ? (int) $params['num_mc'] : 3;
        $numTf = isset($params['num_tf']) ? (int) $params['num_tf'] : 2;
        $numDd = isset($params['num_dd']) ? (int) $params['num_dd'] : 0;
        $numGt = isset($params['num_gt']) ? (int) $params['num_gt'] : 0;
        $totalQuestions = $numMc + $numTf + $numDd + $numGt;

        return <<<PROMPT
You are an expert Filipino Sign Language (FSL) curriculum designer for the SENAS learning app. Generate complete, structured FSL lesson plans for children.

CRITICAL: You must ONLY generate lesson content and quiz questions that focus on Filipino Sign Language (FSL). Do NOT include ASL (American Sign Language), BSL (British Sign Language), or any other sign/spoken language. All sign names, alphabet signs, vocabulary, and grammar must be FSL-specific.
If the topic requested is general or unrelated to FSL (e.g. "Science", "Math", "Animals"), you MUST adapt it to teach FSL vocabulary and FSL signs for the terms related to that topic (e.g., teach FSL signs for "dog", "cat", "fish" if the topic is Animals). Do not teach non-FSL concepts.

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
- gesture_name must be snake_case (e.g. "letter_a", "hello", "thank_you")
- content_text must be educational, encouraging, and clear for deaf/HoH learners — use simple words, short sentences, and positive tone
- difficulty: beginner = alphabet/numbers/basic signs, intermediate = common phrases, advanced = full sentences/conversations
- Generate EXACTLY the number of content steps requested.
- Generate EXACTLY {$totalQuestions} quiz questions:
  - Generate EXACTLY {$numMc} of type "multiple_choice" (each must have exactly 4 options, and correct_index must be the 0-based index of correct option)
  - Generate EXACTLY {$numTf} of type "true_false" (options must be exactly ["True", "False"], and correct_index must be 0 or 1)
  - Generate EXACTLY {$numDd} of type "drag_drop" (each must have a "drag_drop_pairs" array with at least 2 pairs and up to 5 pairs, mapping left items to right match items. Options/correct_index must be null/empty)
  - Generate EXACTLY {$numGt} of type "gesture" (each must have a "gesture_names" array containing FSL letters A-Z or numbers 1-10 that students need to perform, e.g. ["A", "B"] or ["5"]. Options/correct_index must be null/empty)
- Make quizzes fun and age-appropriate for school children learning FSL
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

    /**
     * Generate quiz questions ONLY from lesson content text.
     * Used by the "Generate Quiz with AI" button in the manual lesson creator.
     *
     * @param  string  $contentText  The lesson content text entered by the teacher
     * @param  int     $numMc        Number of multiple-choice questions
     * @param  int     $numTf        Number of true/false questions
     * @return array   Array of quiz question objects
     */
    public function generateQuizOnly(string $contentText, int $numMc, int $numTf, int $numDd = 0, int $numGt = 0): array
    {
        $total = $numMc + $numTf + $numDd + $numGt;

        $systemPrompt = <<<PROMPT
You are an expert Filipino Sign Language (FSL) quiz designer for the SENAS learning app.
You will receive lesson content written by a teacher, and your job is to generate quiz questions based ONLY on that content.

CRITICAL RULES:
- Only generate questions about Filipino Sign Language (FSL) concepts present in the content.
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
- Generate EXACTLY {$numDd} of type "drag_drop" (each must have a "drag_drop_pairs" array with at least 2 pairs and up to 5 pairs, mapping left items to right match items).
- Generate EXACTLY {$numGt} of type "gesture" (each must have a "gesture_names" array containing FSL letters A-Z or numbers 1-10 that students need to perform, e.g. ["A", "B"] or ["5"]). Options/correct_index must be null/empty
- Make questions test understanding of the lesson content, not just memorization.
PROMPT;

        $userPrompt = "Generate {$total} quiz questions ({$numMc} multiple choice, {$numTf} true/false, {$numDd} drag/drop, {$numGt} gesture) based on the following lesson content:\n\n---\n{$contentText}\n---";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url'),
            'X-Title'       => 'SENAS Teacher Dashboard',
        ])
        ->timeout(60)
        ->post("{$this->baseUrl}/chat/completions", [
            'model'           => $this->model,
            'messages'        => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.6,
            'max_tokens'      => 3000,
        ]);

        if ($response->failed()) {
            Log::error('DeepSeek Quiz-Only API error', ['status' => $response->status(), 'body' => $response->body()]);
            $this->handleApiError($response);
        }

        $rawContent = $response->json()['choices'][0]['message']['content'] ?? null;
        if (empty($rawContent)) {
            throw new \RuntimeException('DeepSeek returned an empty response. Please try again.');
        }

        $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
        $rawContent = preg_replace('/\s*```$/', '', $rawContent);

        $data = json_decode($rawContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Could not parse AI response as JSON: ' . json_last_error_msg());
        }

        if (empty($data['quiz']) || !is_array($data['quiz'])) {
            throw new \RuntimeException('AI returned no quiz questions. Please try again.');
        }

        return $data['quiz'];
    }

    /**
     * Generate a lesson from raw PDF text extracted by the controller.
     *
     * @param  string  $pdfText   Extracted plain text from the PDF
     * @param  array   $params    Keys: difficulty, lesson_type, num_slides, instructions
     * @return array   Structured lesson array
     */
    public function generateFromPdfText(string $pdfText, array $params): array
    {
        // Trim to ~12 000 chars so we don't blow the context window
        $pdfText = mb_substr($pdfText, 0, 12000);

        $systemPrompt = $this->buildSystemPrompt($params);

        $extra = !empty($params['instructions']) ? $params['instructions'] : 'None';

        $numMc = (int) ($params['num_mc'] ?? 3);
        $numTf = (int) ($params['num_tf'] ?? 2);
        $numDd = (int) ($params['num_dd'] ?? 0);
        $numGt = (int) ($params['num_gt'] ?? 0);
        $totalQuestions = $numMc + $numTf + $numDd + $numGt;

        $userPrompt = <<<TEXT
Generate a complete FSL lesson from the following document content.
Adapt it for children learning Filipino Sign Language — keep language simple, friendly, and encouraging.

Difficulty: {$params['difficulty']}
Lesson Type: {$params['lesson_type']}
Number of slides: {$params['num_slides']}
Additional instructions: {$extra}

--- DOCUMENT CONTENT START ---
{$pdfText}
--- DOCUMENT CONTENT END ---

Capture the key ideas from the document and turn them into engaging FSL lesson slides and {$totalQuestions} quiz questions.
TEXT;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url'),
            'X-Title'       => 'SENAS Teacher Dashboard',
        ])
        ->timeout(90)
        ->post("{$this->baseUrl}/chat/completions", [
            'model'           => $this->model,
            'messages'        => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.7,
            'max_tokens'      => 4000,
        ]);

        if ($response->failed()) {
            Log::error('DeepSeek PDF API error', ['status' => $response->status(), 'body' => $response->body()]);
            $this->handleApiError($response);
        }

        $rawContent = $response->json()['choices'][0]['message']['content'] ?? null;
        if (empty($rawContent)) {
            throw new \RuntimeException('DeepSeek returned an empty response. Please try again.');
        }

        $rawContent = preg_replace('/^```(?:json)?\s*/i', '', trim($rawContent));
        $rawContent = preg_replace('/\s*```$/', '', $rawContent);

        $lesson = json_decode($rawContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Could not parse AI response as JSON: ' . json_last_error_msg());
        }

        $this->validateLessonStructure($lesson, (int) $params['num_slides']);

        return $lesson;
    }

    /**
     * Throw user-friendly exception based on HTTP status code.
     */
    private function handleApiError($response): void
    {
        $status = $response->status();
        $json   = $response->json();
        $errMsg = $json['error']['message'] ?? '';

        if ($status === 401) {
            throw new \RuntimeException('DeepSeek API key is invalid or unauthorized (HTTP 401). Please check your DEEPSEEK_API_KEY in .env.');
        }

        if ($status === 402 || str_contains(strtolower($errMsg), 'insufficient balance')) {
            throw new \RuntimeException('DeepSeek API Insufficient Balance (HTTP 402). Please top up your balance at platform.deepseek.com.');
        }

        if ($status === 429) {
            throw new \RuntimeException('DeepSeek API rate limit reached (HTTP 429). Please wait a moment before trying again.');
        }

        $msg = !empty($errMsg) ? $errMsg : 'HTTP ' . $status;
        throw new \RuntimeException("DeepSeek API error ({$msg}). Please try again.");
    }
}
