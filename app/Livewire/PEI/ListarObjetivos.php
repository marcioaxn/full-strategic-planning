<?php

namespace App\Livewire\PEI;

use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\ObjetivoEstrategico;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ListarObjetivos extends Component
{
    public $perspectivas = [];
    public $peiAtivo;

    public bool $showModal = false;
    public $objetivoId;
    public $nom_objetivo_estrategico;
    public $dsc_objetivo_estrategico;
    public $num_nivel_hierarquico_apresentacao;
    public $cod_perspectiva;

    public function mount()
    {
        $this->peiAtivo = PEI::ativos()->first();
        
        if ($this->peiAtivo) {
            $this->carregarPerspectivas();
        }
    }

    public function carregarPerspectivas()
    {
        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with(['objetivos' => function($query) {
                $query->ordenadoPorNivel();
            }])
            ->ordenadoPorNivel()
            ->get();
    }

    public function create($perspectivaId = null)
    {
        $this->resetForm();
        $this->cod_perspectiva = $perspectivaId;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $obj = ObjetivoEstrategico::findOrFail($id);
        $this->objetivoId = $id;
        $this->nom_objetivo_estrategico = $obj->nom_objetivo_estrategico;
        $this->dsc_objetivo_estrategico = $obj->dsc_objetivo_estrategico;
        $this->num_nivel_hierarquico_apresentacao = $obj->num_nivel_hierarquico_apresentacao;
        $this->cod_perspectiva = $obj->cod_perspectiva;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nom_objetivo_estrategico' => 'required|string|max:255',
            'dsc_objetivo_estrategico' => 'nullable|string|max:1000',
            'num_nivel_hierarquico_apresentacao' => 'required|integer|min:1',
            'cod_perspectiva' => 'required|exists:pei.tab_perspectiva,cod_perspectiva',
        ]);

        ObjetivoEstrategico::updateOrCreate(
            ['cod_objetivo_estrategico' => $this->objetivoId],
            [
                'nom_objetivo_estrategico' => $this->nom_objetivo_estrategico,
                'dsc_objetivo_estrategico' => $this->dsc_objetivo_estrategico,
                'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                'cod_perspectiva' => $this->cod_perspectiva,
            ]
        );

        $this->showModal = false;
        $this->carregarPerspectivas();
        session()->flash('status', 'Objetivo estratégico salvo com sucesso!');
    }

    public function delete($id)
    {
        ObjetivoEstrategico::findOrFail($id)->delete();
        $this->carregarPerspectivas();
        session()->flash('status', 'Objetivo estratégico excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->objetivoId = null;
        $this->nom_objetivo_estrategico = '';
        $this->dsc_objetivo_estrategico = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
        $this->cod_perspectiva = '';
    }

    public function render()
    {
        return view('livewire.p-e-i.listar-objetivos');
    }
}