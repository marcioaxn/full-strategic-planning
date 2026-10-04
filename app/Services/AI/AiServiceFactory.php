<?php

namespace App\Services\AI;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\RateLimiter;

class AiServiceFactory
{
    /** Chamadas à IA por usuário por minuto. */
    public const LIMITE_POR_MINUTO = 20;

    /**
     * Constrói o provedor de IA configurado.
     * Retorna null quando nenhuma credencial está configurada,
     * permitindo que todos os callers operem sem o agente.
     */
    public static function make(): ?AiProviderInterface
    {
        // 🔴 O desligamento da IA ficava só numa propriedade pública de cada tela,
        // que o navegador altera: com a chave configurada, a chamada saía para o
        // provedor pago mesmo com a IA desligada. A decisão agora é do servidor,
        // num ponto só, para todas as telas e relatórios.
        $ligada = filter_var(SystemSetting::getValue('ai_enabled', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($ligada === false) {
            return null;
        }

        // Limite por usuário: a IA é paga pela instituição e não pode virar
        // proxy livre de quem está logado. Processos sem usuário (agendador) não contam.
        if ($id = auth()->id()) {
            $chave = 'ia-por-usuario:'.$id;
            if (RateLimiter::tooManyAttempts($chave, self::LIMITE_POR_MINUTO)) {
                return null;
            }
            RateLimiter::hit($chave, 60);
        }

        $provider = SystemSetting::getValue('ai_provider', 'gemini-studio');

        return match ($provider) {
            'vertex-ai' => self::makeVertexProvider(),
            default => self::makeGeminiProvider(),
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
        $json = SystemSetting::getValue('vertex_service_account_json');

        if (empty($projectId) || empty($json)) {
            return null;
        }

        return new VertexAiProvider;
    }
}
