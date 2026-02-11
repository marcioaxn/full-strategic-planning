<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateCsvTemplates extends Command
{
    protected $signature = 'make:export-templates';
    protected $description = 'Gera templates CSV para importação de Planos, Entregas e Indicadores';

    public function handle()
    {
        $path = public_path('templates');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // ==========================================
        // 1. PLANOS DE AÇÃO
        // ==========================================
        $this->generatePair(
            $path,
            '1_Planos',
            [
                'Descrição da Ação' => 'dsc_plano_de_acao',
                'Nome do Objetivo Estratégico' => 'nom_objetivo_vinculado', // Mapeamento manual na importação
                'Unidade Responsável' => 'nom_organizacao',
                'Data de Início (dd/mm/aaaa)' => 'dte_inicio',
                'Data de Fim (dd/mm/aaaa)' => 'dte_fim',
                'Orçamento Previsto (R$)' => 'vlr_orcamento_previsto',
                'Tipo de Execução (Projeto/Processo)' => 'dsc_tipo_execucao',
                'Status' => 'bln_status',
                'Código PPA' => 'cod_ppa',
                'Código LOA' => 'cod_loa',
                'Detalhamento / Justificativa' => 'txt_detalhamento',
            ],
            [
                'Descrição da Ação' => 'Texto curto descrevendo o que será feito. Ex: "Modernizar o Data Center".',
                'Nome do Objetivo Estratégico' => 'Nome exato do objetivo estratégico ao qual este plano está vinculado.',
                'Unidade Responsável' => 'Nome da unidade organizacional responsável pela execução.',
                'Data de Início (dd/mm/aaaa)' => 'Data de início prevista. Formato: 01/01/2026.',
                'Data de Fim (dd/mm/aaaa)' => 'Data de conclusão prevista. Deve ser posterior à data de início.',
                'Orçamento Previsto (R$)' => 'Valor monetário estimado. Use apenas números e vírgula para centavos.',
                'Tipo de Execução (Projeto/Processo)' => 'Classificação do plano. Geralmente "Projeto", "Atividade" ou "Processo".',
                'Status' => 'Estado atual: "Não Iniciado", "Em Andamento", "Concluído", "Suspenso" ou "Cancelado".',
                'Código PPA' => 'Código do Plano Plurianual (Opcional).',
                'Código LOA' => 'Código da Lei Orçamentária Anual (Opcional).',
                'Detalhamento / Justificativa' => 'Texto longo explicando o porquê da ação e detalhes técnicos.',
            ]
        );

        // ==========================================
        // 2. ENTREGAS
        // ==========================================
        $this->generatePair(
            $path,
            '2_Entregas',
            [
                'Título da Entrega' => 'dsc_entrega',
                'Nome do Plano Vinculado' => 'dsc_plano_vinculado',
                'Tipo (Tarefa/Marco)' => 'dsc_tipo',
                'Data de Início' => 'dte_inicio', // Entregas podem ter início implícito
                'Prazo Final (Deadline)' => 'dte_prazo',
                'Peso (%)' => 'num_peso',
                'Prioridade (Baixa/Média/Alta)' => 'cod_prioridade',
                'Status' => 'bln_status',
                'Responsável (Email)' => 'email_responsavel',
            ],
            [
                'Título da Entrega' => 'Nome da entrega ou tarefa. Ex: "Relatório de Diagnóstico".',
                'Nome do Plano Vinculado' => 'Nome exato do Plano de Ação ao qual esta entrega pertence.',
                'Tipo (Tarefa/Marco)' => 'Classificação: "Tarefa" (padrão) ou "Marco" (entrega principal).',
                'Data de Início' => 'Data de início da tarefa (Opcional).',
                'Prazo Final (Deadline)' => 'Data limite para conclusão. Formato: dd/mm/aaaa.',
                'Peso (%)' => 'Impacto desta entrega no progresso do plano (0 a 100). A soma do plano deve ser 100.',
                'Prioridade (Baixa/Média/Alta)' => 'Nível de urgência da entrega.',
                'Status' => 'Estado atual: "Não Iniciado", "Em Andamento", "Concluído".',
                'Responsável (Email)' => 'E-mail do usuário responsável pela entrega (deve estar cadastrado).',
            ]
        );

        // ==========================================
        // 3. INDICADORES
        // ==========================================
        $this->generatePair(
            $path,
            '3_Indicadores',
            [
                'Nome do Indicador' => 'nom_indicador',
                'Conceito / Descrição' => 'dsc_indicador',
                'Unidade de Medida' => 'dsc_unidade_medida',
                'Fórmula de Cálculo' => 'dsc_formula',
                'Polaridade (Maior/Menor Melhor)' => 'dsc_polaridade',
                'Periodicidade' => 'dsc_periodo_medicao',
                'Fonte de Dados' => 'dsc_fonte',
                'Meta Global' => 'dsc_meta',
                'Acumulado (Sim/Não)' => 'bln_acumulado',
                'Tipo de Vínculo (Objetivo/Plano)' => 'dsc_tipo_vinculo',
                'Nome do Vínculo' => 'nom_vinculo', // Nome do Obj ou do Plano
            ],
            [
                'Nome do Indicador' => 'Nome oficial do KPI. Ex: "Índice de Satisfação do Usuário".',
                'Conceito / Descrição' => 'Explicação do que o indicador mede.',
                'Unidade de Medida' => 'Ex: "Percentual (%)", "Quantidade", "Valor (R$)", "Dias".',
                'Fórmula de Cálculo' => 'Expressão matemática. Ex: "(A / B) * 100".',
                'Polaridade (Maior/Menor Melhor)' => '"Positiva" (maior é melhor), "Negativa" (menor é melhor) ou "Estabilidade".',
                'Periodicidade' => 'Frequência de medição: "Mensal", "Trimestral", "Semestral", "Anual".',
                'Fonte de Dados' => 'Origem da informação. Ex: "Sistema ERP", "Planilha de Controle".',
                'Meta Global' => 'Valor alvo geral. Ex: "95%".',
                'Acumulado (Sim/Não)' => '"Sim" se os valores somam ao longo do ano, "Não" se o valor é pontual no mês.',
                'Tipo de Vínculo (Objetivo/Plano)' => 'Define se o indicador mede um Objetivo Estratégico ou um Plano de Ação.',
                'Nome do Vínculo' => 'Nome exato do Objetivo ou Plano ao qual este indicador pertence.',
            ]
        );

        $this->info('Templates gerados com sucesso em public/templates!');
    }

    private function generatePair($path, $prefix, $columnsMap, $guideMap)
    {
        // Arquivo 1: Modelo (Colunas para preenchimento)
        $handleModel = fopen("{$path}/{$prefix}_Modelo_Preenchimento.csv", 'w');
        fprintf($handleModel, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel
        fputcsv($handleModel, array_keys($columnsMap), ';'); // Header
        // Linha de exemplo vazia para forçar tipos se necessário, ou apenas header
        fclose($handleModel);

        // Arquivo 2: Guia (Explicação)
        $handleGuide = fopen("{$path}/{$prefix}_Guia_Preenchimento.csv", 'w');
        fprintf($handleGuide, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para Excel
        fputcsv($handleGuide, ['Coluna', 'O que preencher? (Instruções)'], ';');
        
        foreach ($guideMap as $col => $desc) {
            fputcsv($handleGuide, [$col, $desc], ';');
        }
        fclose($handleGuide);
    }
}
