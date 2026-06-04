<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige divergência entre a UI e o schema na Cadeia de Valor.
 *
 * O formulário de Atividade da Cadeia de Valor trata a Perspectiva BSC como
 * OPCIONAL (oferece a opção "Nenhuma" e rótulo "(opcional)"), mas a coluna
 * cod_perspectiva foi criada como NOT NULL. Ao salvar uma atividade sem
 * perspectiva, o componente injeta NULL e o INSERT viola a constraint,
 * resultando em erro 500.
 *
 * Esta migration torna cod_perspectiva NULLABLE, alinhando o banco à intenção
 * já expressa na interface. A foreign key (cascadeOnDelete) é preservada —
 * chaves estrangeiras aceitam NULL normalmente.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE strategic_planning.tab_atividade_cadeia_valor ALTER COLUMN cod_perspectiva DROP NOT NULL');
    }

    public function down(): void
    {
        // Reverter exige que não existam registros com cod_perspectiva NULL.
        DB::statement('ALTER TABLE strategic_planning.tab_atividade_cadeia_valor ALTER COLUMN cod_perspectiva SET NOT NULL');
    }
};
