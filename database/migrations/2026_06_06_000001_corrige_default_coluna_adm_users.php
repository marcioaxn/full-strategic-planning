<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrige o default da coluna "adm" (flag de Super Administrador).
 *
 * O default anterior era 2, fazendo todo novo usuário nascer como Super
 * Administrador (já que o cast booleano interpreta qualquer valor != 0 como
 * verdadeiro). O novo default é 0 — usuários nascem SEM privilégio de Super
 * Admin. A condição de Super Admin passou a ser determinada pelo PERFIL de
 * acesso vinculado (PerfilAcesso::SUPER_ADMIN).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users ALTER COLUMN adm SET DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users ALTER COLUMN adm SET DEFAULT 2');
    }
};
