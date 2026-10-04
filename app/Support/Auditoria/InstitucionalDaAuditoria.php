<?php

namespace App\Support\Auditoria;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

/**
 * Coluna "bln_institucional" da trilha de auditoria: o registro é do ciclo
 * inteiro (sem unidade) e por isso aparece no feed de todos que acessam o ciclo.
 *
 * Grava sempre true ou false; o NULL fica só para os registros anteriores à
 * coluna, que o feed mostra apenas ao Super Admin. Regra em UnidadeDaAuditoria.
 *
 * Registrado em config/audit.php ("resolvers.bln_institucional").
 */
class InstitucionalDaAuditoria implements Resolver
{
    public static function resolve(Auditable $auditable): bool
    {
        return $auditable instanceof Model && UnidadeDaAuditoria::institucional($auditable);
    }
}
