<?php

namespace App\Support;

/**
 * Como um registro auditado e um evento de auditoria se leem para o usuário.
 *
 * A tabela de auditoria guarda o nome da classe ("App\Models\ActionPlan\PlanoDeAcao")
 * e o evento em inglês ("deleted"). Na tela, isso vira "Iniciativa" e "Exclusão".
 * Classe auditada que não estiver no mapa aparece pelo nome curto, nunca some.
 */
class RotuloAuditoria
{
    /** @var array<string, string> */
    public const REGISTROS = [
        'PlanoDeAcao' => 'Iniciativa',
        'Indicador' => 'Indicador',
        'Objetivo' => 'Objetivo estratégico',
        'Risco' => 'Risco',
        'RiscoMitigacao' => 'Mitigação de risco',
        'RiscoOcorrencia' => 'Ocorrência de risco',
        'IdentidadeEstrategica' => 'Identidade estratégica',
        'TemaNorteador' => 'Tema norteador',
        'Valor' => 'Valor',
        'Documento' => 'Documento',
    ];

    /** @var array<string, string> */
    public const EVENTOS = [
        'created' => 'Criação',
        'updated' => 'Alteração',
        'deleted' => 'Exclusão',
        'restored' => 'Restauração',
    ];

    public static function registro(?string $auditableType): string
    {
        $classe = class_basename((string) $auditableType);

        return self::REGISTROS[$classe] ?? $classe;
    }

    public static function evento(?string $evento): string
    {
        return self::EVENTOS[(string) $evento] ?? ucfirst((string) $evento);
    }
}
