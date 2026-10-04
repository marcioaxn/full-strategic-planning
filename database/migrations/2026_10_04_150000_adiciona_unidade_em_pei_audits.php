<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A trilha de auditoria passa a guardar A QUE UNIDADE cada registro pertence.
 *
 * É o que permite a aba "Atividade" do sino mostrar a cada pessoa só o que
 * acontece nas unidades do escopo dela. A unidade é derivada no momento da
 * gravação pelo resolvedor App\Support\Auditoria\UnidadeDaAuditoria
 * (config/audit.php, "resolvers").
 *
 * - cod_organizacao: a unidade do registro auditado (anulável).
 * - bln_institucional: true quando o registro é do ciclo inteiro (PEI,
 *   perspectiva, objetivo…) e por isso visível a todos que acessam o ciclo.
 *   Sem esta marca, "sem unidade" seria ambíguo: o registro institucional e o
 *   registro ANTIGO (gravado antes desta coluna) teriam o mesmo NULL.
 *
 * 🔴 O passado NÃO é preenchido aqui: os registros que já existem ficam com as
 * duas colunas nulas e só aparecem no feed do Super Admin.
 *
 * Compatível com PostgreSQL 9.3: ADD COLUMN e CREATE INDEX simples.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pei.audits', function (Blueprint $table) {
            $table->uuid('cod_organizacao')->nullable();
            $table->boolean('bln_institucional')->nullable();
        });

        Schema::table('pei.audits', function (Blueprint $table) {
            $table->index('cod_organizacao', 'audits_cod_organizacao_index');
            $table->index('created_at', 'audits_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('pei.audits', function (Blueprint $table) {
            $table->dropIndex('audits_cod_organizacao_index');
            $table->dropIndex('audits_created_at_index');
            $table->dropColumn(['cod_organizacao', 'bln_institucional']);
        });
    }
};
