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
        // 1. Remover a constraint unique existente
        Schema::table('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->dropUnique('rel_user_org_plano_perfil_unique');
        });

        // 2. Remover a foreign key e tornar a coluna nullable
        Schema::table('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->dropForeign(['cod_plano_de_acao']);
        });

        // 3. Alterar coluna para nullable
        DB::statement('ALTER TABLE rel_users_tab_organizacoes_tab_perfil_acesso ALTER COLUMN cod_plano_de_acao DROP NOT NULL');

        // 4. Re-adicionar foreign key (agora nullable)
        Schema::table('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->foreign('cod_plano_de_acao')
                  ->references('cod_plano_de_acao')
                  ->on('tab_plano_de_acao')
                  ->cascadeOnDelete();
        });

        // 5. Criar nova constraint unique que permite nulls
        // PostgreSQL permite múltiplos NULLs em unique constraints por padrão
        Schema::table('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'cod_organizacao', 'cod_plano_de_acao', 'cod_perfil'],
                'rel_user_org_plano_perfil_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para NOT NULL
        Schema::table('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->dropUnique('rel_user_org_plano_perfil_unique');
            $table->dropForeign(['cod_plano_de_acao']);
        });

        // Remover registros com NULL antes de aplicar NOT NULL
        DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')
            ->whereNull('cod_plano_de_acao')
            ->delete();

        DB::statement('ALTER TABLE rel_users_tab_organizacoes_tab_perfil_acesso ALTER COLUMN cod_plano_de_acao SET NOT NULL');

        Schema::table('rel_users_tab_organizacoes_tab_perfil_acesso', function (Blueprint $table) {
            $table->foreign('cod_plano_de_acao')
                  ->references('cod_plano_de_acao')
                  ->on('tab_plano_de_acao')
                  ->cascadeOnDelete();

            $table->unique(
                ['user_id', 'cod_organizacao', 'cod_plano_de_acao', 'cod_perfil'],
                'rel_user_org_plano_perfil_unique'
            );
        });
    }
};
