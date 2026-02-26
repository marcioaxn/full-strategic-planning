<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('risk_management.tab_risco_mitigacao', function (Blueprint $table) {
            $table->uuid('cod_mitigacao')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_risco')->references('cod_risco')->on('risk_management.tab_risco')->cascadeOnDelete();
            $table->string('dsc_tipo_mitigacao', 50)->nullable(false); // Prevenir, Reduzir, Transferir, Aceitar
            $table->text('txt_acao_mitigacao')->nullable(false);
            $table->foreignUuid('cod_responsavel')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->date('dte_prazo')->nullable(true);
            $table->string('dsc_status', 50)->nullable(false); // Planejada, Em Andamento, Concluída, Cancelada
            $table->text('txt_observacoes')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index('cod_risco');
            $table->index('dsc_tipo_mitigacao');
            $table->index('dsc_status');
            $table->index('cod_responsavel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_management.tab_risco_mitigacao');
    }
};
