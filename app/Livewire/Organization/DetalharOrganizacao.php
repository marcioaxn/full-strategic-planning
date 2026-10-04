<?php

namespace App\Livewire\Organization;

use App\Models\Organization;
use App\Models\PerformanceIndicators\Indicador;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharOrganizacao extends Component
{
    use AuthorizesRequests;

    public $organizacao;

    public $estatisticas = [];

    public function mount($id)
    {
        $this->organizacao = Organization::with([
            'pai',
            'filhas',
            'usuarios',
            'planosAcao',
            'valores',
            'identidadeEstrategica.pei', // Carregar PEI da identidade
        ])->findOrFail($id);

        // 🔴 Abria para qualquer pessoa logada, de qualquer unidade — com a
        // lista de nome e e-mail dos usuários da unidade consultada.
        $this->authorize('view', $this->organizacao);

        $qtdIndicadores = Indicador::whereHas('organizacoes', function ($q) use ($id) {
            $q->where('tab_organizacoes.cod_organizacao', $id);
        })->count();

        $this->estatisticas = [
            'qtd_usuarios' => $this->organizacao->usuarios->count(),
            'qtd_filhas' => $this->organizacao->filhas->count(),
            'qtd_planos' => $this->organizacao->planosAcao->count(),
            'qtd_indicadores' => $qtdIndicadores,
        ];
    }

    public function render()
    {
        // A aba de usuários (nome e e-mail) só para quem administra usuários desta
        // unidade. Calculado a cada render: em propriedade pública, o navegador
        // trocava para true com $wire.set e a aba abria para Consulta e Gestores.
        return view('livewire.organization.detalhar-organizacao', [
            'podeVerUsuarios' => auth()->user()->can('modulo.acessar', ['usuarios', $this->organizacao->cod_organizacao]),
        ]);
    }
}
