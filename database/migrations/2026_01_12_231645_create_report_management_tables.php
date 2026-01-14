<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela de Relatórios Agendados
        Schema::create('tab_relatorios_agendados', function (Blueprint $table) {
            $table->uuid('cod_agendamento')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('dsc_tipo_relatorio'); // ex: executivo, objetivos, indicadores, planos, riscos
            $table->string('dsc_frequencia'); // diario, semanal, mensal
            $table->jsonb('txt_filtros')->nullable(); // filtros aplicados (organizacao_id, ano, etc)
            $table->timestamp('dte_proxima_execucao');
            $table->boolean('bln_ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabela de Histórico de Relatórios Gerados
        Schema::create('tab_relatorios_gerados', function (Blueprint $table) {
            $table->uuid('cod_relatorio_gerado')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('dsc_tipo_relatorio');
            $table->string('dsc_caminho_arquivo');
            $table->string('dsc_formato'); // pdf, excel
            $table->jsonb('txt_filtros_aplicados')->nullable();
            $table->integer('num_tamanho_bytes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_relatorios_gerados');
        Schema::dropIfExists('tab_relatorios_agendados');
    }
};