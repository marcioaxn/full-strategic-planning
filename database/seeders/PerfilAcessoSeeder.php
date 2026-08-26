<?php

namespace Database\Seeders;

use App\Models\PerfilAcesso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Popula os quatro perfis de acesso do sistema (dado de referência, não de negócio).
 *
 * Os identificadores NÃO podem ser alterados: são constantes em
 * App\Models\PerfilAcesso e estão referenciados em Policies, Gates e no
 * CapacidadeResolver. Um UUID diferente aqui significa um sistema em que
 * ninguém tem permissão de nada.
 */
class PerfilAcessoSeeder extends Seeder
{
    private const TABELA = 'organization.tab_perfil_acesso';

    /**
     * @return array<int, array{cod_perfil: string, dsc_perfil: string, dsc_permissao: string}>
     */
    public static function perfis(): array
    {
        return [
            [
                'cod_perfil' => PerfilAcesso::SUPER_ADMIN,
                'dsc_perfil' => 'Super Administrador',
                'dsc_permissao' => 'Servidor(a) com todos os privilégios de administração do sistema',
            ],
            [
                'cod_perfil' => PerfilAcesso::ADMIN_UNIDADE,
                'dsc_perfil' => 'Administrador da Unidade',
                'dsc_permissao' => 'Servidor(a) com todos os privilégios de administração do sistema somente dentro da Unidade que está cadastrado',
            ],
            [
                'cod_perfil' => PerfilAcesso::GESTOR_RESPONSAVEL,
                'dsc_perfil' => 'Gestor(a) Responsável',
                'dsc_permissao' => 'Servidor(a) que tem como responsabilidade manter a atualização do Plano de Ação ao qual está como responsável',
            ],
            [
                'cod_perfil' => PerfilAcesso::GESTOR_SUBSTITUTO,
                'dsc_perfil' => 'Gestor(a) Substituto(a)',
                'dsc_permissao' => 'Servidor(a) que tem como responsabilidade manter a atualização do Plano de Ação ao qual está como substituto(a)',
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::perfis() as $perfil) {
            $existe = DB::table(self::TABELA)
                ->where('cod_perfil', $perfil['cod_perfil'])
                ->exists();

            if ($existe) {
                DB::table(self::TABELA)
                    ->where('cod_perfil', $perfil['cod_perfil'])
                    ->update([
                        'dsc_perfil' => $perfil['dsc_perfil'],
                        'dsc_permissao' => $perfil['dsc_permissao'],
                        'deleted_at' => null,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table(self::TABELA)->insert($perfil + [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command?->line('  Perfis de acesso cadastrados: '.count(self::perfis()).'.');
    }
}
