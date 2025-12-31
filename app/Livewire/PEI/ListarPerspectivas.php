<?php

namespace App\Livewire\PEI;

use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarPerspectivas extends Component
{
    public $perspectivas = [];
    public $peiAtivo;

    public bool $showModal = false;
    public $perspectivaId;
    public $dsc_perspectiva;
    public $num_nivel_hierarquico_apresentacao;

    protected $listeners = [
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->carregarPEI();

        if ($this->peiAtivo) {
            $this->carregarPerspectivas();
        }
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarPerspectivas();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function carregarPerspectivas()
    {
        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with('pei')
            ->ordenadoPorNivel()
            ->get();
    }

    public function create()
    {
        $this->resetForm();
        $maxNivel = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->max('num_nivel_hierarquico_apresentacao') ?? 0;
        $this->num_nivel_hierarquico_apresentacao = $maxNivel + 1;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $p = Perspectiva::findOrFail($id);
        $this->perspectivaId = $id;
        $this->dsc_perspectiva = $p->dsc_perspectiva;
        $this->num_nivel_hierarquico_apresentacao = $p->num_nivel_hierarquico_apresentacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'dsc_perspectiva' => 'required|string|max:255',
            'num_nivel_hierarquico_apresentacao' => 'required|integer|min:1',
        ]);

        Perspectiva::updateOrCreate(
            ['cod_perspectiva' => $this->perspectivaId],
            [
                'dsc_perspectiva' => $this->dsc_perspectiva,
                'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                'cod_pei' => $this->peiAtivo->cod_pei
            ]
        );

        $this->showModal = false;
        $this->resetForm();
        $this->carregarPerspectivas();
        session()->flash('status', 'Perspectiva salva com sucesso!');
    }

    public function delete($id)
    {
        Perspectiva::findOrFail($id)->delete();
        $this->carregarPerspectivas();
        session()->flash('status', 'Perspectiva removida com sucesso!');
    }

    public function resetForm()
    {
        $this->perspectivaId = null;
        $this->dsc_perspectiva = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
    }

    public function render()
    {
        return view('livewire.p-e-i.listar-perspectivas');
    }
}