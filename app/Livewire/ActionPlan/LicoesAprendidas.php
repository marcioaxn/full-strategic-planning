<?php

namespace App\Livewire\ActionPlan;

use App\Models\ActionPlan\LicaoAprendida;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class LicoesAprendidas extends Component
{
    public $peiAtivo;

    // Só o servidor define (atualizarOrganizacao confere o escopo): um $set
    // do navegador listava as lições de qualquer unidade.
    #[Locked]
    public $organizacaoId;

    // Muda pelo select da tela; o render() descarta plano fora do escopo.
    public ?string $planoFiltro = null;

    public bool $showModal = false;

    #[Locked]
    public ?string $licaoEditId = null;

    public array $form = [
        'cod_plano_de_acao' => '',
        'dsc_categoria' => 'Geral',
        'dsc_tipo' => 'Aprendizado',
        'txt_descricao' => '',
        'txt_recomendacao' => '',
    ];

    public bool $showDelete = false;

    #[Locked]
    public ?string $deleteId = null;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount(): void
    {
        $this->authorize('modulo.acessar', 'planos-de-acao');

        $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        // Validada contra o escopo — nunca a sessão crua.
        $this->organizacaoId = Auth::user()->organizacaoSelecionadaId();
    }

    public function atualizarPEI($id): void
    {
        $this->peiAtivo = PEI::find($id);
    }

    public function atualizarOrganizacao($id): void
    {
        // Método público (e ouvinte de evento): o ID vem do cliente. Logado, só
        // dentro do próprio escopo; o visitante da área pública só consulta.
        abort_unless(Auth::check() && (! $id ? Auth::user()->isSuperAdmin() : Auth::user()->podeAcessarOrganizacao($id)), 403);

        $this->organizacaoId = $id;
        $this->planoFiltro = null;
    }

    public function novaLicao(): void
    {
        if ($this->planoFiltro) {
            $this->authorize('update', PlanoDeAcao::findOrFail($this->planoFiltro));
        }

        $this->licaoEditId = null;
        $this->form = ['cod_plano_de_acao' => $this->planoFiltro ?? '', 'dsc_categoria' => 'Geral', 'dsc_tipo' => 'Aprendizado', 'txt_descricao' => '', 'txt_recomendacao' => ''];
        $this->showModal = true;
    }

    public function editar(string $id): void
    {
        $l = LicaoAprendida::findOrFail($id);
        $this->authorize('update', PlanoDeAcao::findOrFail($l->cod_plano_de_acao));

        $this->licaoEditId = $id;
        $this->form = [
            'cod_plano_de_acao' => $l->cod_plano_de_acao,
            'dsc_categoria' => $l->dsc_categoria,
            'dsc_tipo' => $l->dsc_tipo,
            'txt_descricao' => $l->txt_descricao,
            'txt_recomendacao' => $l->txt_recomendacao ?? '',
        ];
        $this->showModal = true;
    }

    public function salvar(): void
    {
        $this->validate([
            'form.cod_plano_de_acao' => 'required|string',
            'form.dsc_tipo' => 'required|string',
            'form.txt_descricao' => 'required|string|max:2000',
        ], [
            'form.cod_plano_de_acao.required' => 'Selecione a iniciativa.',
            'form.txt_descricao.required' => 'Descreva a lição aprendida.',
        ]);

        $this->authorize('update', PlanoDeAcao::findOrFail($this->form['cod_plano_de_acao']));

        if ($this->licaoEditId) {
            $this->authorize('update', PlanoDeAcao::findOrFail(LicaoAprendida::findOrFail($this->licaoEditId)->cod_plano_de_acao));
        }

        $this->licaoEditId
            ? LicaoAprendida::findOrFail($this->licaoEditId)->update($this->form)
            : LicaoAprendida::create($this->form);

        $this->showModal = false;
        $this->licaoEditId = null;
        $this->dispatch('notify', message: 'Lição aprendida salva.', style: 'success');
    }

    public function confirmarExclusao(string $id): void
    {
        $this->authorize('update', PlanoDeAcao::findOrFail(LicaoAprendida::findOrFail($id)->cod_plano_de_acao));
        $this->deleteId = $id;
        $this->showDelete = true;
    }

    public function excluir(): void
    {
        $licao = LicaoAprendida::findOrFail($this->deleteId);
        $this->authorize('update', PlanoDeAcao::findOrFail($licao->cod_plano_de_acao));

        $licao->delete();
        $this->showDelete = false;
        $this->deleteId = null;
        $this->dispatch('notify', message: 'Lição removida.', style: 'warning');
    }

    /**
     * Unidades cujas lições aparecem: a selecionada e as subordinadas, dentro
     * do que o usuário alcança. Null = sem filtro (só para Super Admin).
     *
     * @return array<int, string>|null
     */
    private function orgIdsVisiveis(): ?array
    {
        $usuario = Auth::user();

        if (! $this->organizacaoId) {
            return $usuario->isSuperAdmin() ? null : $usuario->organizacaoIdsPermitidas()->all();
        }

        $ids = Organization::descendentesEProprio($this->organizacaoId);

        return $usuario->isSuperAdmin() ? $ids : array_values(array_intersect($ids, $usuario->organizacaoIdsPermitidas()->all()));
    }

    public function render()
    {
        $orgIds = $this->orgIdsVisiveis();

        // O plano do filtro vem do select do navegador: fora do escopo, é descartado.
        if ($this->planoFiltro && $orgIds !== null
            && ! PlanoDeAcao::where('cod_plano_de_acao', $this->planoFiltro)->whereIn('cod_organizacao', $orgIds)->exists()) {
            $this->planoFiltro = null;
        }

        $planos = collect();
        if ($this->peiAtivo) {
            $planos = PlanoDeAcao::whereHas('objetivo.perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))
                ->when($orgIds !== null, fn ($q) => $q->whereIn('cod_organizacao', $orgIds))
                ->orderBy('dsc_plano_de_acao')
                ->get();
        }

        $query = LicaoAprendida::with('plano')
            ->when($this->peiAtivo, fn ($q) => $q->whereHas('plano.objetivo.perspectiva', fn ($inner) => $inner->where('cod_pei', $this->peiAtivo->cod_pei)))
            ->when($orgIds !== null, fn ($q) => $q->whereHas('plano', fn ($p) => $p->whereIn('cod_organizacao', $orgIds)))
            ->when($this->planoFiltro, fn ($q) => $q->where('cod_plano_de_acao', $this->planoFiltro))
            ->orderBy('dsc_tipo')->orderBy('dsc_categoria');

        $licoes = $query->get()->groupBy('dsc_tipo');

        return view('livewire.plano-acao.licoes-aprendidas', [
            'licoes' => $licoes,
            'planos' => $planos,
            'tipos' => LicaoAprendida::TIPOS,
            'categorias' => LicaoAprendida::CATEGORIAS,
        ]);
    }
}
