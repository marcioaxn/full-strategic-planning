<?php

namespace App\Support\Auditoria;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use OwenIt\Auditing\Contracts\UserResolver;
use OwenIt\Auditing\Resolvers\UserResolver as ResolvedorPadrao;

/**
 * Quem responde pela alteração gravada na trilha de auditoria (pei.audits).
 *
 * 🔴 Durante a impersonação, o guard devolve o usuário ASSUMIDO. Com o
 * resolvedor padrão, o que o Super Admin fazia "como" um Gestor ficava na
 * trilha em nome do Gestor — e a única pista era uma linha no log em arquivo.
 * Aqui o autor é sempre quem está ao teclado; quem estava sendo assumido vai
 * na marca (MarcaDeImpersonacao). Teste: ImpersonacaoComTrocaDeSenhaPendenteTest.
 *
 * Registrado em config/audit.php ("user.resolver").
 */
class AutorDaAuditoria implements UserResolver
{
    public static function resolve(): ?Authenticatable
    {
        $logado = ResolvedorPadrao::resolve();

        if (! $logado) {
            return null;
        }

        return static::impersonador() ?? $logado;
    }

    /** O Super Admin por trás de uma impersonação ativa, se houver. */
    public static function impersonador(): ?User
    {
        $id = static::idDoImpersonador();

        return $id ? User::find($id) : null;
    }

    /** O usuário assumido (o logado), quando há impersonação ativa. */
    public static function assumido(): ?Authenticatable
    {
        return static::idDoImpersonador() ? ResolvedorPadrao::resolve() : null;
    }

    private static function idDoImpersonador(): ?string
    {
        if (! app()->bound('session')) {
            return null;
        }

        try {
            $id = session('impersonator_id');
        } catch (\Throwable) {
            return null;
        }

        return is_string($id) && $id !== '' ? $id : null;
    }
}
