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
        Schema::create('performance_indicators.tab_linha_base_indicador', function (Blueprint $table) {
            $table->uuid('cod_linha_base')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_indicador')
                  ->references('cod_indicador')
                  ->on('performance_indicators.tab_indicador')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->decimal('num_linha_base', 15, 2);
            $table->smallInteger('num_ano')->nullable(false);
            $table->timestamps();
            $table->softDeletes();

            // Índice para performance
            $table->index(['cod_indicador', 'num_ano']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_indicators.tab_linha_base_indicador');
    }
};
