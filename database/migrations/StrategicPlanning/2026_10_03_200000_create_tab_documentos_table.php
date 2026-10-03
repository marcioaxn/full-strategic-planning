<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acervo de documentos em PDF (pedido do cliente em 03/10/2026): decretos,
 * portarias, relatórios de gestão e afins, ligados opcionalmente a um PEI e a
 * uma unidade.
 *
 * O arquivo fica no disco privado (storage/app/private/documentos); a tabela
 * guarda o caminho, o nome original, o tamanho e o SHA-256 — para provar que o
 * arquivo baixado é o mesmo que foi enviado.
 *
 * Os tipos de documento são vocabulário controlado em código
 * (App\Models\Documento::TIPOS), não linhas no banco: nada a semear.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_planning.tab_documentos', function (Blueprint $table) {
            $table->uuid('cod_documento')->primary();
            $table->string('nom_documento', 255);
            $table->string('dsc_tipo', 80);
            $table->string('num_documento', 60)->nullable();
            $table->smallInteger('num_ano_referencia')->nullable();
            $table->date('dte_documento')->nullable();
            $table->string('dsc_origem', 255)->nullable();
            $table->text('txt_descricao')->nullable();
            $table->string('dsc_link', 500)->nullable();
            $table->uuid('cod_pei')->nullable();
            $table->uuid('cod_organizacao')->nullable();
            $table->string('dsc_nome_arquivo', 255);
            $table->string('dsc_caminho', 500);
            $table->bigInteger('num_tamanho_bytes');
            $table->string('dsc_hash_sha256', 64);
            $table->uuid('cod_usuario')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('cod_pei')->references('cod_pei')->on('strategic_planning.tab_pei')->nullOnDelete();
            $table->foreign('cod_organizacao')->references('cod_organizacao')->on('organization.tab_organizacoes')->nullOnDelete();
            $table->index('cod_pei');
            $table->index('cod_organizacao');
            $table->index('dsc_tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_planning.tab_documentos');
    }
};
