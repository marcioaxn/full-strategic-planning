<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\FuturoAlmejado;
use App\Models\StrategicPlanning\Objetivo;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class GerenciarFuturoAlmejado extends Component
{
    /**
     * Este registro não tem organização: vale para a INSTITUIÇÃO inteira
     * (todas as unidades). Por isso, além da capacidade no módulo, gravar
     * exige poder editar o que é institucional — Super Admin ou Administrador
     * da unidade raiz. Antes, o Administrador de qualquer unidade folha (ou
     * um Gestor) alterava o que vale para todas.
     */
    private function autorizarInstitucional(string $ability): void
    {
        $this->authorize("modulo.{$ability}", 'planejamento-estrategico');
        $this->authorize('editar-institucional');
    }

    public $objetivo;

    public $futuros = [];

    public bool $showModal = false;

    #[Locked]
    public $futuroId;

    public array $form = [
        'dsc_situacao_atual' => '',
        'dsc_futuro_almejado' => '',
        'dsc_indicador_referencia' => '',
        'vlr_referencia_meta' => '',
        'dte_horizonte' => '',
    ];

    public function mount($objetivoId)
    {
        // Ler exige o módulo (todo perfil com vínculo tem). Escrever exige
        // poder editar o que é institucional (ver autorizarInstitucional).
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->objetivo = Objetivo::findOrFail($objetivoId);
        $this->carregarFuturos();
    }

    /**
     * O id vem do navegador: o registro tem de ser DESTE objetivo. Antes,
     * editava-se ou excluía-se o futuro almejado de qualquer objetivo.
     */
    private function doObjetivo(string $id): FuturoAlmejado
    {
        $f = FuturoAlmejado::findOrFail($id);
        abort_unless($f->cod_objetivo === $this->objetivo->cod_objetivo, 403);

        return $f;
    }

    public function carregarFuturos()
    {
        $this->futuros = FuturoAlmejado::where('cod_objetivo', $this->objetivo->cod_objetivo)->get();
    }

    public function create()
    {
        $this->autorizarInstitucional('criar');

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->autorizarInstitucional('editar');

        $f = $this->doObjetivo($id);
        $this->futuroId = $id;
        $this->form = [
            'dsc_situacao_atual' => $f->dsc_situacao_atual ?? '',
            'dsc_futuro_almejado' => $f->dsc_futuro_almejado,
            'dsc_indicador_referencia' => $f->dsc_indicador_referencia ?? '',
            'vlr_referencia_meta' => $f->vlr_referencia_meta ?? '',
            'dte_horizonte' => $f->dte_horizonte?->format('Y-m-d') ?? '',
        ];
        $this->showModal = true;
    }

    public function save()
    {
        $this->autorizarInstitucional($this->futuroId ? 'editar' : 'criar');

        $this->validate([
            'form.dsc_futuro_almejado' => 'required|string|max:1000',
            'form.vlr_referencia_meta' => 'nullable|numeric|min:0',
            'form.dte_horizonte' => 'nullable|date',
        ], ['form.dsc_futuro_almejado.required' => 'Descreva o futuro almejado.']);

        if ($this->futuroId) {
            $this->doObjetivo($this->futuroId);
        }

        FuturoAlmejado::updateOrCreate(
            ['cod_futuro_almejado' => $this->futuroId],
            array_merge($this->form, [
                'cod_objetivo' => $this->objetivo->cod_objetivo,
                'dsc_situacao_atual' => $this->form['dsc_situacao_atual'] ?: null,
                'dsc_indicador_referencia' => $this->form['dsc_indicador_referencia'] ?: null,
                'vlr_referencia_meta' => $this->form['vlr_referencia_meta'] !== '' ? $this->form['vlr_referencia_meta'] : null,
                'dte_horizonte' => $this->form['dte_horizonte'] ?: null,
            ])
        );

        $this->showModal = false;
        $this->carregarFuturos();
        session()->flash('status', 'Futuro almejado salvo com sucesso!');
    }

    public function delete($id)
    {
        $this->autorizarInstitucional('excluir');

        $this->doObjetivo($id)->delete();
        $this->carregarFuturos();
        session()->flash('status', 'Futuro almejado excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->futuroId = null;
        $this->form = [
            'dsc_situacao_atual' => '',
            'dsc_futuro_almejado' => '',
            'dsc_indicador_referencia' => '',
            'vlr_referencia_meta' => '',
            'dte_horizonte' => '',
        ];
    }

    public function render()
    {
        // O futuro almejado é da instituição: a tela só oferece o botão que o servidor aceita.
        $institucional = Gate::allows('editar-institucional');

        return view('livewire.p-e-i.gerenciar-futuro-almejado', [
            'podeCriar' => $institucional && Gate::allows('modulo.criar', 'planejamento-estrategico'),
            'podeEditar' => $institucional && Gate::allows('modulo.editar', 'planejamento-estrategico'),
            'podeExcluir' => $institucional && Gate::allows('modulo.excluir', 'planejamento-estrategico'),
        ]);
    }
}
