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
        Schema::create('strategic_planning.tab_perspectiva', function (Blueprint $table) {
            $table->uuid('cod_perspectiva')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('dsc_perspectiva')->nullable(false);
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->nullable(false);
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('pei.tab_pei')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.tab_perspectiva');
    }
};
