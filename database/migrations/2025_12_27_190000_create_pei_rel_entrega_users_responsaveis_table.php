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
        // Criar tabela pivô para múltiplos responsáveis
        Schema::create('action_plan.rel_entrega_users_responsaveis', function (Blueprint $table) {
            $table->uuid('cod_entrega');
            $table->uuid('cod_usuario');
            
            $table->primary(['cod_entrega', 'cod_usuario']);
            
            $table->foreign('cod_entrega')
                  ->references('cod_entrega')
                  ->on('action_plan.tab_entregas')
                  ->cascadeOnDelete();
                  
            $table->foreign('cod_usuario')
                  ->references('id')
                  ->on('users')
                  ->cascadeOnDelete();
                  
            $table->timestamps();
        });

        // Migrar dados existentes da coluna cod_responsavel para a nova tabela pivô
        $entregasComResponsavel = DB::table('action_plan.tab_entregas')
            ->whereNotNull('cod_responsavel')
            ->select('cod_entrega', 'cod_responsavel')
            ->get();

        foreach ($entregasComResponsavel as $entrega) {
            // Verifica se o usuário ainda existe (para evitar erro de FK)
            $userExists = DB::table('users')->where('id', $entrega->cod_responsavel)->exists();
            if ($userExists) {
                DB::table('action_plan.rel_entrega_users_responsaveis')->insert([
                    'cod_entrega' => $entrega->cod_entrega,
                    'cod_usuario' => $entrega->cod_responsavel,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Comentário na tabela
        DB::statement("COMMENT ON TABLE action_plan.rel_entrega_users_responsaveis IS 'Tabela pivô para permitir múltiplos responsáveis por entrega (estilo Notion).'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plan.rel_entrega_users_responsaveis');
    }
};
