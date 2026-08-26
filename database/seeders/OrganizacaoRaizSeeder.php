<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Cria a organização raiz da instalação — a unidade institucional de topo da
 * hierarquia, à qual o Super Administrador é vinculado.
 *
 * O sistema identifica a raiz pela AUTO-REFERÊNCIA: `rel_cod_organizacao` aponta
 * para o próprio `cod_organizacao`. É esse critério que o seletor de organização
 * (App\Livewire\Shared\SeletorOrganizacao) e o Dashboard usam para montar a
 * árvore. Sem uma raiz auto-referenciada, nenhuma organização aparece na
 * interface após o login.
 *
 * PARA ADAPTAR À SUA INSTITUIÇÃO: altere apenas SIGLA e NOME abaixo e rode
 * novamente `php artisan db:seed`. NÃO altere COD_ORGANIZACAO — é o mesmo UUID
 * usado pela migration de criação da tabela, e mantê-lo evita organizações
 * duplicadas em bancos que já foram migrados.
 */
class OrganizacaoRaizSeeder extends Seeder
{
    private const TABELA = 'organization.tab_organizacoes';

    /** Identificador fixo da organização raiz — não alterar. */
    public const COD_ORGANIZACAO = '3834910f-66f7-46d8-9104-2904d59e1241';

    /** Sigla exibida no seletor de organização — troque pela sigla da sua instituição. */
    public const SIGLA = 'ORG';

    /** Nome completo da instituição — troque pelo nome da sua instituição. */
    public const NOME = 'Organização Padrão';

    public function run(): void
    {
        $dados = [
            'sgl_organizacao' => self::SIGLA,
            'nom_organizacao' => self::NOME,
            'rel_cod_organizacao' => self::COD_ORGANIZACAO, // auto-referência = raiz
            'deleted_at' => null,
            'updated_at' => now(),
        ];

        $existe = DB::table(self::TABELA)
            ->where('cod_organizacao', self::COD_ORGANIZACAO)
            ->exists();

        if ($existe) {
            DB::table(self::TABELA)
                ->where('cod_organizacao', self::COD_ORGANIZACAO)
                ->update($dados);
        } else {
            DB::table(self::TABELA)->insert($dados + [
                'cod_organizacao' => self::COD_ORGANIZACAO,
                'created_at' => now(),
            ]);
        }

        $this->command?->line('  Organização raiz: '.self::SIGLA.' — '.self::NOME.'.');
    }
}
