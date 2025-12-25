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
        Schema::create('tab_perfil_acesso', function (Blueprint $table) {
            $table->uuid('cod_perfil')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->text('dsc_perfil')->nullable(false);
            $table->text('dsc_permissao')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Inserir perfis de acesso padrão
        DB::table('tab_perfil_acesso')->insert([
            [
                'cod_perfil' => 'c00b9ebc-7014-4d37-97dc-7875e55fff2a',
                'dsc_perfil' => 'Super Administrador',
                'dsc_permissao' => 'Servidor(a) com todos os privilégios de administração do sistema',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_perfil' => 'c00b9ebc-7014-4d37-97dc-7875e55fff3b',
                'dsc_perfil' => 'Administrador da Unidade',
                'dsc_permissao' => 'Servidor(a) com todos os privilégios de administração do sistema somente dentro da Unidade que está cadastrado',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_perfil' => 'c00b9ebc-7014-4d37-97dc-7875e55fff4c',
                'dsc_perfil' => 'Gestor(a) Responsável',
                'dsc_permissao' => 'Servidor(a) que tem como responsabilidade manter a atualização do Plano de Ação ao qual está como responsável',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cod_perfil' => 'c00b9ebc-7014-4d37-97dc-7875e55fff5d',
                'dsc_perfil' => 'Gestor(a) Substituto(a)',
                'dsc_permissao' => 'Servidor(a) que tem como responsabilidade manter a atualização do Plano de Ação ao qual está como substituto(a)',
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
        Schema::dropIfExists('tab_perfil_acesso');
    }
};
