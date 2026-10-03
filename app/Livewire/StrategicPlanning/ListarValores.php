<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Valor;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class ListarValores extends Component
{
    use AuthorizesRequests;

    #[Locked]
    public $organizacaoId;

    public $organizacaoNome;

    #[Locked]
    public $peiAtivo;

    public $valores = [];

    public bool $showModal = false;

    #[Locked]
    public $valorId;

    public $nom_valor;

    public $dsc_valor;

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount()
    {
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->carregarPEI();
        // Organização da sessão só vale se estiver no escopo do usuário.
        $this->atualizarOrganizacao(Auth::user()->organizacaoSelecionadaId());
    }

    /**
     * Valor é da UNIDADE: a capacidade vale na unidade desta tela, não na
     * soma dos vínculos do usuário em todas as unidades.
     */
    private function autorizarNaUnidade(string $ability): void
    {
        abort_unless($this->organizacaoId !== null, 403);
        $this->authorize("modulo.{$ability}", ['planejamento-estrategico', $this->organizacaoId]);
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarValores();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (! $this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function atualizarOrganizacao($id)
    {
        // Método público (e ouvinte de evento): o ID vem do cliente.
        abort_unless(! $id || Auth::user()?->podeAcessarOrganizacao($id), 403);

        $this->organizacaoId = $id;

        if ($id) {
            $org = Organization::find($id);
            $this->organizacaoNome = $org?->nom_organizacao ?? '';
            $this->carregarValores();
        } else {
            $this->valores = [];
            $this->organizacaoNome = '';
        }
    }

    public function carregarValores()
    {
        if (! $this->peiAtivo || ! $this->organizacaoId) {
            $this->valores = [];

            return;
        }

        $this->valores = Valor::where('cod_organizacao', $this->organizacaoId)
            ->where('cod_pei', $this->peiAtivo->cod_pei)
            ->orderBy('nom_valor')
            ->get();
    }

    public function create()
    {
        $this->autorizarNaUnidade('criar');

        if (! $this->peiAtivo) {
            session()->flash('error', 'Não há um Ciclo PEI selecionado.');

            return;
        }
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->autorizarNaUnidade('editar');
        $valor = Valor::findOrFail($id);
        abort_unless($valor->cod_organizacao === $this->organizacaoId, 403);
        $this->valorId = $id;
        $this->nom_valor = $valor->nom_valor;
        $this->dsc_valor = $valor->dsc_valor;
        $this->showModal = true;
    }

    public function save()
    {
        if (! $this->peiAtivo || ! $this->organizacaoId) {
            session()->flash('error', 'Selecione um Ciclo PEI e uma organização antes de salvar.');

            return;
        }

        $this->autorizarNaUnidade($this->valorId ? 'editar' : 'criar');

        if ($this->valorId) {
            abort_unless(Valor::findOrFail($this->valorId)->cod_organizacao === $this->organizacaoId, 403);
        }

        $this->validate([
            'nom_valor' => 'required|string|max:255',
            'dsc_valor' => 'nullable|string|max:1000',
        ]);

        Valor::updateOrCreate(
            ['cod_valor' => $this->valorId],
            [
                'nom_valor' => $this->nom_valor,
                'dsc_valor' => $this->dsc_valor,
                'cod_organizacao' => $this->organizacaoId,
                'cod_pei' => $this->peiAtivo->cod_pei,
            ]
        );

        $this->showModal = false;
        $this->carregarValores();
        session()->flash('status', 'Valor salvo com sucesso!');
    }

    public function delete($id)
    {
        $this->autorizarNaUnidade('excluir');
        $valor = Valor::findOrFail($id);
        abort_unless($valor->cod_organizacao === $this->organizacaoId, 403);
        $valor->delete();
        $this->carregarValores();
        session()->flash('status', 'Valor excluído com sucesso!');
    }

    public function resetForm()
    {
        $this->valorId = null;
        $this->nom_valor = '';
        $this->dsc_valor = '';
    }

    public function render()
    {
        $pode = fn (string $a) => $this->organizacaoId !== null
            && Gate::allows("modulo.{$a}", ['planejamento-estrategico', $this->organizacaoId]);

        // A tela só oferece o botão que o servidor aceita.
        return view('livewire.p-e-i.listar-valores', [
            'podeCriar' => $pode('criar'),
            'podeEditar' => $pode('editar'),
            'podeExcluir' => $pode('excluir'),
        ]);
    }
}
