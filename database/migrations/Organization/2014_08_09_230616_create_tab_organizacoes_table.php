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
        Schema::create('organization.tab_organizacoes', function (Blueprint $table) {
            $table->uuid('cod_organizacao')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('sgl_organizacao')->nullable(false);
            $table->text('nom_organizacao')->nullable(false);
            $table->uuid('rel_cod_organizacao')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Inserir unidade central padrão
        DB::table('organization.tab_organizacoes')->insert([
            'cod_organizacao' => '3834910f-66f7-46d8-9104-2904d59e1241',
            'sgl_organizacao' => 'UnidCent',
            'nom_organizacao' => 'Unidade Central',
            'rel_cod_organizacao' => '3834910f-66f7-46d8-9104-2904d59e1241',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization.tab_organizacoes');
    }
};
