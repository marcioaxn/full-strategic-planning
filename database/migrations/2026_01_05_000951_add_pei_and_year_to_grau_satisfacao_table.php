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
        Schema::table('strategic_planning.tab_grau_satisfacao', function (Blueprint $table) {
            // Adiciona o vínculo com o PEI (Nullable para permitir escala global padrão)
            $table->uuid('cod_pei')->nullable()->after('cod_grau_satisfacao');
            
            // Adiciona o Ano para implementar o conceito de "Thresholds de Maturidade"
            $table->integer('num_ano')->nullable()->after('cod_pei');

            // Foreign Key
            $table->foreign('cod_pei')
                  ->references('cod_pei')
                  ->on('strategic_planning.tab_pei')
                  ->onDelete('cascade');

            // Índices para performance nas consultas de cálculo
            $table->index(['cod_pei', 'num_ano'], 'idx_grau_satisfacao_pei_ano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strategic_planning.tab_grau_satisfacao', function (Blueprint $table) {
            $table->dropForeign(['cod_pei']);
            $table->dropIndex('idx_grau_satisfacao_pei_ano');
            $table->dropColumn(['cod_pei', 'num_ano']);
        });
    }
};