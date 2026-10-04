<?php

namespace App\Support\Auditoria;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

/**
 * Coluna "tags" da trilha de auditoria: as marcas do próprio model e, durante a
 * impersonação, "impersonando:<id do usuário assumido>".
 *
 * O autor do registro é o Super Admin (AutorDaAuditoria); esta marca diz em
 * nome de quem ele navegava. Os resolvedores do pacote rodam depois das marcas
 * do model e as sobrescrevem — por isso as do model são repetidas aqui.
 *
 * Registrado em config/audit.php ("resolvers.tags").
 */
class MarcaDeImpersonacao implements Resolver
{
    public const PREFIXO = 'impersonando:';

    public static function resolve(Auditable $auditable): ?string
    {
        $marcas = method_exists($auditable, 'generateTags') ? $auditable->generateTags() : [];

        if ($assumido = AutorDaAuditoria::assumido()) {
            $marcas[] = self::PREFIXO.$assumido->getAuthIdentifier();
        }

        $marcas = array_values(array_filter($marcas, fn ($m) => is_string($m) && $m !== ''));

        return $marcas === [] ? null : implode(',', $marcas);
    }

    /** O id do usuário assumido gravado nas marcas, se houver. */
    public static function idAssumido(?string $tags): ?string
    {
        foreach (explode(',', (string) $tags) as $marca) {
            if (str_starts_with($marca, self::PREFIXO)) {
                return substr($marca, strlen(self::PREFIXO)) ?: null;
            }
        }

        return null;
    }
}
