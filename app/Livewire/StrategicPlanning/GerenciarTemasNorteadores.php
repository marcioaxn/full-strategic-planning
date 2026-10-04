<?php

namespace App\Livewire\StrategicPlanning;

use App\Concerns\RevalidaUnidadeNaRequisicao;
use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use App\Services\NotificationService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class GerenciarTemasNorteadores extends Component
{
    use AuthorizesRequests;
    use RevalidaUnidadeNaRequisicao;
    use WithPagination;

    public $search = '';

    #[Locked]
    public $peiAtivo;

    #[Locked]
    public $organizacaoId;

    // Campos do Modal
    public $showModal = false;

    public bool $showDeleteModal = false;

    #[Locked]
    public $temaId;

    public $nom_tema_norteador;

    public $cod_organizacao;

    #[Locked]
    public bool $aiEnabled = false;

    public $aiSuggestion = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount()
    {
        $this->authorize('modulo.acessar', 'planejamento-estrategico');

        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        // Organização da sessão só vale se estiver no escopo do usuário.
        $this->organizacaoId = Auth::user()->organizacaoSelecionadaId();
        $this->cod_organizacao = $this->organizacaoId;
    }

    /**
     * Tema norteador é da UNIDADE: a capacidade vale na unidade do registro,
     * não na soma dos vínculos do usuário em todas as unidades.
     */
    private function autorizarNaUnidade(string $ability, ?string $codOrganizacao = null): void
    {
        $codOrganizacao ??= $this->organizacaoId;
        abort_unless($codOrganizacao !== null, 403);
        $this->authorize("modulo.{$ability}", ['planejamento-estrategico', $codOrganizacao]);
    }

    public function pedirAjudaIA()
    {
        $this->autorizarNaUnidade('criar');

        if (! $this->aiEnabled) {
            return;
        }

        $aiService = AiServiceFactory::make();
        if (! $aiService) {
            return;
        }

        $org = Organization::find($this->organizacaoId);
        if (! $org) {
            session()->flash('error', 'Selecione uma organização antes de usar o Agente IA.');

            return;
        }
        $this->aiSuggestion = 'Pensando...';

        $prompt = "Sugerir 3 Temas Norteadores (Objetivos Estratégicos de alto nível) para a organização: '{$org->nom_organizacao}'. 
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com o campo 'nome'.";

        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarSugestao($nome)
    {
        $this->nom_tema_norteador = $nome;
        $this->save();

        // Remove da lista
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, fn ($item) => $item['nome'] !== $nome);
            if (empty($this->aiSuggestion)) {
                $this->aiSuggestion = '';
            }
        }
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->resetPage();
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
        $this->cod_organizacao = $id;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->autorizarNaUnidade('criar');
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->autorizarNaUnidade('editar');
        $obj = TemaNorteador::findOrFail($id);
        abort_unless($obj->cod_organizacao === $this->organizacaoId, 403);
        abort_unless($this->peiAtivo && $obj->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->temaId = $id;
        $this->nom_tema_norteador = $obj->nom_tema_norteador;
        $this->cod_organizacao = $obj->cod_organizacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'nom_tema_norteador' => 'required|string|min:5|max:1000',
            'cod_organizacao' => 'required|exists:tab_organizacoes,cod_organizacao',
        ]);

        // A unidade vem de um select no cliente: tem de estar no escopo de quem
        // grava, e o perfil NAQUELA unidade tem de permitir a gravação.
        abort_unless(Auth::user()->podeAcessarOrganizacao($this->cod_organizacao), 403);
        $this->autorizarNaUnidade($this->temaId ? 'editar' : 'criar', $this->cod_organizacao);

        if ($this->temaId) {
            // Editar continua sendo sobre um tema da unidade em operação.
            $atual = TemaNorteador::findOrFail($this->temaId);
            abort_unless($atual->cod_organizacao === $this->organizacaoId, 403);
            abort_unless($this->peiAtivo && $atual->cod_pei === $this->peiAtivo->cod_pei, 403);
        }

        if (! $this->peiAtivo) {
            session()->flash('error', 'Não existe um ciclo PEI ativo.');

            return;
        }

        TemaNorteador::updateOrCreate(
            ['cod_tema_norteador' => $this->temaId],
            [
                'nom_tema_norteador' => $this->nom_tema_norteador,
                'cod_pei' => $this->peiAtivo->cod_pei,
                'cod_organizacao' => $this->cod_organizacao,
            ]
        );

        $alert = NotificationService::sendMentorAlert(
            $this->temaId ? 'Tema Norteador Atualizado!' : 'Tema Norteador Criado!',
            'O tema norteador foi registrado com sucesso.',
            'bi-shield-check'
        );
        $this->dispatch('mentor-notification', ...$alert);

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->autorizarNaUnidade('excluir');
        $tema = TemaNorteador::findOrFail($id);
        abort_unless($tema->cod_organizacao === $this->organizacaoId, 403);
        abort_unless($this->peiAtivo && $tema->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->temaId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $this->autorizarNaUnidade('excluir');
        if ($this->temaId) {
            $tema = TemaNorteador::findOrFail($this->temaId);
            abort_unless($tema->cod_organizacao === $this->organizacaoId, 403);
            abort_unless($this->peiAtivo && $tema->cod_pei === $this->peiAtivo->cod_pei, 403);
            $tema->delete();
            $this->temaId = null;
            $this->showDeleteModal = false;

            $alert = NotificationService::sendMentorAlert(
                'Tema Norteador Removido',
                'O item foi excluído do planejamento institucional.',
                'bi-trash',
                'warning'
            );
            $this->dispatch('mentor-notification', ...$alert);
        }
    }

    public function resetForm()
    {
        $this->temaId = null;
        $this->nom_tema_norteador = '';
        $this->cod_organizacao = $this->organizacaoId;
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetValidation();
    }

    public function render()
    {
        $query = TemaNorteador::query()
            ->with(['organizacao', 'pei'])
            ->when($this->search, function ($q) {
                $q->where('nom_tema_norteador', 'ilike', '%'.$this->search.'%');
            })
            ->when(
                $this->organizacaoId,
                fn ($q) => $q->where('cod_organizacao', $this->organizacaoId),
                // Sem unidade, nunca "todas": o escopo do usuário (Super Admin vê tudo).
                fn ($q) => Auth::user()->aplicarEscopoOrganizacional($q)
            )
            ->when($this->peiAtivo, function ($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            });

        return view('livewire.p-e-i.gerenciar-temas-norteadores', [
            'temas' => $query->latest()->paginate(10),
            // O seletor de unidade do formulário lista só o escopo de quem grava.
            'organizacoes' => Auth::user()->aplicarEscopoOrganizacional(Organization::query())->orderBy('nom_organizacao')->get(),
            ...$this->permissoesNaUnidade(),
        ]);
    }

    /** @return array{podeCriar: bool, podeEditar: bool, podeExcluir: bool} */
    private function permissoesNaUnidade(): array
    {
        $pode = fn (string $a) => $this->organizacaoId !== null
            && Gate::allows("modulo.{$a}", ['planejamento-estrategico', $this->organizacaoId]);

        return ['podeCriar' => $pode('criar'), 'podeEditar' => $pode('editar'), 'podeExcluir' => $pode('excluir')];
    }
}
