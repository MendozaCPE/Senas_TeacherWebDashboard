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
        $systemPrompt = $this->buildSystemPrompt();
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
            throw new \RuntimeException(
                'DeepSeek API returned HTTP ' . $response->status() . '. Please try again.'
            );
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

    private function buildSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an expert Filipino Sign Language (FSL) curriculum designer for the SENAS learning app. Generate complete, structured FSL lesson plans for children.

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
      "type": "multiple_choice"|"true_false",
      "options": [string, string, string, string],
      "correct_index": number
    }
  ]
}

Rules:
- gesture_name must be snake_case (e.g. "letter_a", "hello", "thank_you")
- content_text must be educational, encouraging, and clear for deaf/HoH learners — use simple words, short sentences, and positive tone
- difficulty: beginner = alphabet/numbers/basic signs, intermediate = common phrases, advanced = full sentences/conversations
- Generate EXACTLY the number of content steps requested and EXACTLY 5 quiz questions
- Mix quiz types: use both "multiple_choice" (4 options) and "true_false" (options: ["True","False"], correct_index 0 or 1)
- For true_false questions, options must be exactly ["True", "False"]
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

        $systemPrompt = $this->buildSystemPrompt();

        $extra = !empty($params['instructions']) ? $params['instructions'] : 'None';

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

Capture the key ideas from the document and turn them into engaging FSL lesson slides and 5 quiz questions.
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
            throw new \RuntimeException('DeepSeek API returned HTTP ' . $response->status() . '. Please try again.');
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
}
