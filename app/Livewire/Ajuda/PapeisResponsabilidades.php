<?php

namespace App\Livewire\Ajuda;

use App\Models\PerfilAcesso;
use App\Services\Authorization\CapacidadeResolver;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * "Quem pode fazer o quê" — a resposta à pergunta que travou o maior cliente.
 *
 * A Presidência da República não entendeu quem poderia lançar a evolução dos
 * indicadores, das Iniciativas e das Entregas. As regras existiam, eram
 * corretas e sofisticadas — e viviam em três lugares que o cliente não pode
 * ler: a MATRIZ do CapacidadeResolver, as Policies e o pivô perfil × usuário ×
 * iniciativa. Nenhuma tela traduzia isso.
 *
 * 🔴 Esta página é DERIVADA da matriz, nunca escrita à mão. Mudou a regra,
 * mudou a página. Documentação copiada envelhece na primeira alteração — e aí
 * volta a mentir para o cliente, que é pior do que não existir.
 */
#[Layout('layouts.app')]
class PapeisResponsabilidades extends Component
{
    /** Rótulo humano de cada módulo. Só apresentação; a fonte é a matriz. */
    private const ROTULOS = [
        'planejamento-estrategico' => 'Planejamento Estratégico (PEI, perspectivas, objetivos)',
        'planos-de-acao' => 'Iniciativas',
        'entregas' => 'Entregas',
        'indicadores' => 'Indicadores e lançamento de evolução',
        'riscos' => 'Gestão de Riscos',
        'organizacoes' => 'Organizações',
        'usuarios' => 'Usuários',
        'relatorios' => 'Relatórios',
        'graus-satisfacao' => 'Graus de Satisfação (a régua do farol)',
        'auditoria' => 'Auditoria',
        'admin.perfis' => 'Administração de Perfis',
        'admin.configuracoes' => 'Configurações do Sistema',
    ];

    private const DESCRICAO_ABILITY = [
        'acessar' => 'Abrir a tela e consultar',
        'criar' => 'Cadastrar novo registro',
        'editar' => 'Alterar registro existente e lançar evolução',
        'excluir' => 'Excluir registro',
        'exportar' => 'Exportar em PDF ou Excel',
    ];

    public function render()
    {
        $perfis = [
            PerfilAcesso::SUPER_ADMIN => 'Super Administrador',
            PerfilAcesso::ADMIN_UNIDADE => 'Admin de Unidade',
            PerfilAcesso::GESTOR_RESPONSAVEL => 'Gestor Responsável',
            PerfilAcesso::GESTOR_SUBSTITUTO => 'Gestor Substituto',
            PerfilAcesso::CONSULTA => 'Consulta',
        ];

        $linhas = [];

        foreach (array_keys(CapacidadeResolver::matriz()) as $modulo) {
            $celulas = [];

            foreach (array_keys($perfis) as $codPerfil) {
                $celulas[$codPerfil] = CapacidadeResolver::capacidadesDoPerfil($codPerfil, $modulo);
            }

            $linhas[] = [
                'modulo' => $modulo,
                'rotulo' => self::ROTULOS[$modulo] ?? $modulo,
                'restrito' => CapacidadeResolver::matriz()[$modulo] === [],
                'celulas' => $celulas,
            ];
        }

        return view('livewire.ajuda.papeis-responsabilidades', [
            'perfis' => $perfis,
            'linhas' => $linhas,
            'abilities' => CapacidadeResolver::ABILITIES,
            'descricoes' => self::DESCRICAO_ABILITY,
        ]);
    }
}
