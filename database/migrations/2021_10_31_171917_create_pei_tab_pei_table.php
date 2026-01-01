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
        Schema::create('pei.tab_pei', function (Blueprint $table) {
            $table->uuid('cod_pei')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('dsc_pei')->nullable(false);
            $table->smallInteger('num_ano_inicio_pei')->nullable(false);
            $table->smallInteger('num_ano_fim_pei')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.tab_pei');
    }
};
