<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements AiProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrlTemplate = 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent';

    public function __construct(?string $apiKey = null, ?string $model = null)
    {
        $this->apiKey = $apiKey ?? \App\Models\SystemSetting::getValue('ai_api_key', '');
        $this->model = $model ?? \App\Models\SystemSetting::getValue('ai_model', 'gemini-2.5-flash');
    }

    protected function getApiUrl(): string
    {
        return str_replace('{model}', $this->model, $this->baseUrlTemplate);
    }

    public function suggest(string $prompt, string $context = ''): ?string
    {
        if (empty($this->apiKey)) {
            return 'API Key da IA não configurada no sistema.';
        }

        try {
            $response = Http::timeout(60)

                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->getApiUrl() . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $context . "\n\nSolicitação: " . $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $fullText = data_get($data, 'candidates.0.content.parts.0.text', 'A IA não retornou uma resposta válida.');

                if (str_contains($fullText, '\\u')) {
                    $fullText = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
                    }, $fullText);
                }

                return $fullText;
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro desconhecido na API do Google.';
            Log::error('Gemini API Error: ' . $msg);
            return "Erro na análise: {$msg}";

        } catch (\Throwable $e) {
            Log::error('Gemini Exception: ' . $e->getMessage());
            return 'Falha técnica na comunicação com o cérebro da IA.';
        }
    }

    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'message' => 'Chave de API ausente nas configurações.'];
        }

        try {
            $response = Http::timeout(10)

                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->getApiUrl() . '?key=' . $this->apiKey, [
                'contents' => [['parts' => [['text' => 'Olá, responda apenas OK.']]]]
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Conexão estabelecida com sucesso! O Agente está respondendo.'];
            }

            $error = $response->json();
            $msg = $error['error']['message'] ?? 'Erro na autenticação.';
            return ['success' => false, 'message' => "Falha na conexão: {$msg} (Status: {$response->status()})"];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Não foi possível contatar os servidores da Google: ' . $e->getMessage()];
        }
    }

    public function analyzeSmart(string $type, string $title, string $description = ''): ?string
    {
        $context = "Você é um auditor sênior de Planejamento Estratégico Integrado (PEI).
        Sua tarefa é analisar se o {$type} informado segue os critérios SMART (Específico, Mensurável, Atingível, Relevante, Prazo).
        Forneça um feedback muito curto e direto (máximo 3 frases) com sugestões de melhoria.
        Seja encorajador mas rigoroso com a metodologia.";

        $prompt = "Título do {$type}: {$title}. Descrição: {$description}.";

        return $this->suggest($prompt, $context);
    }

    public function summarizeStrategy(array $stats, string $orgName): ?string
    {
        $context = "Você é um Chief Strategy Officer (CSO) especialista em Planejamento Estratégico Integrado (PEI).
        Analise os KPIs do dashboard e escreva um resumo executivo de altíssimo nível para a liderança.
        O texto deve ser curto (máximo 4 frases), direto e focado em INSIGHTS, não apenas repetindo números.
        Destaque o que vai bem e onde há necessidade de atenção.";

        $statsJson = json_encode($stats);
        $prompt = "Organização: {$orgName}. Estatísticas Atuais: {$statsJson}. Escreva o resumo executivo.";

        return $this->suggest($prompt, $context);
    }

    public function analyzeTrends(array $indicatorData, string $orgName): ?string
    {
        $context = "Você é um Analista de Dados Estratégicos especializado em PEI.
        Analise o histórico de evolução dos indicadores e preveja tendências.
        Identifique riscos de não atingimento de metas e sugira ações corretivas.
        Seja técnico, direto e use dados para justificar sua análise (máximo 5 frases).";

        $dataJson = json_encode($indicatorData);
        $prompt = "Organização: {$orgName}. Dados de Evolução: {$dataJson}. Realize a análise preditiva.";

        return $this->suggest($prompt, $context);
    }
}
