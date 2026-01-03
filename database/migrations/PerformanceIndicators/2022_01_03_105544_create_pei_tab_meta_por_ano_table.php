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
        Schema::create('performance_indicators.tab_meta_por_ano', function (Blueprint $table) {
            $table->uuid('cod_meta_por_ano')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_indicador')->references('cod_indicador')->on('pei.tab_indicador')->cascadeOnDelete();
            $table->smallInteger('num_ano')->nullable(false);
            $table->decimal('meta', 15, 2)->nullable(true);
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
        Schema::dropIfExists('pei.tab_meta_por_ano');
    }
};
