<?php

namespace App\Livewire\RiskManagement;

use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\PEI;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class MatrizRiscos extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public $organizacaoId;

    public $matriz = [];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarMatriz',
    ];

    public function mount()
    {
        $this->authorize('modulo.acessar', 'riscos');

        // A sessão guarda preferência, não permissão: vale só dentro do escopo.
        $this->organizacaoId = Auth::user()->organizacaoSelecionadaId();
        $this->carregarMatriz();
    }

    public function atualizarMatriz($id)
    {
        // Método público (e ouvinte de evento): o ID vem do cliente. Sem esta
        // checagem, a matriz exibia os riscos de qualquer organização.
        abort_unless(! $id || Auth::user()->podeAcessarOrganizacao($id), 403);

        $this->organizacaoId = $id;
        $this->carregarMatriz();
    }

    public function carregarMatriz()
    {
        $this->matriz = [];

        // Inicializar matriz vazia 5x5
        for ($i = 5; $i >= 1; $i--) {
            for ($j = 1; $j <= 5; $j++) {
                $this->matriz[$i][$j] = [];
            }
        }

        $query = Risco::query();
        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        } else {
            // Sem organização selecionada: só o que está no alcance do usuário.
            Auth::user()->aplicarEscopoOrganizacional($query);
        }

        // Só o ciclo selecionado no topo, como a lista de riscos.
        if ($pei = PEI::doContexto()) {
            $query->where('cod_pei', $pei->cod_pei);
        }

        $riscos = $query->get();

        foreach ($riscos as $risco) {
            $this->matriz[$risco->num_impacto][$risco->num_probabilidade][] = $risco;
        }
    }

    public function render()
    {
        return view('livewire.risco.matriz-riscos', [
            'organizacaoNome' => Session::get('organizacao_selecionada_sgl', 'Global'),
        ]);
    }
}
