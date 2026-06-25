<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\FuturoAlmejado;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GerenciarFuturoAlmejado extends Component
{
    public $objetivo;
    public $futuros = [];

    public bool $showModal = false;
    public $futuroId;
    public array $form = [
        'dsc_situacao_atual'       => '',
        'dsc_futuro_almejado'      => '',
        'dsc_indicador_referencia' => '',
        'vlr_referencia_meta'      => '',
        'dte_horizonte'            => '',
    ];

    public function mount($objetivoId)
    {
        $this->objetivo = Objetivo::findOrFail($objetivoId);
        $this->carregarFuturos();
    }

    public function carregarFuturos()
    {
        $this->futuros = FuturoAlmejado::where('cod_objetivo', $this->objetivo->cod_objetivo)->get();
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
        $this->form = [
            'dsc_situacao_atual'       => $f->dsc_situacao_atual ?? '',
            'dsc_futuro_almejado'      => $f->dsc_futuro_almejado,
            'dsc_indicador_referencia' => $f->dsc_indicador_referencia ?? '',
            'vlr_referencia_meta'      => $f->vlr_referencia_meta ?? '',
            'dte_horizonte'            => $f->dte_horizonte?->format('Y-m-d') ?? '',
        ];
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.dsc_futuro_almejado' => 'required|string|max:1000',
            'form.vlr_referencia_meta' => 'nullable|numeric|min:0',
            'form.dte_horizonte'       => 'nullable|date',
        ], ['form.dsc_futuro_almejado.required' => 'Descreva o futuro almejado.']);

        FuturoAlmejado::updateOrCreate(
            ['cod_futuro_almejado' => $this->futuroId],
            array_merge($this->form, [
                'cod_objetivo'         => $this->objetivo->cod_objetivo,
                'dsc_situacao_atual'   => $this->form['dsc_situacao_atual'] ?: null,
                'dsc_indicador_referencia' => $this->form['dsc_indicador_referencia'] ?: null,
                'vlr_referencia_meta'  => $this->form['vlr_referencia_meta'] !== '' ? $this->form['vlr_referencia_meta'] : null,
                'dte_horizonte'        => $this->form['dte_horizonte'] ?: null,
            ])
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
        $this->form = [
            'dsc_situacao_atual'       => '',
            'dsc_futuro_almejado'      => '',
            'dsc_indicador_referencia' => '',
            'vlr_referencia_meta'      => '',
            'dte_horizonte'            => '',
        ];
    }

    public function render()
    {
        return view('livewire.p-e-i.gerenciar-futuro-almejado');
    }
}
