<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? \App\Models\SystemSetting::getValue('ai_api_key', '');
    }

    public function suggest(string $prompt, string $context = ''): ?string
    {
        if (empty($this->apiKey)) {
            return 'OpenAI API Key não configurada.';
        }

        try {
            $response = Http::timeout(20)->withoutVerifying()->withToken($this->apiKey)->post($this->baseUrl, [
                'model' => 'gpt-4o-mini', // Modelo performático e econômico
                'messages' => [
                    ['role' => 'system', 'content' => $context],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? 'A OpenAI não retornou uma resposta válida.';
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro desconhecido na OpenAI.';
            Log::error('OpenAI API Error: ' . $msg);
            return "Erro na OpenAI: {$msg}";

        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return 'Falha técnica na comunicação com a OpenAI.';
        }
    }

    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'Chave de API OpenAI ausente.'];
        }

        try {
            $response = Http::timeout(10)->withoutVerifying()->withToken($this->apiKey)->post($this->baseUrl, [
                'model' => 'gpt-4o-mini',
                'messages' => [['role' => 'user', 'content' => 'Responda apenas OK.']],
                'max_tokens' => 5
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Conexão com OpenAI estabelecida com sucesso!'];
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro na autenticação da OpenAI.';
            return ['success' => false, 'message' => "Falha na OpenAI: {$msg}"];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Não foi possível contatar a OpenAI: ' . $e->getMessage()];
        }
    }
}
