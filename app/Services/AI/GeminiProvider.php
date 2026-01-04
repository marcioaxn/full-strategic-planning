<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? \App\Models\SystemSetting::getValue('ai_api_key', '');
    }

    public function suggest(string $prompt, string $context = ''): ?string
    {
        if (empty($this->apiKey)) {
            return 'API Key não configurada.';
        }

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $context . "\n\nUsuário: " . $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'A IA não retornou uma resposta válida.';
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro desconhecido na API do Google.';
            Log::error('Gemini API Error: ' . $msg);
            return "Erro na IA: {$msg}";

        } catch (\Exception $e) {
            Log::error('Gemini Exception: ' . $e->getMessage());
            return 'Falha técnica na comunicação com o Google Gemini.';
        }
    }

    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'Chave de API ausente.'];
        }

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '?key=' . $this->apiKey, [
                'contents' => [['parts' => [['text' => 'Olá, responda apenas com a palavra OK.']]]]
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Conexão estabelecida com sucesso! O Gemini está respondendo.'];
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro na autenticação.';
            return ['success' => false, 'message' => "Falha na conexão: {$msg}"];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Não foi possível contatar os servidores da Google: ' . $e->getMessage()];
        }
    }
}
