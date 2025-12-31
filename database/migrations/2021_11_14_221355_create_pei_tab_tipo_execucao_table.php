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
        Schema::create('tab_tipo_execucao', function (Blueprint $table) {
            $table->uuid('cod_tipo_execucao')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('dsc_tipo_execucao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Inserir tipos de execução padrão
        DB::table('tab_tipo_execucao')->insert([
            [
                'cod_tipo_execucao' => 'c00b9ebc-7014-4d37-97dc-7875e55fff1b',
                'dsc_tipo_execucao' => 'Ação',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_tipo_execucao' => 'ecef6a50-c010-4cda-afc3-cbda245b55b0',
                'dsc_tipo_execucao' => 'Iniciativa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_tipo_execucao' => '57518c30-3bc5-4305-a998-8ce8b11550ed',
                'dsc_tipo_execucao' => 'Projeto',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_tipo_execucao');
    }
};
