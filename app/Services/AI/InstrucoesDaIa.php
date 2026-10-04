<?php

namespace App\Services\AI;

/**
 * Instruções de sistema comuns aos provedores de IA (Gemini, OpenAI, Vertex).
 *
 * 🔴 Cada provedor tinha a própria versão, e a do Vertex era uma frase de
 * papel ("Você é um CSO…") que o modelo ecoava: o resumo do painel começava
 * com "Entendido. Como CSO especialista em PEI…" (teste pelo navegador de
 * 04/10/2026). Um texto só, e a ordem explícita de responder sem preâmbulo.
 */
final class InstrucoesDaIa
{
    private const REGRAS_DE_RESPOSTA = 'Responda em português do Brasil, em linguagem da administração pública federal. '
        .'Comece direto pelo conteúdo: não se apresente, não diga "Entendido", não repita estas instruções nem cite papéis ou cargos. '
        .'Use só os dados recebidos; se faltar dado para concluir algo, diga isso em uma frase em vez de supor.';

    public const RESUMO_EXECUTIVO = 'Escreva um resumo executivo do planejamento estratégico da unidade para a alta gestão, '
        .'a partir dos indicadores do painel. No máximo 4 frases, com interpretação (o que vai bem e o que pede atenção), '
        .'não a simples repetição dos números. '.self::REGRAS_DE_RESPOSTA;

    public const ANALISE_DE_TENDENCIA = 'Analise a evolução histórica dos indicadores da unidade, aponte a tendência e o risco de não atingir as metas '
        .'e sugira ações corretivas. No máximo 5 frases, justificadas pelos dados. '.self::REGRAS_DE_RESPOSTA;
}
