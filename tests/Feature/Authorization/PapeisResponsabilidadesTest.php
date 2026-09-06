<?php

/*
 * A página "Papéis e responsabilidades" existe porque o maior cliente não
 * entendeu quem poderia lançar a evolução dos indicadores, das Iniciativas e
 * das Entregas. As regras existiam e estavam certas — o sistema é que nunca
 * as dizia a ninguém.
 *
 * O teste que importa aqui não é "a página abre". É que ela seja DERIVADA da
 * matriz: documentação copiada envelhece na primeira mudança de regra, e aí
 * volta a mentir para o cliente — pior do que não existir.
 */

use App\Livewire\Ajuda\PapeisResponsabilidades;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use App\Services\Authorization\CapacidadeResolver;
use Livewire\Livewire;

function usuarioAjuda(string $perfil): User
{
    $org = Organization::firstOrCreate(
        ['sgl_organizacao' => 'ORGAJ'],
        ['nom_organizacao' => 'Org Ajuda', 'cod_organizacao_pai' => null]
    );

    $user = User::factory()->create();
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    return $user;
}

test('todo perfil consegue abrir a página de papéis', function () {
    // Não faz sentido restringir a explicação de quem pode o quê: é a resposta
    // a uma dúvida, não um dado sensível.
    foreach ([
        PerfilAcesso::SUPER_ADMIN,
        PerfilAcesso::ADMIN_UNIDADE,
        PerfilAcesso::GESTOR_RESPONSAVEL,
        PerfilAcesso::GESTOR_SUBSTITUTO,
    ] as $perfil) {
        Livewire::actingAs(usuarioAjuda($perfil))
            ->test(PapeisResponsabilidades::class)
            ->assertOk();
    }
});

test('a página lista TODOS os módulos da matriz — nenhum fica de fora', function () {
    $componente = Livewire::actingAs(usuarioAjuda(PerfilAcesso::ADMIN_UNIDADE))
        ->test(PapeisResponsabilidades::class);

    $modulosNaPagina = collect($componente->viewData('linhas'))->pluck('modulo')->all();

    expect($modulosNaPagina)->toBe(array_keys(CapacidadeResolver::matriz()));
});

test('a página REFLETE a matriz: mudar a regra muda a página', function () {
    // Se alguém trocar a tabela por texto escrito à mão, este teste falha.
    $componente = Livewire::actingAs(usuarioAjuda(PerfilAcesso::ADMIN_UNIDADE))
        ->test(PapeisResponsabilidades::class);

    $linhas = collect($componente->viewData('linhas'))->keyBy('modulo');

    foreach (CapacidadeResolver::matriz() as $modulo => $porPerfil) {
        foreach ([
            PerfilAcesso::ADMIN_UNIDADE,
            PerfilAcesso::GESTOR_RESPONSAVEL,
            PerfilAcesso::GESTOR_SUBSTITUTO,
        ] as $perfil) {
            expect($linhas[$modulo]['celulas'][$perfil])
                ->toBe($porPerfil[$perfil] ?? [], "módulo [{$modulo}], perfil [{$perfil}]");
        }
    }
});

test('o Super Admin aparece com todas as capacidades', function () {
    // Ele não está na matriz — é liberado incondicionalmente em podeNoModulo().
    // A página tem de dizer isso, senão parece que ele não pode nada.
    $componente = Livewire::actingAs(usuarioAjuda(PerfilAcesso::ADMIN_UNIDADE))
        ->test(PapeisResponsabilidades::class);

    foreach (collect($componente->viewData('linhas')) as $linha) {
        expect($linha['celulas'][PerfilAcesso::SUPER_ADMIN])
            ->toBe(CapacidadeResolver::ABILITIES);
    }
});

test('os módulos restritos ao Super Admin estão marcados como restritos', function () {
    $componente = Livewire::actingAs(usuarioAjuda(PerfilAcesso::ADMIN_UNIDADE))
        ->test(PapeisResponsabilidades::class);

    $restritosNaPagina = collect($componente->viewData('linhas'))
        ->where('restrito', true)
        ->pluck('modulo')
        ->values()
        ->all();

    expect($restritosNaPagina)->toBe(CapacidadeResolver::modulosRestritos());
});

test('Graus de Satisfação NÃO é mais restrito ao Super Admin', function () {
    // Regressão da correção que destravou o ciclo: o guia mandava o cliente
    // configurar as faixas e a tela devolvia 403.
    expect(CapacidadeResolver::modulosRestritos())->not->toContain('graus-satisfacao');
});
