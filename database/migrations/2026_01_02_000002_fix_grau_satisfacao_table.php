<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cenário 1: Tabela ficou presa no schema 'pei' com o nome errado (satisfcao)
        $existsOldTypo = DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'pei' AND table_name = 'tab_grau_satisfcao')")[0]->exists;
        
        if ($existsOldTypo) {
            // Renomear para o correto
            DB::statement("ALTER TABLE pei.tab_grau_satisfcao RENAME TO tab_grau_satisfacao");
            // Mover para o schema correto
            DB::statement("ALTER TABLE pei.tab_grau_satisfacao SET SCHEMA strategic_planning");
            return;
        }

        // Cenário 2: Tabela ficou presa no schema 'pei' com o nome certo (satisfacao) - caso o script anterior tenha falhado silenciosamente
        $existsOldCorrect = DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'pei' AND table_name = 'tab_grau_satisfacao')")[0]->exists;
        
        if ($existsOldCorrect) {
            DB::statement("ALTER TABLE pei.tab_grau_satisfacao SET SCHEMA strategic_planning");
            return;
        }

        // Cenário 3: Tabela foi movida, mas com o nome errado (satisfcao)
        $existsNewTypo = DB::select("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'strategic_planning' AND table_name = 'tab_grau_satisfcao')")[0]->exists;
        
        if ($existsNewTypo) {
            DB::statement("ALTER TABLE strategic_planning.tab_grau_satisfcao RENAME TO tab_grau_satisfacao");
            return;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Não reverter renomeações de correção de typo.
    }
};
