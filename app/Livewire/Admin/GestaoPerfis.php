<?php

namespace App\Livewire\Admin;

use App\Models\PerfilAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
     * Reflete a lógica real implementada nas Policies do sistema.
     * Legenda: T=Total(CRUD) · E=Edição · L=Leitura · —=Sem acesso
     */
    public function getMatrizProperty(): array
    {
        return [
            'funcionalidades' => [
                'Configurações do Sistema',
                'Organizações',
                'Usuários e Perfis',
                'Ciclo PEI / Identidade',
                'Objetivos e Indicadores',
                'Planos de Ação',
                'Entregas',
                'Riscos',
                'Relatórios',
            ],
            'perfis' => [
                'Administrador Geral' => ['T', 'T', 'T', 'T', 'T', 'T', 'T', 'T', 'T'],
                'Admin de Unidade' => ['—', 'L', '—', 'E', 'E', 'T', 'T', 'E', 'L'],
                'Gestor Responsável' => ['—', 'L', '—', 'L', 'L', 'E', 'E', 'L', 'L'],
                'Gestor Substituto' => ['—', 'L', '—', 'L', 'L', 'E', 'E', 'L', 'L'],
            ],
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
                'desc' => 'Edita os planos de ação e entregas sob sua responsabilidade direta. Não exclui planos.',
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
        $perfis = PerfilAcesso::withCount('usuarios')->get();

        $usuarios = User::query()
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
