<?php

/*
 * Acervo de Documentos (pedido do cliente em 03/10/2026). Os testes vão pelo
 * caminho da tela (ListarDocumentos::save, a rota que entrega o PDF) e por
 * perfil: envia o Super Admin e o Administrador da Unidade; Gestores e
 * Consulta só leem — e só o que está no escopo deles.
 */

use App\Livewire\Documentos\ListarDocumentos;
use App\Models\Documento;
use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake(Documento::DISCO);
});

function pdfDeTeste(string $nome = 'portaria.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($nome, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
}

function usuarioDoAcervo(string $perfil, Organization $org): User
{
    $user = User::factory()->create();
    $user->perfisAcesso()->attach($perfil, ['cod_organizacao' => $org->cod_organizacao]);
    $user->unsetRelation('perfisAcesso');

    return $user;
}

function arvoreDocumentos(): array
{
    $raiz = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'rel_cod_organizacao' => null]);
    $a = Organization::create(['nom_organizacao' => 'Unidade A', 'sgl_organizacao' => 'UA', 'rel_cod_organizacao' => $raiz->cod_organizacao]);
    $b = Organization::create(['nom_organizacao' => 'Unidade B', 'sgl_organizacao' => 'UB', 'rel_cod_organizacao' => $raiz->cod_organizacao]);

    return [$raiz, $a, $b];
}

test('o Administrador da Unidade envia um PDF para a sua unidade, com o arquivo guardado no disco privado', function () {
    [, $a] = arvoreDocumentos();
    $admin = usuarioDoAcervo(PerfilAcesso::ADMIN_UNIDADE, $a);

    Livewire::actingAs($admin)->test(ListarDocumentos::class)
        ->call('create')
        ->set('arquivo', pdfDeTeste())
        ->set('nom_documento', 'Portaria que aprova o PEI')
        ->set('dsc_tipo', 'Portaria Conjunta')
        ->set('num_ano_referencia', '2026')
        ->set('dsc_origem', 'Gabinete')
        ->set('cod_organizacao', $a->cod_organizacao)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $doc = Documento::sole();
    expect($doc->dsc_tipo)->toBe('Portaria Conjunta')
        ->and($doc->num_ano_referencia)->toBe(2026)
        ->and($doc->cod_usuario)->toBe($admin->id)
        ->and($doc->dsc_hash_sha256)->toHaveLength(64)
        ->and($doc->dsc_caminho)->toStartWith('documentos/');
    Storage::disk(Documento::DISCO)->assertExists($doc->dsc_caminho);
});

test('arquivo, nome e tipo são obrigatórios; os demais campos não', function () {
    [, $a] = arvoreDocumentos();
    $admin = usuarioDoAcervo(PerfilAcesso::ADMIN_UNIDADE, $a);

    Livewire::actingAs($admin)->test(ListarDocumentos::class)
        ->call('create')
        ->set('cod_organizacao', $a->cod_organizacao)
        ->call('save')
        ->assertHasErrors(['arquivo' => 'required', 'nom_documento' => 'required', 'dsc_tipo' => 'required'])
        ->assertHasNoErrors(['num_ano_referencia', 'dsc_origem', 'cod_pei']);

    expect(Documento::count())->toBe(0);
});

test('recusa arquivo que não é PDF, mesmo com a extensão .pdf', function () {
    [, $a] = arvoreDocumentos();
    $admin = usuarioDoAcervo(PerfilAcesso::ADMIN_UNIDADE, $a);

    Livewire::actingAs($admin)->test(ListarDocumentos::class)
        ->call('create')
        ->set('arquivo', UploadedFile::fake()->createWithContent('falso.pdf', 'MZ isto é um executável'))
        ->set('nom_documento', 'Falso')
        ->set('dsc_tipo', 'Decreto')
        ->set('cod_organizacao', $a->cod_organizacao)
        ->call('save')
        ->assertHasErrors(['arquivo']);

    expect(Documento::count())->toBe(0);
});

test('o Administrador da Unidade não envia documento institucional nem para outra unidade', function () {
    [, $a, $b] = arvoreDocumentos();
    $admin = usuarioDoAcervo(PerfilAcesso::ADMIN_UNIDADE, $a);

    foreach (['', $b->cod_organizacao] as $destino) {
        Livewire::actingAs($admin)->test(ListarDocumentos::class)
            ->set('arquivo', pdfDeTeste())
            ->set('nom_documento', 'Decreto')
            ->set('dsc_tipo', 'Decreto')
            ->set('cod_organizacao', $destino)
            ->call('save')
            ->assertHasErrors(['cod_organizacao']);
    }

    expect(Documento::count())->toBe(0);
});

test('Gestores e Consulta não enviam; leem só os documentos do seu escopo e os institucionais', function (string $perfil) {
    [, $a, $b] = arvoreDocumentos();
    $leitor = usuarioDoAcervo($perfil, $a);

    $base = ['dsc_tipo' => 'Decreto', 'dsc_nome_arquivo' => 'x.pdf', 'num_tamanho_bytes' => 10, 'dsc_hash_sha256' => str_repeat('0', 64)];
    $daA = Documento::create($base + ['nom_documento' => 'Da unidade A', 'cod_organizacao' => $a->cod_organizacao, 'dsc_caminho' => 'documentos/a.pdf']);
    $daB = Documento::create($base + ['nom_documento' => 'Da unidade B', 'cod_organizacao' => $b->cod_organizacao, 'dsc_caminho' => 'documentos/b.pdf']);
    Documento::create($base + ['nom_documento' => 'Institucional', 'cod_organizacao' => null, 'dsc_caminho' => 'documentos/i.pdf']);
    Storage::disk(Documento::DISCO)->put('documentos/a.pdf', '%PDF-1.4');
    Storage::disk(Documento::DISCO)->put('documentos/b.pdf', '%PDF-1.4');

    Livewire::actingAs($leitor)->test(ListarDocumentos::class)
        ->assertSee('Da unidade A')
        ->assertSee('Institucional')
        ->assertDontSee('Da unidade B')
        ->assertDontSee('Enviar documento')
        ->set('arquivo', pdfDeTeste())
        ->set('nom_documento', 'Tentativa')
        ->set('dsc_tipo', 'Decreto')
        ->set('cod_organizacao', $a->cod_organizacao)
        ->call('save')
        ->assertHasErrors(['cod_organizacao']);

    expect(Documento::count())->toBe(3);

    $this->actingAs($leitor)->get(route('acervo.arquivo', $daA->cod_documento))->assertOk();
    $this->actingAs($leitor)->get(route('acervo.arquivo', $daB->cod_documento))
        ->assertRedirect()
        ->assertHeaderMissing('Content-Disposition');
})->with([
    'Gestor Responsável' => PerfilAcesso::GESTOR_RESPONSAVEL,
    'Gestor Substituto' => PerfilAcesso::GESTOR_SUBSTITUTO,
    'Consulta' => PerfilAcesso::CONSULTA,
]);

test('o Super Administrador envia documento institucional e o baixa com o nome original', function () {
    [$raiz] = arvoreDocumentos();
    $super = usuarioDoAcervo(PerfilAcesso::SUPER_ADMIN, $raiz);

    Livewire::actingAs($super)->test(ListarDocumentos::class)
        ->call('create')
        ->assertSet('cod_organizacao', '')
        ->set('arquivo', pdfDeTeste('Relatório de Gestão 2025.pdf'))
        ->set('nom_documento', 'Relatório de Gestão 2025')
        ->set('dsc_tipo', 'Relatório de Gestão')
        ->call('save')
        ->assertHasNoErrors();

    $doc = Documento::sole();
    expect($doc->cod_organizacao)->toBeNull();

    $this->actingAs($super)->get(route('acervo.arquivo', ['documento' => $doc->cod_documento, 'baixar' => 1]))
        ->assertOk()
        ->assertDownload('Relatorio de Gestao 2025.pdf')
        // O navegador usa o filename* (UTF-8): o arquivo chega com os acentos.
        ->assertHeader('Content-Disposition', "attachment; filename=\"Relatorio de Gestao 2025.pdf\"; filename*=utf-8''Relat%C3%B3rio%20de%20Gest%C3%A3o%202025.pdf");
});

test('filtro e id forjados na tela não viram erro de banco', function () {
    [, $a] = arvoreDocumentos();
    $admin = usuarioDoAcervo(PerfilAcesso::ADMIN_UNIDADE, $a);

    $this->actingAs($admin)->get(route('acervo.index', ['filtroPei' => 'nao-e-uuid']))->assertOk();

    Livewire::actingAs($admin)->test(ListarDocumentos::class)
        ->call('edit', 'nao-e-uuid')
        ->assertNotFound();
});
