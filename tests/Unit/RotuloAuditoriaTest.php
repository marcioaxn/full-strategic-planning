<?php

use App\Support\RotuloAuditoria;

it('traduz a classe auditada para o nome que o usuário conhece', function () {
    expect(RotuloAuditoria::registro('App\\Models\\ActionPlan\\PlanoDeAcao'))->toBe('Iniciativa')
        ->and(RotuloAuditoria::registro('App\\Models\\StrategicPlanning\\Objetivo'))->toBe('Objetivo estratégico');
});

it('mostra o nome curto da classe que não está no mapa, sem sumir com ela', function () {
    expect(RotuloAuditoria::registro('App\\Models\\Novo\\CoisaNova'))->toBe('CoisaNova')
        ->and(RotuloAuditoria::registro(null))->toBe('');
});

it('traduz o evento de auditoria', function () {
    expect(RotuloAuditoria::evento('deleted'))->toBe('Exclusão')
        ->and(RotuloAuditoria::evento('custom'))->toBe('Custom');
});

it('cobre toda classe auditável do sistema', function () {
    $modelos = dirname(__DIR__, 2).'/app/Models';
    $auditaveis = collect(array_merge(glob($modelos.'/*.php'), glob($modelos.'/*/*.php')))
        ->filter(fn ($arquivo) => str_contains(file_get_contents($arquivo), 'OwenIt\\Auditing\\Contracts\\Auditable'))
        ->map(fn ($arquivo) => basename($arquivo, '.php'))
        ->values();

    expect($auditaveis)->not->toBeEmpty();
    foreach ($auditaveis as $classe) {
        expect(RotuloAuditoria::REGISTROS)->toHaveKey($classe);
    }
});
