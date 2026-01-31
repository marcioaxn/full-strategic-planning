<?php

namespace App\Livewire\Reports;

use App\Models\Organization;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarRelatorios extends Component
{
    public $organizacaoId;
    public $organizacaoNome;
    public $organizacoes = [];

    // Novos Filtros
    public $anos = [];
    public $anoSelecionado;
    public $periodoSelecionado = 'anual';
    public $perspectivas = [];
    public $perspectivaSelecionada = '';

    // Dados de Identidade (Disponíveis para a View)
    public $identidade;
    public $valores = [];
    public $temasNorteadores = [];

    public $peiAtivo;
    public $aiEnabled = false;
    public $includeAi = true; // Opção do usuário
    public $aiInsight = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno'
    ];

    public function atualizarAno($ano)
    {
        $this->anoSelecionado = $ano;
    }

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->includeAi = $this->aiEnabled; // Padrão segue configuração do sistema
        $this->organizacoes = Organization::orderBy('nom_organizacao')->get();

        // Carregar Anos (baseado nos ciclos PEI ativos/recentes)
        $this->anos = range(date('Y') - 1, date('Y') + 4);
        $this->anoSelecionado = Session::get('ano_selecionado', date('Y'));

        // Carregar PEI
        $this->carregarPEI();
        // Identidade é carregada dentro de carregarPEI agora

        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarPerspectivas();
        $this->carregarIdentidade();
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
        
        $this->carregarPerspectivas();
        $this->carregarIdentidade();
    }

    private function carregarIdentidade()
    {
        if ($this->peiAtivo && $this->organizacaoId) {
            $this->identidade = \App\Models\StrategicPlanning\MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)
                ->where('cod_organizacao', $this->organizacaoId)
                ->first();

            $this->valores = \App\Models\StrategicPlanning\Valor::where('cod_pei', $this->peiAtivo->cod_pei)
                ->where('cod_organizacao', $this->organizacaoId)
                ->orderBy('nom_valor')
                ->get();

            $this->temasNorteadores = \App\Models\StrategicPlanning\TemaNorteador::where('cod_pei', $this->peiAtivo->cod_pei)
                ->where('cod_organizacao', $this->organizacaoId)
                ->get();
        } else {
            $this->identidade = null;
            $this->valores = [];
            $this->temasNorteadores = [];
        }
    }

    private function carregarPerspectivas()
    {
        if ($this->peiAtivo) {
            $this->perspectivas = Perspectiva::where('cod_pei', $this->peiAtivo->cod_pei)
                ->ordenadoPorNivel()
                ->get();
        }
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
        $this->carregarIdentidade(); // Recarregar identidade ao mudar organização
    }

    public function updatedOrganizacaoId($value)
    {
        $this->setOrganizacao($value);
    }

    public function setOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
        // Sincronizar com sessão global se desejado, ou manter apenas local para o relatório
    }

    public function getQueryParamsProperty()
    {
        return [
            'ano' => $this->anoSelecionado,
            'periodo' => $this->periodoSelecionado,
            'perspectiva' => $this->perspectivaSelecionada,
            'organizacao_id' => $this->organizacaoId,
            'include_ai' => $this->includeAi // Novo parâmetro
        ];
    }

    public function gerarInsightIA()
    {
        if (!$this->aiEnabled) return;
        if (!$this->organizacaoId) {
            session()->flash('error', 'Selecione uma organização.');
            return;
        }

        if (!$this->peiAtivo) {
            session()->flash('error', 'Não há Ciclo PEI ativo selecionado.');
            return;
        }

        try {
            $aiService = \App\Services\AI\AiServiceFactory::make();
            if (!$aiService) return;

            $this->aiInsight = 'Analisando dados estratégicos...';

            // Coletar dados básicos para o prompt
            $objetivos = \App\Models\StrategicPlanning\Objetivo::whereHas('perspectiva', function($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            })->get();
            
            $planos = \App\Models\ActionPlan\PlanoDeAcao::where('cod_organizacao', $this->organizacaoId)->get();
            
            $prompt = "Gere um resumo executivo estratégico (AI Minute) para a organização {$this->organizacaoNome} no ano {$this->anoSelecionado}.
            Contexto: Possui " . $objetivos->count() . " objetivos estratégicos e " . $planos->count() . " planos de ação.
            Destaque pontos de atenção e sugestões de melhoria. Use Markdown para formatação.";

            $this->aiInsight = $aiService->suggest($prompt);
        } catch (\Exception $e) {
            \Log::error('Erro IA Relatórios: ' . $e->getMessage());
            $this->aiInsight = '';
            session()->flash('error', 'Falha ao gerar resumo inteligente.');
        }
    }

    public function download($id)
    {
        $relatorio = \App\Models\Reports\RelatorioGerado::findOrFail($id);
        
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($relatorio->dsc_caminho_arquivo)) {
            session()->flash('error', 'Arquivo não encontrado.');
            return;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($relatorio->dsc_caminho_arquivo);
    }

    public function render()
    {
        $recentReports = \App\Models\Reports\RelatorioGerado::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->latest()
            ->take(3)
            ->get();

        return view('livewire.relatorio.listar-relatorios', [
            'recentReports' => $recentReports
        ]);
    }
}