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
        Schema::create('strategic_planning.tab_objetivo_estrategico', function (Blueprint $table) {
            $table->uuid('cod_objetivo')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('nom_objetivo')->nullable(false);
            $table->text('dsc_objetivo')->nullable(false);
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->nullable(false);
            $table->foreignUuid('cod_perspectiva')->references('cod_perspectiva')->on('strategic_planning.tab_perspectiva')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_objetivo_estrategico');
    }
};
