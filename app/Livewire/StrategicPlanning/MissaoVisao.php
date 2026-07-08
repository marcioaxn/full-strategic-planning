<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\Organization;
use App\Models\StrategicPlanning\IdentidadeEstrategica;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use App\Services\NotificationService;
use App\Services\PeiGuidanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class MissaoVisao extends Component
{
    use AuthorizesRequests;

    public $negocio = '';

    public $missao = '';

    public $visao = '';

    #[Locked]
    public $organizacaoId;

    public $organizacaoNome;

    #[Locked]
    public $identidadeId;

    #[Locked]
    public $peiAtivo;

    public bool $isEditing = false;

    public bool $aiEnabled = false;

    public $aiSuggestion = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount()
    {
        // Visualização é livre para qualquer usuário autenticado (navegar e
        // "mergulhar" na informação não é restrito por perfil/organização).
        // Só a edição (habilitarEdicao/salvar) exige capacidade RBAC + escopo.
        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function pedirAjudaIA()
    {
        if (! $this->aiEnabled) {
            return;
        }

        if (! $this->organizacaoNome) {
            session()->flash('error', 'Selecione uma organização primeiro.');

            return;
        }

        try {
            $aiService = AiServiceFactory::make();
            if (! $aiService) {
                return;
            }

            $this->aiSuggestion = 'Pensando...';

            $prompt = "Sugira Missão e Visão para a organização: {$this->organizacaoNome}.
            Responda OBRIGATORIAMENTE em formato JSON puro com os campos:
            'missao' (string) e 'visao' (string).";

            $response = $aiService->suggest($prompt);
            $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

            if (is_array($decoded)) {
                $this->aiSuggestion = $decoded;
            } else {
                throw new \Exception('Formato de resposta inválido');
            }
        } catch (\Exception $e) {
            \Log::error('Erro IA MissaoVisao: '.$e->getMessage());
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões.');
        }
    }

    public function aplicarIdentidade()
    {
        if (! isset($this->aiSuggestion['missao'])) {
            return;
        }

        $this->missao = $this->aiSuggestion['missao'];
        $this->visao = $this->aiSuggestion['visao'];

        $this->salvar();
        $this->aiSuggestion = '';
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarDados();
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
        $this->organizacaoId = $id;

        if ($id) {
            $org = Organization::find($id);
            $this->organizacaoNome = $org->nom_organizacao;
            $this->carregarDados();
        } else {
            $this->resetarDados();
        }

        $this->isEditing = false;
    }

    public function resetarDados()
    {
        $this->identidadeId = null;
        $this->negocio = '';
        $this->missao = '';
        $this->visao = '';
        $this->organizacaoNome = '';
    }

    public function carregarDados()
    {
        // Carregar Missão/Visão
        $dados = IdentidadeEstrategica::where('cod_organizacao', $this->organizacaoId)
            ->where(function ($q) {
                // Tenta buscar pelo PEI ativo, ou o último registro se não houver PEI específico
                if ($this->peiAtivo) {
                    $q->where('cod_pei', $this->peiAtivo->cod_pei);
                }
            })
            ->latest() // Em caso de múltiplos (ex: sem PEI), pega o mais recente
            ->first();

        if ($dados) {
            $this->identidadeId = $dados->cod_missao_visao_valores;
            $this->negocio = $dados->dsc_negocio ?? '';
            $this->missao = $dados->dsc_missao;
            $this->visao = $dados->dsc_visao;
        } else {
            $this->identidadeId = null;
            $this->negocio = '';
            $this->missao = '';
            $this->visao = '';
        }

    }

    public function habilitarEdicao()
    {
        $this->authorize('modulo.editar', 'planejamento-estrategico');

        if (! auth()->user()->podeAcessarOrganizacao($this->organizacaoId)) {
            abort(403, 'Você não tem permissão para editar a identidade estratégica desta organização.');
        }

        if (! $this->peiAtivo) {
            session()->flash('error', 'Não há um Ciclo PEI ativo. Não é possível editar a Identidade Estratégica.');

            return;
        }
        $this->isEditing = true;
    }

    public function cancelar()
    {
        $this->carregarDados();
        $this->isEditing = false;
        $this->resetValidation();
    }

    public function salvar()
    {
        $this->authorize('modulo.editar', 'planejamento-estrategico');

        if (! auth()->user()->podeAcessarOrganizacao($this->organizacaoId)) {
            abort(403, 'Você não tem permissão para salvar a identidade estratégica desta organização.');
        }

        if (! $this->peiAtivo) {
            session()->flash('error', 'Não é possível salvar sem um Ciclo PEI ativo.');

            return;
        }

        $service = app(PeiGuidanceService::class);
        $before = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $this->validate([
            'negocio' => 'nullable|string|max:2000',
            'missao' => 'nullable|string|max:5000',
            'visao' => 'nullable|string|max:5000',
        ]);

        IdentidadeEstrategica::updateOrCreate(
            [
                'cod_organizacao' => $this->organizacaoId,
                'cod_pei' => $this->peiAtivo->cod_pei,
            ],
            [
                'dsc_negocio' => $this->negocio ?: null,
                'dsc_missao' => $this->missao,
                'dsc_visao' => $this->visao,
            ]
        );

        $after = $service->analyzeCompleteness($this->peiAtivo->cod_pei);

        $alert = NotificationService::sendMentorAlert(
            'Identidade Estratégica Salva!',
            $after['message'],
            'bi-fingerprint'
        );

        $this->dispatch('mentor-notification', ...$alert);

        $this->isEditing = false;
        session()->flash('status', 'Identidade estratégica atualizada com sucesso!');
    }

    public function render()
    {
        return view('livewire.p-e-i.missao-visao');
    }
}
