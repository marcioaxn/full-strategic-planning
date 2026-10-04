<?php

/*
 * Detalhe da auditoria (pedido de 03/10/2026): "nenhum humano consegue ler".
 * A tela mostrava "dsc_origem", UUID de cod_pei e "null". A asserção é sobre
 * o HTML que o Super Admin recebe pela rota da tela.
 */

use App\Models\Documento;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\StrategicPlanning\PEI;
use App\Models\User;
use App\Support\AuditoriaLegivel;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

function superAdminDaAuditoria(): User
{
    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $user = User::factory()->create(['name' => 'Ana Auditora']);
    $user->perfisAcesso()->attach(PerfilAcesso::SUPER_ADMIN, ['cod_organizacao' => $raiz->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    return $user;
}

function auditoriaDeDocumento(User $autor, array $antes, array $depois, string $evento = 'updated'): Audit
{
    return Audit::create([
        'user_type' => User::class,
        'user_id' => $autor->id,
        'event' => $evento,
        'auditable_type' => Documento::class,
        'auditable_id' => (string) Str::uuid(),
        'old_values' => $antes,
        'new_values' => $depois,
        'url' => 'http://localhost/fs-v1/public/livewire/update',
        'ip_address' => '::1',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36',
    ]);
}

test('o detalhe traduz campos, chaves estrangeiras e vazios para quem lê', function () {
    $admin = superAdminDaAuditoria();
    $antigo = PEI::create(['dsc_pei' => 'Ciclo antigo', 'num_ano_inicio_pei' => 2020, 'num_ano_fim_pei' => 2023]);
    $novo = PEI::create(['dsc_pei' => 'Ciclo novo', 'num_ano_inicio_pei' => 2024, 'num_ano_fim_pei' => 2027]);

    $log = auditoriaDeDocumento($admin,
        ['dsc_origem' => null, 'cod_pei' => $antigo->cod_pei, 'nom_documento' => 'Portaria 1'],
        ['dsc_origem' => 'Secretaria B', 'cod_pei' => $novo->cod_pei, 'nom_documento' => 'Portaria 1']
    );

    $resposta = $this->actingAs($admin)->get(route('audit.detalhes', $log->id))->assertOk();

    // A frase-resumo, os rótulos e os nomes no lugar dos UUIDs.
    expect(preg_replace('/\s+/', ' ', $resposta->getContent()))->toContain('alterou o documento');

    $resposta->assertSee('Ana Auditora')
        ->assertSee('Origem')
        ->assertSee('Ciclo PEI')
        ->assertSee('Ciclo antigo (2020–2023)')
        ->assertSee('Ciclo novo (2024–2027)')
        ->assertSee('(vazio)')
        ->assertSee('Chrome 154 · Windows');

    // O que era ilegível não aparece mais fora dos detalhes técnicos.
    $principal = strip_tags(explode('Detalhes técnicos', $resposta->getContent())[0]);
    expect($principal)->not->toContain('dsc_origem')
        ->and($principal)->not->toContain('cod_pei')
        ->and($principal)->not->toContain($novo->cod_pei)
        ->and($principal)->not->toContain('>null<');
});

test('texto longo mostra a diferença palavra a palavra', function () {
    $trechos = AuditoriaLegivel::diferencaPorPalavra(
        'Promover o desenvolvimento regional sustentável com foco nas pessoas',
        'Promover o desenvolvimento regional inclusivo com foco nas pessoas'
    );

    expect($trechos)->toContain(['removido', 'sustentável'])
        ->and($trechos)->toContain(['incluido', 'inclusivo'])
        ->and(collect($trechos)->where(0, 'igual')->pluck(1)->implode(''))->toContain('Promover o desenvolvimento regional');
});

test('senha nunca é exibida e o valor do usuário sai escapado', function () {
    $admin = superAdminDaAuditoria();
    $log = auditoriaDeDocumento($admin, [], ['password' => 'hash-secreto', 'nom_documento' => '<img src=x onerror=alert(1)>'], 'created');

    $this->actingAs($admin)->get(route('audit.detalhes', $log->id))
        ->assertOk()
        ->assertDontSee('hash-secreto')
        ->assertSee('(conteúdo sigiloso — não exibido)')
        ->assertDontSee('<img src=x onerror=alert(1)>', false);
});

test('quem não é Super Admin não abre o detalhe', function () {
    $admin = superAdminDaAuditoria();
    $log = auditoriaDeDocumento($admin, [], ['nom_documento' => 'x'], 'created');
    $org = Organization::first();
    $gestor = User::factory()->create();
    $gestor->perfisAcesso()->attach(PerfilAcesso::ADMIN_UNIDADE, ['cod_organizacao' => $org->cod_organizacao]);

    // Acesso negado em página inteira volta ao painel (tratamento padrão do sistema).
    $this->actingAs($gestor)->get(route('audit.detalhes', $log->id))
        ->assertRedirect(route('dashboard'))
        ->assertDontSee('Ana Auditora');
});
