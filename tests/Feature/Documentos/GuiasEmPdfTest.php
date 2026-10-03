<?php

/*
 * Os guias metodológicos em PDF.
 *
 * 🔴 POR QUE ISTO EXISTE
 * A tela /guia-gppei mostrava "404 not found" no lugar do documento. O viewer
 * estava certo: o arquivo é que não existia no checkout. `documentacao/pdf/`
 * caía na regra `/documentacao/**\/*.pdf` do .gitignore, feita para binários de
 * escritório — só que estes dois PDFs não são documentação de apoio, são ativos
 * que a aplicação serve por rota. Todo deploy novo nascia com a tela quebrada.
 *
 * O .gitignore hoje abre exceção para a pasta. Este teste existe para que, se
 * alguém fechar a exceção de novo ou mexer no caminho, a suíte diga onde — em
 * vez de o cliente descobrir pelo 404.
 */

use App\Models\Organization;
use App\Models\PerfilAcesso;
use App\Models\User;

/** Usuário com perfil: conta sem perfil não entra na área restrita. */
function usuarioComPerfilParaGuias(): User
{
    $org = Organization::create(['nom_organizacao' => 'Órgão', 'sgl_organizacao' => 'ORG', 'cod_organizacao_pai' => null]);
    $user = User::factory()->create();
    $user->perfisAcesso()->attach(PerfilAcesso::CONSULTA, ['cod_organizacao' => $org->cod_organizacao]);

    return $user;
}

test('os PDFs que a aplicação serve estão no repositório', function () {
    // Os mesmos caminhos que o DocumentosController lê.
    expect(base_path('documentacao/pdf/Guia_PEI_VF.pdf'))->toBeReadableFile()
        ->and(base_path('documentacao/pdf/guia-pratico-de-projetos.pdf'))->toBeReadableFile();
});

test('a rota do guia GPPEI entrega o PDF, não um 404', function () {
    $this->actingAs(usuarioComPerfilParaGuias());

    $this->get(route('documentos.gppei'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('a rota do guia de projetos entrega o PDF, não um 404', function () {
    $this->actingAs(usuarioComPerfilParaGuias());

    $this->get(route('documentos.projetos.pdf'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('o viewer aponta para a rota do PDF, e não para um caminho solto', function () {
    // O viewer monta o src do iframe com route('documentos.gppei'). Se alguém
    // trocar por um caminho em public/, o arquivo privado vira público.
    $blade = file_get_contents(resource_path('views/documentos/viewer-gppei.blade.php'));

    expect($blade)->toContain("route('documentos.gppei')");
});
