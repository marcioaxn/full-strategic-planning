<?php

namespace App\Livewire\PEI;

use App\Models\PEI\ObjetivoEstrategico;
use App\Models\PEI\FuturoAlmejadoObjetivoEstrategico;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GerenciarFuturoAlmejado extends Component
{
    public $objetivo;
    public $futuros = [];
    
    public bool $showModal = false;
    public $futuroId;
    public $dsc_futuro_almejado;

    public function mount($objetivoId)
    {
        $this->objetivo = ObjetivoEstrategico::findOrFail($objetivoId);
        $this->carregarFuturos();
    }

    public function carregarFuturos()
    {
        $this->futuros = FuturoAlmejadoObjetivoEstrategico::where('cod_objetivo_estrategico', $this->objetivo->cod_objetivo_estrategico)
            ->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $f = FuturoAlmejadoObjetivoEstrategico::findOrFail($id);
        $this->futuroId = $id;
        $this->dsc_futuro_almejado = $f->dsc_futuro_almejado;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'dsc_futuro_almejado' => 'required|string|max:1000',
        ]);

        FuturoAlmejadoObjetivoEstrategico::updateOrCreate(
            ['cod_futuro_almejado' => $this->futuroId],
            [
                'dsc_futuro_almejado' => $this->dsc_futuro_almejado,
                'cod_objetivo_estrategico' => $this->objetivo->cod_objetivo_estrategico,
            ]
        );

        $this->showModal = false;
        $this->carregarFuturos();
        session()->flash('status', 'Futuro almejado salvo com sucesso!');
    }

    public function delete($id)
    {
        FuturoAlmejadoObjetivoEstrategico::findOrFail($id)->delete();
        $this->carregarFuturos();
        session()->flash('status', 'Futuro almejado excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->futuroId = null;
        $this->dsc_futuro_almejado = '';
    }

    public function render()
    {
        return view('livewire.p-e-i.gerenciar-futuro-almejado');
    }
}