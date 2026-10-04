<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Auditoria de segurança (PRM-05): o autocadastro passa a exigir confirmação
 * do e-mail (Fortify emailVerification + User implements MustVerifyEmail).
 *
 * Sem esta migration, toda conta antiga com email_verified_at nulo — inclusive
 * as migradas da v1 — ficaria presa na tela de verificação no próximo login.
 *
 * Só se marcam como verificadas as contas que JÁ TÊM perfil de acesso: foram
 * criadas ou aprovadas por um administrador, que respondeu pela identidade. A
 * conta de autocadastro ainda sem perfil continua não verificada e terá de
 * confirmar o e-mail — é exatamente o caso que a correção quer cobrir.
 *
 * SQL simples, compatível com PostgreSQL 9.3. Idempotente: só toca linhas nulas.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE pei.users
               SET email_verified_at = COALESCE(created_at, NOW())
             WHERE email_verified_at IS NULL
               AND EXISTS (
                   SELECT 1
                     FROM organization.rel_users_tab_organizacoes_tab_perfil_acesso v
                    WHERE v.user_id = pei.users.id
                      AND v.deleted_at IS NULL
               )
        ');
    }

    public function down(): void
    {
        // Irreversível de propósito: não há como saber quais contas estavam nulas.
    }
};
