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
        // 1. Localizar o PEI mais antigo (provavelmente o legado da organização)
        $firstPei = DB::table('strategic_planning.tab_pei')
            ->orderBy('num_ano_inicio_pei', 'asc')
            ->first();

        if ($firstPei) {
            // 2. Vincular todos os Graus de Satisfação que estão nulos ao primeiro PEI encontrado
            DB::table('strategic_planning.tab_grau_satisfacao')
                ->whereNull('cod_pei')
                ->update(['cod_pei' => $firstPei->cod_pei]);
        }

        // 3. Tornar a coluna cod_pei NOT NULL para garantir integridade futura
        // Nota: Só fazemos isso se houver pelo menos um PEI no sistema
        if ($firstPei) {
            Schema::table('strategic_planning.tab_grau_satisfacao', function (Blueprint $table) {
                $table->uuid('cod_pei')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strategic_planning.tab_grau_satisfacao', function (Blueprint $table) {
            $table->uuid('cod_pei')->nullable()->change();
        });
    }
};