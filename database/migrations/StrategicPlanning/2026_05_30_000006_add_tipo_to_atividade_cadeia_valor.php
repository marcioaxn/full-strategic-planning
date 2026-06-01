<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('strategic_planning.tab_atividade_cadeia_valor', function (Blueprint $table) {
            $table->string('dsc_tipo', 20)->default('Finalística')->after('dsc_atividade');
            $table->integer('num_ordem')->default(0)->after('dsc_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('strategic_planning.tab_atividade_cadeia_valor', function (Blueprint $table) {
            $table->dropColumn(['dsc_tipo', 'num_ordem']);
        });
    }
};
