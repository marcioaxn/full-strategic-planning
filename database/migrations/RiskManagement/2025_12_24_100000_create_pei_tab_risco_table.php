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
        Schema::create('risk_management.tab_risco', function (Blueprint $table) {
            $table->uuid('cod_risco')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('cod_pei')->references('cod_pei')->on('pei.tab_pei')->cascadeOnDelete();
            $table->foreignUuid('cod_organizacao')->references('cod_organizacao')->on('tab_organizacoes')->cascadeOnDelete();
            $table->integer('num_codigo_risco')->nullable(false); // Auto-incremento por PEI (gerenciado pela aplicação)
            $table->string('dsc_titulo', 255)->nullable(false);
            $table->text('txt_descricao')->nullable(false);
            $table->string('dsc_categoria', 50)->nullable(false); // Operacional, Financeiro, Reputacional, Legal, Tecnológico, Estratégico, Ambiental
            $table->string('dsc_status', 50)->nullable(false); // Identificado, Em Análise, Monitorado, Mitigado, Materializado, Encerrado
            $table->smallInteger('num_probabilidade')->nullable(false); // 1-5
            $table->smallInteger('num_impacto')->nullable(false); // 1-5
            $table->smallInteger('num_nivel_risco')->nullable(false); // Calculado: probabilidade * impacto (1-25)
            $table->text('txt_causas')->nullable(true);
            $table->text('txt_consequencias')->nullable(true);
            $table->foreignUuid('cod_responsavel_monitoramento')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index('cod_pei');
            $table->index('cod_organizacao');
            $table->index('dsc_categoria');
            $table->index('dsc_status');
            $table->index('num_nivel_risco');
            $table->index(['cod_pei', 'num_codigo_risco']);
        });

        // Validações CHECK via SQL raw (PostgreSQL)
        DB::statement('ALTER TABLE pei.tab_risco ADD CONSTRAINT chk_probabilidade CHECK (num_probabilidade >= 1 AND num_probabilidade <= 5)');
        DB::statement('ALTER TABLE pei.tab_risco ADD CONSTRAINT chk_impacto CHECK (num_impacto >= 1 AND num_impacto <= 5)');
        DB::statement('ALTER TABLE pei.tab_risco ADD CONSTRAINT chk_nivel_risco CHECK (num_nivel_risco >= 1 AND num_nivel_risco <= 25)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pei.tab_risco');
    }
};
