<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alinha as tabelas de mitigação e de ocorrência de risco ao que a aplicação usa.
 *
 * 🔴 POR QUE ISTO É NECESSÁRIO
 *
 * As tabelas foram criadas com nomes de coluna que nenhuma tela, Model ou
 * relatório usa. Os Models gravam `dsc_tipo`, `txt_descricao`,
 * `vlr_custo_estimado` e `num_impacto_real`; a tabela tinha
 * `dsc_tipo_mitigacao`, `txt_acao_mitigacao`, `txt_descricao_ocorrencia` — e
 * nem tinha as colunas de custo e de impacto real.
 *
 * Resultado (verificado no navegador em 03/10/2026): cadastrar plano de
 * mitigação ou registrar ocorrência quebrava com "column dsc_tipo does not
 * exist". As mitigações que existem vieram de carga de dados, gravadas
 * direto nas colunas antigas — e por isso apareciam sem descrição na tela.
 *
 * Renomear preserva os dados já gravados. As colunas novas são anuláveis e sem
 * padrão: nenhum registro existente é afetado. Só RENAME e ADD COLUMN simples —
 * compatível com PostgreSQL 9.3 (sem IF NOT EXISTS em coluna).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_management.tab_risco_mitigacao', function (Blueprint $table) {
            $table->renameColumn('dsc_tipo_mitigacao', 'dsc_tipo');
            $table->renameColumn('txt_acao_mitigacao', 'txt_descricao');
        });

        Schema::table('risk_management.tab_risco_mitigacao', function (Blueprint $table) {
            $table->decimal('vlr_custo_estimado', 15, 2)->nullable();
        });

        Schema::table('risk_management.tab_risco_ocorrencia', function (Blueprint $table) {
            $table->renameColumn('txt_descricao_ocorrencia', 'txt_descricao');
        });

        Schema::table('risk_management.tab_risco_ocorrencia', function (Blueprint $table) {
            $table->smallInteger('num_impacto_real')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('risk_management.tab_risco_ocorrencia', function (Blueprint $table) {
            $table->dropColumn('num_impacto_real');
        });

        Schema::table('risk_management.tab_risco_ocorrencia', function (Blueprint $table) {
            $table->renameColumn('txt_descricao', 'txt_descricao_ocorrencia');
        });

        Schema::table('risk_management.tab_risco_mitigacao', function (Blueprint $table) {
            $table->dropColumn('vlr_custo_estimado');
        });

        Schema::table('risk_management.tab_risco_mitigacao', function (Blueprint $table) {
            $table->renameColumn('dsc_tipo', 'dsc_tipo_mitigacao');
            $table->renameColumn('txt_descricao', 'txt_acao_mitigacao');
        });
    }
};
