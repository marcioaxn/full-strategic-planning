<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            ALTER TABLE strategic_planning.tab_objetivo
                ADD COLUMN cod_objetivo_pai UUID NULL
                    REFERENCES strategic_planning.tab_objetivo(cod_objetivo) ON DELETE SET NULL,
                ADD COLUMN num_nivel_desdobramento SMALLINT NOT NULL DEFAULT 1
        ');

        DB::statement('CREATE INDEX idx_objetivo_pai ON strategic_planning.tab_objetivo(cod_objetivo_pai)');
        DB::statement('CREATE INDEX idx_objetivo_nivel ON strategic_planning.tab_objetivo(num_nivel_desdobramento)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS strategic_planning.idx_objetivo_pai');
        DB::statement('DROP INDEX IF EXISTS strategic_planning.idx_objetivo_nivel');
        DB::statement('
            ALTER TABLE strategic_planning.tab_objetivo
                DROP COLUMN IF EXISTS cod_objetivo_pai,
                DROP COLUMN IF EXISTS num_nivel_desdobramento
        ');
    }
};
