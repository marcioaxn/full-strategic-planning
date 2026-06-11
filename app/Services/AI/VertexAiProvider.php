<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VertexAiProvider implements AiProviderInterface
{
    protected ?string $projectId;
    protected ?string $location;
    protected ?string $modelId;
    protected ?array $credentials;

    public function __construct(
        ?string $projectId = null,
        ?string $location = null,
        ?string $modelId = null,
        ?string $json = null
    ) {
        $this->projectId = $projectId ?? \App\Models\SystemSetting::getValue('vertex_project_id');
        $this->location = $location ?? \App\Models\SystemSetting::getValue('vertex_location', 'us-central1');
        $this->modelId = $modelId ?? \App\Models\SystemSetting::getValue('ai_model', 'gemini-2.5-flash');

        $json = $json ?? \App\Models\SystemSetting::getValue('vertex_service_account_json');

        if ($json) {
            $this->credentials = json_decode(trim($json), true);
        } else {
            $this->credentials = null;
        }
    }

    /**
     * Gera token OAuth2 via JWT assinado com a chave privada da Service Account (sem google/auth).
     * @throws \Exception
     */
    protected function getAccessToken(): string
    {
        if (!$this->credentials) {
            throw new \Exception('Credenciais da Service Account não foram carregadas (JSON vazio ou inválido).');
        }

        if (!isset($this->credentials['private_key']) || !isset($this->credentials['client_email'])) {
            throw new \Exception('O JSON fornecido é inválido: chaves "private_key" ou "client_email" estão ausentes.');
        }

        $now = time();
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claim = base64_encode(json_encode([
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        ]));

        $header = rtrim(strtr($header, '+/', '-_'), '=');
        $claim  = rtrim(strtr($claim, '+/', '-_'), '=');

        $sigInput = "{$header}.{$claim}";
        $privateKey = openssl_pkey_get_private($this->credentials['private_key']);

        if (!$privateKey) {
            throw new \Exception('Não foi possível carregar a chave privada da Service Account.');
        }

        $signature = '';
        if (!openssl_sign($sigInput, $signature, $privateKey, 'SHA256')) {
            throw new \Exception('Falha ao assinar o JWT com a chave privada.');
        }

        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $jwt = "{$sigInput}.{$signature}";

        $response = Http::timeout(10)
            ->withoutVerifying()
            ->asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

        if (!$response->successful()) {
            $error = $response->json();
            $msg = $error['error_description'] ?? $error['error'] ?? 'Erro desconhecido ao gerar token.';
            throw new \Exception('Falha na autenticação Google: ' . $msg);
        }

        $token = $response->json('access_token');
        if (!$token) {
            throw new \Exception('Token de acesso ausente na resposta do Google.');
        }

        return $token;
    }

    protected function getApiUrl(): string
    {
        return "https://{$this->location}-aiplatform.googleapis.com/v1/projects/{$this->projectId}/locations/{$this->location}/publishers/google/models/{$this->modelId}:streamGenerateContent";
    }

    public function suggest(string $prompt, string $context = ''): ?string
    {
        try {
            $token = $this->getAccessToken();
        } catch (\Exception $e) {
            return 'Erro de Configuração: ' . $e->getMessage();
        }

        try {
            $response = Http::timeout(60)
                ->withoutVerifying()
                ->withToken($token)
                ->post($this->getApiUrl(), [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $context . "\n\nSolicitação: " . $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'topP' => 0.8,
                    'topK' => 40
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $fullText = '';

                if (is_array($data)) {
                    foreach ($data as $chunk) {
                        $fullText .= $chunk['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    }
                }

                if (!empty($fullText)) {
                    if (str_contains($fullText, '\\u')) {
                        $decoded = json_decode('"' . str_replace('"', '\\"', $fullText) . '"');
                        if ($decoded) {
                            $fullText = $decoded;
                        }
                    }
                    return $fullText;
                }

                return 'A IA não retornou uma resposta válida.';
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro desconhecido na Vertex AI API.';
            Log::error('VertexAI API Error: ' . $msg);
            return "Erro na análise (Vertex): {$msg}";

        } catch (\Exception $e) {
            Log::error('VertexAI Exception: ' . $e->getMessage());
            return 'Falha técnica na comunicação com o cérebro da IA (Vertex).';
        }
    }

    public function testConnection(): array
    {
        try {
            $token = $this->getAccessToken();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->withToken($token)
                ->post($this->getApiUrl(), [
                'contents' => [['role' => 'user', 'parts' => [['text' => 'Olá, responda apenas OK.']]]]
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Conexão com Vertex AI estabelecida com sucesso!'];
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro desconhecido.';
            return ['success' => false, 'message' => "Falha na conexão Vertex: {$msg}"];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contatar Vertex AI: ' . $e->getMessage()];
        }
    }

    public function analyzeSmart(string $type, string $title, string $description = ''): ?string
    {
        $context = "Você é um auditor sênior de Planejamento Estratégico Integrado (PEI).
        Analise se o {$type} informado segue os critérios SMART.
        Forneça feedback curto e direto (máximo 3 frases).";

        return $this->suggest("Título: {$title}. Descrição: {$description}.", $context);
    }

    public function summarizeStrategy(array $stats, string $orgName): ?string
    {
        $context = "Você é um CSO especialista em PEI. Analise os KPIs para {$orgName} e entregue insights diretos.";
        return $this->suggest('Dados: ' . json_encode($stats), $context);
    }

    public function analyzeTrends(array $indicatorData, string $orgName): ?string
    {
        $context = "Você é um Analista Estratégico de PEI. Analise o histórico de indicadores de {$orgName} e preveja tendências.";
        return $this->suggest('Dados de Evolução: ' . json_encode($indicatorData), $context);
    }
}
