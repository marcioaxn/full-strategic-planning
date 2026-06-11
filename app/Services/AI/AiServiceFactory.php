<?php

namespace App\Services\AI;

use App\Models\SystemSetting;

class AiServiceFactory
{
    /**
     * Constrói o provedor de IA configurado.
     * Retorna null quando nenhuma credencial está configurada,
     * permitindo que todos os callers operem sem o agente.
     */
    public static function make(): ?AiProviderInterface
    {
        $provider = SystemSetting::getValue('ai_provider', 'gemini-studio');

        return match ($provider) {
            'vertex-ai' => self::makeVertexProvider(),
            default     => self::makeGeminiProvider(),
        };
    }

    private static function makeGeminiProvider(): ?AiProviderInterface
    {
        $key = SystemSetting::getValue('ai_api_key');
        if (empty($key)) {
            return null;
        }

        return new GeminiProvider(
            $key,
            SystemSetting::getValue('ai_model', 'gemini-2.5-flash')
        );
    }

    private static function makeVertexProvider(): ?AiProviderInterface
    {
        $projectId = SystemSetting::getValue('vertex_project_id');
        $json      = SystemSetting::getValue('vertex_service_account_json');

        if (empty($projectId) || empty($json)) {
            return null;
        }

        return new VertexAiProvider();
    }
}
