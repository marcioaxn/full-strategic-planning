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
        Schema::create('tab_nivel_hierarquico', function (Blueprint $table) {
            $table->smallInteger('num_nivel_hierarquico_apresentacao')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        // Inserir 100 níveis hierárquicos
        for ($cont = 1; $cont <= 100; $cont++) {
            DB::table('tab_nivel_hierarquico')->insert([
                'num_nivel_hierarquico_apresentacao' => $cont,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_nivel_hierarquico');
    }
};
