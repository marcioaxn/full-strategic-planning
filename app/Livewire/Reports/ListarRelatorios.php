<?php

namespace App\Livewire\Reports;

use App\Concerns\BaixaRelatorioGerado;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\Reports\RelatorioGerado;
use App\Models\StrategicPlanning\MissaoVisaoValores;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Perspectiva;
use App\Models\StrategicPlanning\TemaNorteador;
use App\Models\StrategicPlanning\Valor;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class ListarRelatorios extends Component
{
    use BaixaRelatorioGerado;

    public $organizacaoId;

    #[Locked]
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

    #[Locked]
    public $aiEnabled = false;

    public $includeAi = false; // Opção do usuário - Padrão desmarcado

    public $aiInsight = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
        'anoSelecionado' => 'atualizarAno',
    ];

    public function atualizarAno($ano)
    {
        $this->anoSelecionado = $ano;
    }

    public function mount()
    {
        $this->authorize('modulo.acessar', 'relatorios');

        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);
        $this->includeAi = false; // Padrão agora é desmarcado
        // Só as unidades do escopo de quem gera o relatório (Super Admin: todas).
        $this->organizacoes = Organization::whereIn('cod_organizacao', Auth::user()->organizacaoIdsPermitidas())
            ->orderBy('nom_organizacao')->get();

        // Anos dos ciclos cadastrados (os mesmos do seletor global de ano).
        // 🔴 Era range(ano-1, ano+4): com o ano global em 2023, o <select> não
        // tinha 2023 e mostrava outro valor, enquanto os links usavam 2023.
        $this->anoSelecionado = (int) Session::get('ano_selecionado', date('Y'));
        $this->anos = $this->anosDosCiclos($this->anoSelecionado);

        // Carregar PEI
        $this->carregarPEI();
        // Identidade é carregada dentro de carregarPEI agora

        // Organização validada contra o escopo, nunca a sessão crua.
        $this->atualizarOrganizacao(Auth::user()->organizacaoSelecionadaId());
    }

    /**
     * A organização do relatório vem do navegador (seletor da tela e eventos):
     * só dentro do escopo do usuário; vazia ("todas as unidades") só para o
     * Super Admin — vazia passava sem filtro e o relatório trazia todas.
     */
    private function garantirOrganizacao(?string $id): void
    {
        $user = Auth::user();

        abort_unless($id ? $user->podeAcessarOrganizacao($id) : $user->isSuperAdmin(), 403);
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarPerspectivas();
        $this->carregarIdentidade();
    }

    /**
     * Do primeiro ao último ano dos ciclos cadastrados, mais o ano global se
     * estiver fora deles — o <select> sempre mostra o ano que os links usam.
     *
     * @return list<int>
     */
    private function anosDosCiclos(int $anoGlobal): array
    {
        $inicio = PEI::min('num_ano_inicio_pei');
        $fim = PEI::max('num_ano_fim_pei');

        $anos = $inicio && $fim ? range((int) $inicio, (int) $fim) : [];
        $anos[] = $anoGlobal;

        $anos = array_values(array_unique(array_map('intval', $anos)));
        sort($anos);

        return $anos;
    }

    /** O ciclo da tela é o mesmo dos PDFs: o selecionado no topo. */
    private function carregarPEI()
    {
        $this->peiAtivo = PEI::doContexto();

        $this->carregarPerspectivas();
        $this->carregarIdentidade();
    }

    private function carregarIdentidade()
    {
        if ($this->peiAtivo && $this->organizacaoId) {
            $this->identidade = MissaoVisaoValores::where('cod_pei', $this->peiAtivo->cod_pei)
                ->where('cod_organizacao', $this->organizacaoId)
                ->first();

            $this->valores = Valor::where('cod_pei', $this->peiAtivo->cod_pei)
                ->where('cod_organizacao', $this->organizacaoId)
                ->orderBy('nom_valor')
                ->get();

            $this->temasNorteadores = TemaNorteador::where('cod_pei', $this->peiAtivo->cod_pei)
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
        // Método público (e ouvinte de evento): o ID vem do cliente.
        $this->garantirOrganizacao($id ?: null);

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
        $this->garantirOrganizacao($id ?: null);

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
            'include_ai' => $this->includeAi, // Novo parâmetro
        ];
    }

    public function gerarInsightIA()
    {
        $this->authorize('modulo.acessar', 'relatorios');
        if (! $this->aiEnabled) {
            return;
        }
        if (! $this->organizacaoId) {
            session()->flash('error', 'Selecione uma organização.');

            return;
        }

        if (! $this->peiAtivo) {
            session()->flash('error', 'Não há Ciclo PEI ativo selecionado.');

            return;
        }

        // A unidade vem do cliente: confere o escopo e lê o nome do banco, nunca
        // de propriedade que o navegador escreve (era um prompt livre ao provedor).
        abort_unless(auth()->user()->podeAcessarOrganizacao($this->organizacaoId), 403);
        $this->organizacaoNome = Organization::find($this->organizacaoId)?->nom_organizacao;

        try {
            $aiService = AiServiceFactory::make();
            if (! $aiService) {
                return;
            }

            $this->aiInsight = 'Analisando dados estratégicos...';

            // Coletar dados básicos para o prompt
            $objetivos = Objetivo::whereHas('perspectiva', function ($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            })->get();

            $planos = PlanoDeAcao::where('cod_organizacao', $this->organizacaoId)->get();

            $prompt = "Gere um resumo executivo estratégico (AI Minute) para a organização {$this->organizacaoNome} no ano {$this->anoSelecionado}.
            Contexto: Possui ".$objetivos->count().' objetivos estratégicos e '.$planos->count().' iniciativas.
            Destaque pontos de atenção e sugestões de melhoria. Use Markdown para formatação.';

            $this->aiInsight = $aiService->suggest($prompt);
        } catch (\Exception $e) {
            \Log::error('Erro IA Relatórios: '.$e->getMessage());
            $this->aiInsight = '';
            session()->flash('error', 'Falha ao gerar resumo inteligente.');
        }
    }

    // O download vive em App\Concerns\BaixaRelatorioGerado, com verificação de
    // autorização. Não reimplementar aqui.

    public function render()
    {
        $recentReports = RelatorioGerado::where('user_id', Auth::id())
            ->latest()
            ->take(3)
            ->get();

        return view('livewire.relatorio.listar-relatorios', [
            'recentReports' => $recentReports,
        ]);
    }
}
