<?php

namespace App\Services\AI;

use App\Models\SystemSetting;

class AiServiceFactory
{
    /**
     * Constrói o provedor de IA configurado.
     */
    public static function make(): ?AiProviderInterface
    {
        $provider = SystemSetting::getValue('ai_provider', 'gemini-studio');

        return match ($provider) {
            'vertex-ai' => new VertexAiProvider(),
            default     => new GeminiProvider(),
        };
    }
}
