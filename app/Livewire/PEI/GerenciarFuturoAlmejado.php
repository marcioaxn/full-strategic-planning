<?php

namespace App\Livewire\PEI;

use App\Models\PEI\Objetivo;
use App\Models\PEI\FuturoAlmejado;
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
        $this->objetivo = Objetivo::findOrFail($objetivoId);
        $this->carregarFuturos();
    }

    public function carregarFuturos()
    {
        $this->futuros = FuturoAlmejado::where('cod_objetivo', $this->objetivo->cod_objetivo)
            ->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $f = FuturoAlmejado::findOrFail($id);
        $this->futuroId = $id;
        $this->dsc_futuro_almejado = $f->dsc_futuro_almejado;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'dsc_futuro_almejado' => 'required|string|max:1000',
        ]);

        FuturoAlmejado::updateOrCreate(
            ['cod_futuro_almejado' => $this->futuroId],
            [
                'dsc_futuro_almejado' => $this->dsc_futuro_almejado,
                'cod_objetivo' => $this->objetivo->cod_objetivo,
            ]
        );

        $this->showModal = false;
        $this->carregarFuturos();
        session()->flash('status', 'Futuro almejado salvo com sucesso!');
    }

    public function delete($id)
    {
        FuturoAlmejado::findOrFail($id)->delete();
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
