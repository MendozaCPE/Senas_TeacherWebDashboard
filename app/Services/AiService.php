<?php

namespace App\Services;

/**
 * AiService — Factory / Router
 *
 * Selects the correct AI backend based on the AI_PROVIDER .env variable.
 *
 * Usage in .env:
 *   AI_PROVIDER=deepseek   → uses DeepSeekService (default)
 *   AI_PROVIDER=gemini     → uses GeminiService
 *
 * All returned service instances share the same public interface:
 *   generate(array $params): array
 *   generateQuizOnly(string $contentText, int $numMc, int $numTf, int $numDd, int $numGt): array
 *   generateFromPdfText(string $pdfText, array $params): array
 */
class AiService
{
    /**
     * Returns the configured AI service instance.
     *
     * @return DeepSeekService|GeminiService
     * @throws \RuntimeException if an unknown provider is configured
     */
    public static function make(): DeepSeekService|GeminiService
    {
        $provider = strtolower(trim(env('AI_PROVIDER', 'deepseek')));

        return match ($provider) {
            'gemini'   => new GeminiService(),
            'deepseek' => new DeepSeekService(),
            default    => throw new \RuntimeException(
                "Unknown AI_PROVIDER \"{$provider}\" in .env. Valid options: deepseek, gemini"
            ),
        };
    }

    /**
     * Returns the human-readable name of the active provider.
     */
    public static function providerName(): string
    {
        $provider = strtolower(trim(env('AI_PROVIDER', 'deepseek')));

        return match ($provider) {
            'gemini'   => 'Gemini',
            'deepseek' => 'DeepSeek',
            default    => ucfirst($provider),
        };
    }
}
