<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Valor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DetalharIdentidade extends Component
{
    public $identidade;

    public $valores;

    public bool $podeVerHistorico = false;

    public function mount($id)
    {
        // 🔴 Abria para qualquer pessoa logada, de qualquer unidade, e exibia a
        // trilha de auditoria (quem alterou, quando e o quê). Agora: o módulo,
        // a unidade da identidade no escopo de quem lê, e o histórico só para
        // quem tem o módulo Auditoria.
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->identidade = MissaoVisaoValores::with(['pei', 'organizacao'])->findOrFail($id);

        abort_unless(Auth::user()->podeAcessarOrganizacao($this->identidade->cod_organizacao), 403);

        $this->podeVerHistorico = Gate::allows('modulo.acessar', 'auditoria');

        // Carregar valores associados ao mesmo PEI e Organização
        $this->valores = Valor::where('cod_organizacao', $this->identidade->cod_organizacao)
            ->where('cod_pei', $this->identidade->cod_pei)
            ->get();
    }

    public function render()
    {
        return view('livewire.p-e-i.detalhar-identidade', [
            'historico' => $this->podeVerHistorico
                ? $this->identidade->audits()->with('user')->latest()->take(5)->get()
                : collect(),
            'totalHistorico' => $this->podeVerHistorico ? $this->identidade->audits()->count() : 0,
        ]);
    }
}
