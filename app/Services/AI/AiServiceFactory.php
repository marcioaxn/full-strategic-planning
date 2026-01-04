<?php

namespace App\Services\AI;

use App\Models\SystemSetting;

class AiServiceFactory
{
    /**
     * Build the configured AI provider.
     */
    public static function make(): ?AiProviderInterface
    {
        $enabled = SystemSetting::getValue('ai_enabled', true);
        if (!$enabled) {
            return null;
        }

        $provider = SystemSetting::getValue('ai_provider', 'gemini');
        $apiKey = SystemSetting::getValue('ai_api_key');

        return match ($provider) {
            'gemini' => new GeminiProvider($apiKey),
            'openai' => new OpenAiProvider($apiKey),
            default => new GeminiProvider($apiKey),
        };
    }
}
