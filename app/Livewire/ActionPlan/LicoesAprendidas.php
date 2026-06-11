<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\LicaoAprendida;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\StrategicPlanning\PEI;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class LicoesAprendidas extends Component
{
    public $peiAtivo;
    public $organizacaoId;
    public ?string $planoFiltro = null;

    public bool $showModal    = false;
    public ?string $licaoEditId = null;

    public array $form = [
        'cod_plano_de_acao' => '',
        'dsc_categoria'     => 'Geral',
        'dsc_tipo'          => 'Aprendizado',
        'txt_descricao'     => '',
        'txt_recomendacao'  => '',
    ];

    public bool $showDelete   = false;
    public ?string $deleteId  = null;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado'         => 'atualizarPEI',
    ];

    public function mount(): void
    {
        $this->peiAtivo      = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        $this->organizacaoId = Session::get('organizacao_selecionada_id');
    }

    public function atualizarPEI($id): void   { $this->peiAtivo = PEI::find($id); }
    public function atualizarOrganizacao($id): void { $this->organizacaoId = $id; }

    public function novaLicao(): void
    {
        $this->licaoEditId = null;
        $this->form = ['cod_plano_de_acao' => $this->planoFiltro ?? '', 'dsc_categoria' => 'Geral', 'dsc_tipo' => 'Aprendizado', 'txt_descricao' => '', 'txt_recomendacao' => ''];
        $this->showModal = true;
    }

    public function editar(string $id): void
    {
        $l = LicaoAprendida::findOrFail($id);
        $this->licaoEditId = $id;
        $this->form = [
            'cod_plano_de_acao' => $l->cod_plano_de_acao,
            'dsc_categoria'     => $l->dsc_categoria,
            'dsc_tipo'          => $l->dsc_tipo,
            'txt_descricao'     => $l->txt_descricao,
            'txt_recomendacao'  => $l->txt_recomendacao ?? '',
        ];
        $this->showModal = true;
    }

    public function salvar(): void
    {
        $this->validate([
            'form.cod_plano_de_acao' => 'required|string',
            'form.dsc_tipo'          => 'required|string',
            'form.txt_descricao'     => 'required|string|max:2000',
        ], [
            'form.cod_plano_de_acao.required' => 'Selecione o plano de ação.',
            'form.txt_descricao.required'     => 'Descreva a lição aprendida.',
        ]);

        $this->licaoEditId
            ? LicaoAprendida::findOrFail($this->licaoEditId)->update($this->form)
            : LicaoAprendida::create($this->form);

        $this->showModal   = false;
        $this->licaoEditId = null;
        $this->dispatch('notify', message: 'Lição aprendida salva.', style: 'success');
    }

    public function confirmarExclusao(string $id): void
    {
        $this->deleteId   = $id;
        $this->showDelete = true;
    }

    public function excluir(): void
    {
        LicaoAprendida::findOrFail($this->deleteId)->delete();
        $this->showDelete = false;
        $this->deleteId   = null;
        $this->dispatch('notify', message: 'Lição removida.', style: 'warning');
    }

    public function render()
    {
        $planos = collect();
        if ($this->peiAtivo) {
            $planos = PlanoDeAcao::whereHas('objetivo.perspectiva', fn($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))
                ->when($this->organizacaoId, fn($q) => $q->where('cod_organizacao', $this->organizacaoId))
                ->orderBy('dsc_plano_de_acao')
                ->get();
        }

        $query = LicaoAprendida::with('plano')
            ->when($this->peiAtivo, fn($q) => $q->whereHas('plano.objetivo.perspectiva', fn($inner) => $inner->where('cod_pei', $this->peiAtivo->cod_pei)))
            ->when($this->organizacaoId, fn($q) => $q->whereHas('plano', fn($p) => $p->where('cod_organizacao', $this->organizacaoId)))
            ->when($this->planoFiltro, fn($q) => $q->where('cod_plano_de_acao', $this->planoFiltro))
            ->orderBy('dsc_tipo')->orderBy('dsc_categoria');

        $licoes = $query->get()->groupBy('dsc_tipo');

        return view('livewire.plano-acao.licoes-aprendidas', [
            'licoes'  => $licoes,
            'planos'  => $planos,
            'tipos'   => LicaoAprendida::TIPOS,
            'categorias' => LicaoAprendida::CATEGORIAS,
        ]);
    }
}
