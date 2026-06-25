<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\Agenda2030\ODS;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarObjetivos extends Component
{
    public $perspectivas = [];
    #[Locked]
    public $peiAtivo;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showSuccessModal = false;
    public bool $showErrorModal = false;
    public string $successMessage = '';
    public string $errorMessage = '';
    public string $createdObjetivoName = '';

    public $objetivoId;
    public $nom_objetivo;
    public $dsc_objetivo;
    public $num_nivel_hierarquico_apresentacao;
    public $cod_perspectiva;
    public $cod_objetivo_pai;

    // Agenda 2030 — vínculo de ODS ao objetivo (máx. 3, padrão ONU institucional)
    public array $odsSelecionados = [];
    public array $odsContribuicoes = [];
    public const MAX_ODS = 3;

    public bool $aiEnabled = false;
    public $aiSuggestion = '';
    public $smartFeedback = '';
    public $impactoExclusao = [];

    protected $listeners = [
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->successMessage = '';
        $this->createdObjetivoName = '';
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
        $this->errorMessage = '';
    }

    /**
     * Hook do Livewire que dispara quando nom_objetivo é alterado.
     * Postura Preventiva: Analisa a qualidade SMART automaticamente.
     */
    public function updatedNomObjetivo($value)
    {
        if (strlen($value) > 10 && $this->aiEnabled) {
            $this->auditSmart();
        }
    }

    public function auditSmart()
    {
        if (empty($this->nom_objetivo)) {
            $this->addError('nom_objetivo', 'Digite um título para auditar.');
            return;
        }

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $this->smartFeedback = 'Analisando qualidade...';
        $this->smartFeedback = $aiService->analyzeSmart('Objetivo', $this->nom_objetivo, $this->dsc_objetivo ?? '');
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;
        
        if (!$this->cod_perspectiva) {
             session()->flash('error', 'Selecione uma perspectiva primeiro.');
             return;
        }

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $perspectiva = Perspectiva::find($this->cod_perspectiva);
        $identidade = \App\Models\StrategicPlanning\MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)->first();

        $this->aiSuggestion = 'Pensando...';
        
        $prompt = "Sugira 3 objetivos estratégicos para a perspectiva '{$perspectiva->dsc_perspectiva}'. 
        Missão: '" . ($identidade->dsc_missao ?? '') . "'. Visão: '" . ($identidade->dsc_visao ?? '') . "'.
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'nome', 'descricao' e 'ordem' (inteiro).";
        
        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões. Tente novamente.');
        }
    }

    public function aplicarSugestao($nome, $descricao, $ordem)
    {
        $this->nom_objetivo = $nome;
        $this->dsc_objetivo = $descricao;
        $this->num_nivel_hierarquico_apresentacao = $ordem;
        
        $this->save();
        
        // Remove o item da lista de sugestões
        if (is_array($this->aiSuggestion)) {
            $this->aiSuggestion = array_filter($this->aiSuggestion, function($item) use ($nome) {
                return $item['nome'] !== $nome;
            });
            
            if (empty($this->aiSuggestion)) $this->aiSuggestion = '';
        }
    }

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();

        if ($this->peiAtivo) {
            $this->carregarPerspectivas();
        }
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarPerspectivas();
    }

    private function carregarPEI()
    {
        $peiId = Session::get('pei_selecionado_id');

        if ($peiId) {
            $this->peiAtivo = PEI::find($peiId);
        }

        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function carregarPerspectivas()
    {
        if (!$this->peiAtivo) return;
        $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
            ->with(['objetivos' => function($query) {
                $query->raiz()->ordenadoPorNivel()->with([
                    'ods',
                    'objetivosFilhos' => function($q) {
                        $q->ordenadoPorNivel()->with('ods');
                    }
                ]);
            }])
            ->ordenadoPorNivel()
            ->get();
    }

    /**
     * Alterna a seleção de um ODS no formulário (respeita o limite institucional).
     */
    public function toggleOds(int $numOds): void
    {
        if (in_array($numOds, $this->odsSelecionados)) {
            $this->odsSelecionados = array_values(array_diff($this->odsSelecionados, [$numOds]));
            unset($this->odsContribuicoes[$numOds]);
            return;
        }

        if (count($this->odsSelecionados) >= self::MAX_ODS) {
            $this->dispatch('mentor-notification',
                title: 'Limite de ODS atingido',
                message: 'Recomenda-se vincular no máximo ' . self::MAX_ODS . ' ODS por objetivo, mantendo o foco estratégico.',
                icon: 'bi-info-circle',
                type: 'info'
            );
            return;
        }

        $this->odsSelecionados[] = $numOds;
    }

    public function create($perspectivaId = null, \App\Services\PeiGuidanceService $service = null)
    {
        // Resolve service if not passed (Livewire handles dependency injection in methods if requested)
        $service = $service ?? app(\App\Services\PeiGuidanceService::class);
        $guidance = $service->analyzeCompleteness($this->peiAtivo->cod_pei);
        
        // If we are in any phase BEFORE objectives, redirect and alert
        $phasesBefore = ['ciclo', 'identidade', 'perspectivas'];
        if ($guidance['status'] === 'warning' && in_array($guidance['current_phase'], $phasesBefore)) {
             session()->flash('error', $guidance['message']);
             return redirect()->route($guidance['action_route']);
        }

        $this->resetForm();
        $this->cod_perspectiva = $perspectivaId;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $obj = Objetivo::with(['ods', 'perspectiva'])->findOrFail($id);
        abort_unless($this->peiAtivo && $obj->perspectiva->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->objetivoId    = $id;
        $this->nom_objetivo  = $obj->nom_objetivo;
        $this->dsc_objetivo  = $obj->dsc_objetivo;
        $this->num_nivel_hierarquico_apresentacao = $obj->num_nivel_hierarquico_apresentacao;
        $this->cod_perspectiva   = $obj->cod_perspectiva;
        $this->cod_objetivo_pai  = $obj->cod_objetivo_pai;

        $this->odsSelecionados  = $obj->ods->pluck('num_ods')->map(fn($n) => (int) $n)->toArray();
        $this->odsContribuicoes = $obj->ods->pluck('pivot.txt_contribuicao', 'num_ods')->toArray();

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
        $service = app(\App\Services\PeiGuidanceService::class);
        $this->validate([
            'nom_objetivo'                        => 'required|string|max:255',
            'dsc_objetivo'                        => 'nullable|string|max:1000',
            'num_nivel_hierarquico_apresentacao'  => 'required|integer|min:1',
            'cod_perspectiva'                     => 'required|exists:tab_perspectiva,cod_perspectiva',
            'cod_objetivo_pai'                    => 'nullable|exists:tab_objetivo,cod_objetivo',
        ]);

        // Impede que um objetivo seja seu próprio pai
        if ($this->cod_objetivo_pai && $this->cod_objetivo_pai === $this->objetivoId) {
            $this->addError('cod_objetivo_pai', 'Um objetivo não pode ser pai de si mesmo.');
            return;
        }

        $nivelDesdobramento = 1;
        if ($this->cod_objetivo_pai) {
            $pai = Objetivo::find($this->cod_objetivo_pai);
            $nivelDesdobramento = ($pai->num_nivel_desdobramento ?? 1) + 1;
        }

        try {
            $objetivo = Objetivo::updateOrCreate(
                ['cod_objetivo' => $this->objetivoId],
                [
                    'nom_objetivo'                       => $this->nom_objetivo,
                    'dsc_objetivo'                       => $this->dsc_objetivo,
                    'num_nivel_hierarquico_apresentacao' => $this->num_nivel_hierarquico_apresentacao,
                    'cod_perspectiva'                    => $this->cod_perspectiva,
                    'cod_objetivo_pai'                   => $this->cod_objetivo_pai ?: null,
                    'num_nivel_desdobramento'            => $nivelDesdobramento,
                ]
            );

            // Sincronizar vínculos de ODS (Agenda 2030) com a contribuição de cada um
            $syncOds = [];
            foreach ($this->odsSelecionados as $num) {
                $syncOds[(int) $num] = ['txt_contribuicao' => $this->odsContribuicoes[$num] ?? null];
            }
            $objetivo->ods()->sync($syncOds);

            if ($this->objetivoId) {
                $this->successMessage = "As definições do objetivo estratégico foram atualizadas com sucesso e já estão refletidas no mapa estratégico.";
            } else {
                $this->successMessage = "O novo objetivo estratégico foi registrado. Agora você pode prosseguir vinculando indicadores e planos de ação a esta meta.";
            }

            $this->createdObjetivoName = $this->nom_objetivo;
            $this->showModal = false;
            $this->resetForm();
            $this->carregarPerspectivas();
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            $this->errorMessage = "Não foi possível processar o registro do objetivo. Por favor, revise as informações e tente novamente.";
            $this->showErrorModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $objetivo = Objetivo::with('perspectiva')->withCount(['indicadores', 'planosAcao'])->findOrFail($id);
        abort_unless($this->peiAtivo && $objetivo->perspectiva->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->objetivoId = $id;
        
        $this->impactoExclusao = [
            'indicadores' => $objetivo->indicadores_count,
            'planos' => $objetivo->planos_acao_count,
        ];
        
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $obj = Objetivo::with('perspectiva')->findOrFail($this->objetivoId);
        abort_unless($this->peiAtivo && $obj->perspectiva->cod_pei === $this->peiAtivo->cod_pei, 403);
        $obj->delete();
        $this->showDeleteModal = false;
        $this->objetivoId = null;
        $this->carregarPerspectivas();
        
        $this->dispatch('mentor-notification', 
            title: 'Objetivo Removido',
            message: 'O objetivo foi excluído do seu planejamento estratégico.',
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function resetForm()
    {
        $this->objetivoId       = null;
        $this->nom_objetivo     = '';
        $this->dsc_objetivo     = '';
        $this->num_nivel_hierarquico_apresentacao = 1;
        $this->cod_perspectiva  = '';
        $this->cod_objetivo_pai = null;
        $this->odsSelecionados  = [];
        $this->odsContribuicoes = [];
    }

    public function render()
    {
        // Objetivos raiz do PEI (candidatos a pai no modal)
        $objetivosPossivelPai = collect();
        if ($this->peiAtivo) {
            $objetivosPossivelPai = Objetivo::whereHas('perspectiva', function($q) {
                    $q->where('cod_pei', $this->peiAtivo->cod_pei);
                })
                ->raiz()
                ->when($this->objetivoId, fn($q) => $q->where('cod_objetivo', '!=', $this->objetivoId))
                ->with('perspectiva')
                ->orderBy('cod_perspectiva')
                ->orderBy('num_nivel_hierarquico_apresentacao')
                ->get();
        }

        return view('livewire.p-e-i.listar-objetivos', [
            'todosOds'            => ODS::ordenado()->get(),
            'objetivosPossivelPai'=> $objetivosPossivelPai,
        ]);
    }
}
