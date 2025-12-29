<?php

namespace App\Livewire\PEI;

use App\Models\PEI\PEI;
use App\Models\PEI\Perspectiva;
use App\Models\PEI\Objetivo;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ListarObjetivos extends Component
{
    public $perspectivas = [];
    public $peiAtivo;

    public bool $showModal = false;
    public $objetivoId;
    public $nom_objetivo;
    public $dsc_objetivo;
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
        $obj = Objetivo::findOrFail($id);
        $this->objetivoId = $id;
        $this->nom_objetivo = $obj->nom_objetivo;
        $this->dsc_objetivo = $obj->dsc_objetivo;
        $this->num_nivel_hierarquico_apresentacao = $obj->num_nivel_hierarquico_apresentacao;
        $this->cod_perspectiva = $obj->cod_perspectiva;
        $this->showModal = true;
    }

    /**
     * Hook do Livewire que dispara quando cod_perspectiva é alterado
     * Melhora UX sugerindo a próxima ordem automaticamente
     */
    public function updatedCodPerspectiva($value)
    {
        if (!$this->objetivoId && $value) {
            $proximaOrdem = Objetivo::where('cod_perspectiva', $value)
                ->max('num_nivel_hierarquico_apresentacao');
            
            $this->num_nivel_hierarquico_apresentacao = ($proximaOrdem ?? 0) + 1;
        }
    }

    public function save()
    {
        $this->validate([
            'nom_objetivo' => 'required|string|max:255',
            'dsc_objetivo' => 'nullable|string|max:1000',
            'num_nivel_hierarquico_apresentacao' => 'required|integer|min:1',
            'cod_perspectiva' => 'required|exists:pei.tab_perspectiva,cod_perspectiva',
        ]);

        Objetivo::updateOrCreate(
            ['cod_objetivo' => $this->objetivoId],
            [
                'nom_objetivo' => $this->nom_objetivo,
                'dsc_objetivo' => $this->dsc_objetivo,
                'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                'cod_perspectiva' => $this->cod_perspectiva,
            ]
        );

        $this->showModal = false;
        $this->carregarPerspectivas();
        session()->flash('status', 'Objetivo salvo com sucesso!');
    }

    public function delete($id)
    {
        Objetivo::findOrFail($id)->delete();
        $this->carregarPerspectivas();
        session()->flash('status', 'Objetivo excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->objetivoId = null;
        $this->nom_objetivo = '';
        $this->dsc_objetivo = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
        $this->cod_perspectiva = '';
    }

    public function render()
    {
        return view('livewire.p-e-i.listar-objetivos');
    }
}
