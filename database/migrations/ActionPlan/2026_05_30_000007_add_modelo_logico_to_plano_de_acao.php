<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('action_plan.tab_plano_de_acao', function (Blueprint $table) {
            $table->jsonb('json_modelo_logico')->nullable()->after('txt_detalhamento');
        });
    }

    public function down(): void
    {
        Schema::table('action_plan.tab_plano_de_acao', function (Blueprint $table) {
            $table->dropColumn('json_modelo_logico');
        });
    }
};
