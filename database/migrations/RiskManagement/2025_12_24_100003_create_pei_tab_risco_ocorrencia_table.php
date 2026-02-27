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
        Schema::create('risk_management.tab_risco_ocorrencia', function (Blueprint $table) {
            $table->uuid('cod_ocorrencia')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_risco')->references('cod_risco')->on('risk_management.tab_risco')->cascadeOnDelete();
            $table->date('dte_ocorrencia')->nullable(false);
            $table->text('txt_descricao_ocorrencia')->nullable(false);
            $table->decimal('vlr_impacto_financeiro', 15, 2)->nullable(true);
            $table->text('txt_acoes_tomadas')->nullable(true);
            $table->text('txt_licoes_aprendidas')->nullable(true);
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index('cod_risco');
            $table->index('dte_ocorrencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_management.tab_risco_ocorrencia');
    }
};
