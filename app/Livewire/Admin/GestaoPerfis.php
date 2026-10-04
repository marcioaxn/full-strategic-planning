<?php

namespace App\Livewire\Admin;

use App\Models\PerfilAcesso;
use App\Models\User;
use App\Services\Authorization\CapacidadeResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class GestaoPerfis extends Component
{
    use WithPagination;

    public string $buscaUsuario = '';

    protected string $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->authorize('modulo.acessar', 'admin.perfis');
    }

    /**
     * Matriz de permissões por perfil × funcionalidade.
     *
     * Era uma tabela ESCRITA À MÃO que dizia refletir as Policies — e não
     * refletia: mostrava "Leitura" no Planejamento e nos Indicadores para o
     * Gestor Responsável, que pela MATRIZ real cria e edita. A tela de perfis
     * mentia sobre quem pode o quê. Agora a tabela é DERIVADA da MATRIZ do
     * CapacidadeResolver (a mesma que decide cada acesso), lida por reflexão.
     *
     * Legenda: T=Total (criar, editar e excluir) · E=Edição (cria e/ou edita)
     * · L=Leitura · —=Sem acesso. O Super Admin tem tudo. Sobre isto ainda vale
     * o escopo: cada perfil só age nas unidades a que está vinculado.
     */
    public function getMatrizProperty(): array
    {
        $modulos = [
            'Planejamento (ciclo, identidade, análises, objetivos)' => 'planejamento-estrategico',
            'Indicadores' => 'indicadores',
            'Iniciativas' => 'planos-de-acao',
            'Entregas' => 'entregas',
            'Riscos' => 'riscos',
            'Graus de Satisfação' => 'graus-satisfacao',
            'Documentos' => 'documentos',
            'Relatórios' => 'relatorios',
            'Organizações' => 'organizacoes',
            'Usuários' => 'usuarios',
            'Perfis de Acesso' => 'admin.perfis',
            'Auditoria' => 'auditoria',
            'Configurações do Sistema' => 'admin.configuracoes',
        ];

        $matriz = (new \ReflectionClass(CapacidadeResolver::class))->getConstant('MATRIZ');

        $nivel = function (array $abilities): string {
            $escreve = array_intersect(['criar', 'editar'], $abilities);
            if ($escreve && in_array('excluir', $abilities, true) && in_array('criar', $abilities, true)) {
                return 'T';
            }
            if ($escreve) {
                return 'E';
            }

            return in_array('acessar', $abilities, true) ? 'L' : '—';
        };

        $perfis = [
            'Administrador Geral' => array_fill(0, count($modulos), 'T'),
        ];
        foreach ([
            'Admin de Unidade' => PerfilAcesso::ADMIN_UNIDADE,
            'Gestor Responsável' => PerfilAcesso::GESTOR_RESPONSAVEL,
            'Gestor Substituto' => PerfilAcesso::GESTOR_SUBSTITUTO,
            'Consulta' => PerfilAcesso::CONSULTA,
        ] as $rotulo => $codPerfil) {
            $perfis[$rotulo] = array_map(
                fn ($modulo) => $nivel($matriz[$modulo][$codPerfil] ?? []),
                array_values($modulos)
            );
        }

        return [
            'funcionalidades' => array_keys($modulos),
            'perfis' => $perfis,
        ];
    }

    public function getPerfisDescricaoProperty(): array
    {
        return [
            'Administrador Geral' => [
                'icon' => 'shield-lock-fill',
                'color' => 'danger',
                'desc' => 'Acesso irrestrito a todos os módulos, configurações e gestão de usuários. Pode assumir a identidade de qualquer usuário.',
                'flag' => 'adm = true',
            ],
            'Admin de Unidade' => [
                'icon' => 'building-gear',
                'color' => 'primary',
                'desc' => 'Gerencia os planos, entregas e dados estratégicos da sua organização. Pode criar e excluir planos da unidade.',
                'flag' => 'ADMIN_UNIDADE',
            ],
            'Gestor Responsável' => [
                'icon' => 'person-fill-gear',
                'color' => 'success',
                'desc' => 'Edita as iniciativas e entregas sob sua responsabilidade direta. Não exclui planos.',
                'flag' => 'GESTOR_RESPONSAVEL',
            ],
            'Gestor Substituto' => [
                'icon' => 'person-fill-up',
                'color' => 'info',
                'desc' => 'Substitui o gestor responsável na edição de planos e entregas vinculados. Mesmas permissões de edição.',
                'flag' => 'GESTOR_SUBSTITUTO',
            ],
        ];
    }

    public function render()
    {
        // Reautoriza a cada requisição: busca e paginação disparam updates, e quem
        // perdeu o Super Admin com a aba aberta continuava listando os usuários.
        $this->authorize('modulo.acessar', 'admin.perfis');

        // Pessoas DISTINTAS com vínculo ativo: um Gestor de duas iniciativas tem
        // dois vínculos, mas é uma pessoa — e vínculo excluído não conta.
        $perfis = PerfilAcesso::withCount([
            'usuarios as usuarios_count' => fn ($q) => $q->select(DB::raw('count(distinct users.id)')),
        ])->get();

        // Perfis carregados: o selo e o botão "Assumir" seguem o PERFIL (Super
        // Admin não é assumível), não a coluna legada `adm`.
        $usuarios = User::query()
            ->with('perfisAcesso')
            ->when($this->buscaUsuario, fn ($q) => $q->where(function ($sub) {
                $sub->where('name', 'ilike', '%'.$this->buscaUsuario.'%')
                    ->orWhere('email', 'ilike', '%'.$this->buscaUsuario.'%');
            }))
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->paginate(8);

        return view('livewire.admin.gestao-perfis', [
            'perfis' => $perfis,
            'usuarios' => $usuarios,
        ]);
    }
}
