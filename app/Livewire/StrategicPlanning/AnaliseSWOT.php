<?php

namespace App\Livewire\StrategicPlanning;

use App\Concerns\RevalidaUnidadeNaRequisicao;
use App\Models\Organization;
use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\CenarioProspectivo;
use App\Models\StrategicPlanning\EstrategiaTows;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\ParteInteressada;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app')]
class AnaliseSWOT extends Component
{
    use AuthorizesRequests;
    use RevalidaUnidadeNaRequisicao;

    #[Locked]
    public $peiAtivo;

    #[Locked]
    public $organizacaoId;

    public $organizacaoNome;

    // Dados agrupados por categoria
    public $forcas = [];

    public $fraquezas = [];

    public $oportunidades = [];

    public $ameacas = [];

    // Estado da Visualização
    public bool $modoVisualizacao = false;

    // Aba ativa
    public string $abaAtiva = 'swot';

    // Modal SWOT
    public bool $showModal = false;

    #[Locked]
    public $itemId;

    public $dsc_categoria;

    public $dsc_item = '';

    public $num_impacto = 3;

    public $num_gravidade = 3;

    public $num_urgencia = 3;

    public $num_tendencia = 3;

    public $txt_observacao = '';

    // Partes Interessadas
    public bool $showModalParte = false;

    #[Locked]
    public ?string $parteEditId = null;

    public array $formParte = [
        'nom_parte' => '',
        'dsc_tipo' => 'Externo',
        'num_interesse' => 3,
        'num_influencia' => 3,
        'txt_estrategia_engajamento' => '',
    ];

    // Cenários Prospectivos
    public bool $showModalCenario = false;

    #[Locked]
    public ?string $cenarioEditId = null;

    public array $formCenario = [
        'nom_cenario' => '',
        'dsc_tipo' => 'Tendencial',
        'dsc_descricao' => '',
        'txt_implicacoes' => '',
        'txt_resposta_estrategica' => '',
        'num_probabilidade' => 3,
        'num_impacto' => 3,
    ];

    // Matriz TOWS
    public bool $showModalTows = false;

    #[Locked]
    public ?string $towsEditId = null;

    public array $formTows = [
        'dsc_tipo' => 'SO',
        'dsc_estrategia' => '',
        'txt_fundamentacao' => '',
        'cod_objetivo_vinculado' => '',
    ];

    public array $objetivosOptions = [];

    #[Locked]
    public bool $aiEnabled = false;

    public $aiSuggestion = '';

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
        $this->atualizarOrganizacao(Auth::user()->organizacaoSelecionadaId());
    }

    /**
     * Itens da SWOT, TOWS e cenários são da UNIDADE: a capacidade vale na
     * unidade desta tela, não na soma dos vínculos do usuário em todas.
     */
    private function autorizarNaUnidade(string $ability): void
    {
        abort_unless($this->organizacaoId !== null, 403);
        $this->authorize("modulo.{$ability}", ['planejamento-estrategico', $this->organizacaoId]);
    }

    /**
     * Partes interessadas não têm organização: valem para a instituição
     * inteira. Alterar é de quem edita o que é institucional.
     */
    private function autorizarInstitucional(string $ability): void
    {
        $this->authorize("modulo.{$ability}", 'planejamento-estrategico');
        $this->authorize('editar-institucional');
    }

    /** O registro tem de ser da unidade em operação (o id vem do navegador). */
    private function garantirDaUnidade(?string $codOrganizacao): void
    {
        abort_unless($codOrganizacao !== null && $codOrganizacao === $this->organizacaoId, 403);
    }

    public function pedirAjudaIA()
    {
        $this->autorizarNaUnidade('criar');

        if (! $this->aiEnabled) {
            return;
        }

        if (empty($this->organizacaoNome)) {
            $this->dispatch('notify', message: 'Selecione uma organização antes de usar o Agente IA.', style: 'danger');

            return;
        }

        try {
            $aiService = AiServiceFactory::make();
            if (! $aiService) {
                return;
            }

            $this->aiSuggestion = 'Pensando...';

            $prompt = "Sugira 3 Forças, 3 Fraquezas, 3 Oportunidades e 3 Ameaças para a análise SWOT da organização: {$this->organizacaoNome}.
            Responda OBRIGATORIAMENTE em formato JSON puro com as chaves 'forcas', 'fraquezas', 'oportunidades', 'ameacas', cada uma contendo um array de strings.";

            $response = $aiService->suggest($prompt);
            $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

            if (is_array($decoded)) {
                $this->aiSuggestion = $decoded;
            } else {
                throw new \Exception('Formato de resposta inválido.');
            }
        } catch (\Throwable $e) {
            \Log::error('Erro IA SWOT: '.$e->getMessage());
            $this->aiSuggestion = null;
            $this->dispatch('notify', message: 'Não foi possível gerar sugestões.', style: 'danger');
        }
    }

    public function adicionarSugerido($categoria, $item)
    {
        $this->autorizarNaUnidade('criar');
        if (! $this->peiAtivo) {
            return;
        }

        // Categoria e texto vêm do navegador (botão da sugestão da IA).
        abort_unless(in_array($categoria, self::CATEGORIAS, true), 422);
        $item = mb_substr(trim((string) $item), 0, 500);

        AnaliseAmbiental::create([
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
            'dsc_tipo_analise' => AnaliseAmbiental::TIPO_SWOT,
            'dsc_categoria' => $categoria,
            'dsc_item' => $item,
            'num_impacto' => 3,
        ]);

        $this->carregarDados();

        // Remover da sugestão
        $map = [
            'Força' => 'forcas',
            'Fraqueza' => 'fraquezas',
            'Oportunidade' => 'oportunidades',
            'Ameaça' => 'ameacas',
        ];
        $key = $map[$categoria] ?? null;

        if ($key && isset($this->aiSuggestion[$key])) {
            $this->aiSuggestion[$key] = array_filter($this->aiSuggestion[$key], fn ($i) => $item !== $i);
        }
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
        // Método público (e ouvinte de evento): o ID vem do cliente. Sem esta
        // checagem, o escopo #[Locked] era trocado por qualquer organização.
        abort_unless(! $id || Auth::user()?->podeAcessarOrganizacao($id), 403);

        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? Organization::find($id)?->nom_organizacao : null;
        $this->carregarDados();
    }

    private function carregarObjetivos(): void
    {
        $this->objetivosOptions = $this->peiAtivo
            ? Objetivo::whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))
                ->orderBy('nom_objetivo')->get(['cod_objetivo', 'nom_objetivo'])->toArray()
            : [];
    }

    public function carregarDados()
    {
        if (! $this->peiAtivo) {
            return;
        }

        $query = AnaliseAmbiental::swot()
            ->where('cod_pei', $this->peiAtivo->cod_pei)
            ->ordenado();

        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        } else {
            // Sem unidade, nunca "todas": o escopo do usuário (Super Admin vê tudo).
            Auth::user()->aplicarEscopoOrganizacional($query);
        }

        $itens = $query->get();

        $this->forcas = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_FORCA)->values()->toArray();
        $this->fraquezas = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_FRAQUEZA)->values()->toArray();
        $this->oportunidades = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_OPORTUNIDADE)->values()->toArray();
        $this->ameacas = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_AMEACA)->values()->toArray();

        $this->carregarObjetivos();
    }

    // ── Matriz TOWS ───────────────────────────────────────────────────────────

    public function novaEstrategiaTows(string $tipo = 'SO'): void
    {
        $this->autorizarNaUnidade('criar');
        $this->towsEditId = null;
        $this->formTows = ['dsc_tipo' => $tipo, 'dsc_estrategia' => '', 'txt_fundamentacao' => '', 'cod_objetivo_vinculado' => ''];
        $this->showModalTows = true;
    }

    public function editarEstrategiaTows(string $id): void
    {
        $this->autorizarNaUnidade('editar');
        $e = EstrategiaTows::findOrFail($id);
        abort_unless($this->peiAtivo && $e->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->garantirDaUnidade($e->cod_organizacao);
        $this->towsEditId = $id;
        $this->formTows = [
            'dsc_tipo' => $e->dsc_tipo,
            'dsc_estrategia' => $e->dsc_estrategia,
            'txt_fundamentacao' => $e->txt_fundamentacao ?? '',
            'cod_objetivo_vinculado' => $e->cod_objetivo_vinculado ?? '',
        ];
        $this->showModalTows = true;
    }

    public function salvarEstrategiaTows(): void
    {
        $this->autorizarNaUnidade($this->towsEditId ? 'editar' : 'criar');
        $this->validate([
            'formTows.dsc_tipo' => 'required|in:SO,ST,WO,WT',
            'formTows.dsc_estrategia' => 'required|string|max:1000',
            'formTows.cod_objetivo_vinculado' => 'nullable|uuid',
        ], ['formTows.dsc_estrategia.required' => 'Descreva a estratégia TOWS.']);

        // O objetivo vinculado vem do navegador: precisa ser do ciclo desta análise.
        $codObjetivo = $this->formTows['cod_objetivo_vinculado'] ?: null;
        if ($codObjetivo && ! Objetivo::whereKey($codObjetivo)
            ->whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))->exists()) {
            $this->addError('formTows.cod_objetivo_vinculado', 'Escolha um objetivo deste ciclo.');

            return;
        }

        $data = [
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
            'dsc_tipo' => $this->formTows['dsc_tipo'],
            'dsc_estrategia' => $this->formTows['dsc_estrategia'],
            'txt_fundamentacao' => $this->formTows['txt_fundamentacao'] ?: null,
            'cod_objetivo_vinculado' => $this->formTows['cod_objetivo_vinculado'] ?: null,
        ];

        if ($this->towsEditId) {
            $e = EstrategiaTows::findOrFail($this->towsEditId);
            $this->garantirDaUnidade($e->cod_organizacao);
            $e->update($data);
        } else {
            EstrategiaTows::create($data);
        }

        $this->showModalTows = false;
        $this->towsEditId = null;
        $this->dispatch('notify', message: 'Estratégia TOWS salva.', style: 'success');
    }

    public function excluirEstrategiaTows(string $id): void
    {
        $this->autorizarNaUnidade('excluir');
        $e = EstrategiaTows::findOrFail($id);
        abort_unless($this->peiAtivo && $e->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->garantirDaUnidade($e->cod_organizacao);
        $e->delete();
        $this->dispatch('notify', message: 'Estratégia removida.', style: 'warning');
    }

    public function toggleModoVisualizacao()
    {
        $this->modoVisualizacao = ! $this->modoVisualizacao;
    }

    public function create($categoria)
    {
        $this->autorizarNaUnidade('criar');
        $this->resetForm();
        $this->dsc_categoria = $categoria;
        $this->showModal = true;
    }

    /** As quatro categorias da SWOT — lista fechada no servidor. */
    private const CATEGORIAS = [
        AnaliseAmbiental::SWOT_FORCA, AnaliseAmbiental::SWOT_FRAQUEZA,
        AnaliseAmbiental::SWOT_OPORTUNIDADE, AnaliseAmbiental::SWOT_AMEACA,
    ];

    /**
     * Item SWOT desta unidade E deste ciclo. Conferir só a unidade deixava
     * editar (e converter em SWOT) um item PESTEL ou de outro ciclo.
     */
    private function itemDaAnalise(string $id): AnaliseAmbiental
    {
        return AnaliseAmbiental::whereKey($id)
            ->where('cod_organizacao', $this->organizacaoId)
            ->where('cod_pei', $this->peiAtivo?->cod_pei)
            ->where('dsc_tipo_analise', AnaliseAmbiental::TIPO_SWOT)
            ->firstOrFail();
    }

    public function edit($id)
    {
        $this->autorizarNaUnidade('editar');
        $item = $this->itemDaAnalise($id);
        $this->itemId = $id;
        $this->dsc_categoria = $item->dsc_categoria;
        $this->dsc_item = $item->dsc_item;
        $this->num_impacto = $item->num_impacto;
        $this->num_gravidade = $item->num_gravidade ?? 3;
        $this->num_urgencia = $item->num_urgencia ?? 3;
        $this->num_tendencia = $item->num_tendencia ?? 3;
        $this->txt_observacao = $item->txt_observacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->autorizarNaUnidade($this->itemId ? 'editar' : 'criar');
        if (! $this->peiAtivo) {
            $this->dispatch('notify', message: 'Selecione um Ciclo PEI antes de salvar.', style: 'danger');

            return;
        }

        $this->validate([
            'dsc_item' => 'required|string|max:500',
            'num_impacto' => 'required|integer|min:1|max:5',
            'num_gravidade' => 'required|integer|min:1|max:5',
            'num_urgencia' => 'required|integer|min:1|max:5',
            'num_tendencia' => 'required|integer|min:1|max:5',
            'txt_observacao' => 'nullable|string|max:1000',
            'dsc_categoria' => ['required', Rule::in(self::CATEGORIAS)],
        ]);

        $data = [
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
            'dsc_tipo_analise' => AnaliseAmbiental::TIPO_SWOT,
            'dsc_categoria' => $this->dsc_categoria,
            'dsc_item' => $this->dsc_item,
            'num_impacto' => $this->num_impacto,
            'num_gravidade' => $this->num_gravidade,
            'num_urgencia' => $this->num_urgencia,
            'num_tendencia' => $this->num_tendencia,
            'txt_observacao' => $this->txt_observacao,
        ];

        if ($this->itemId) {
            $item = $this->itemDaAnalise($this->itemId);
            $item->update($data);
            $message = 'Item atualizado com sucesso!';
        } else {
            AnaliseAmbiental::create($data);
            $message = 'Item adicionado com sucesso!';
        }

        $this->showModal = false;
        $this->carregarDados();
        $this->dispatch('notify', message: $message, style: 'success');
    }

    // ── Partes Interessadas ──────────────────────────────────────────────────

    public function novaParte(): void
    {
        $this->autorizarInstitucional('criar');
        $this->parteEditId = null;
        $this->formParte = ['nom_parte' => '', 'dsc_tipo' => 'Externo', 'num_interesse' => 3, 'num_influencia' => 3, 'txt_estrategia_engajamento' => ''];
        $this->showModalParte = true;
    }

    public function editarParte(string $id): void
    {
        $this->autorizarInstitucional('editar');
        $p = ParteInteressada::findOrFail($id);
        abort_unless($this->peiAtivo && $p->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->parteEditId = $id;
        $this->formParte = [
            'nom_parte' => $p->nom_parte,
            'dsc_tipo' => $p->dsc_tipo,
            'num_interesse' => $p->num_interesse,
            'num_influencia' => $p->num_influencia,
            'txt_estrategia_engajamento' => $p->txt_estrategia_engajamento ?? '',
        ];
        $this->showModalParte = true;
    }

    public function salvarParte(): void
    {
        $this->autorizarInstitucional($this->parteEditId ? 'editar' : 'criar');
        $this->validate([
            'formParte.nom_parte' => 'required|string|max:150',
            'formParte.num_interesse' => 'required|integer|min:1|max:5',
            'formParte.num_influencia' => 'required|integer|min:1|max:5',
        ], ['formParte.nom_parte.required' => 'Informe o nome da parte interessada.']);

        $data = array_merge($this->formParte, ['cod_pei' => $this->peiAtivo->cod_pei]);

        if ($this->parteEditId) {
            $parte = ParteInteressada::findOrFail($this->parteEditId);
            abort_unless($parte->cod_pei === $this->peiAtivo->cod_pei, 403);
            $parte->update($data);
        } else {
            ParteInteressada::create($data);
        }

        $this->showModalParte = false;
        $this->parteEditId = null;
        $this->dispatch('notify', message: 'Parte interessada salva.', style: 'success');
    }

    public function excluirParte(string $id): void
    {
        $this->autorizarInstitucional('excluir');
        $parte = ParteInteressada::findOrFail($id);
        abort_unless($this->peiAtivo && $parte->cod_pei === $this->peiAtivo->cod_pei, 403);
        $parte->delete();
        $this->dispatch('notify', message: 'Parte interessada removida.', style: 'warning');
    }

    // ── Cenários Prospectivos ─────────────────────────────────────────────────

    public function novoCenario(): void
    {
        $this->autorizarNaUnidade('criar');
        $this->cenarioEditId = null;
        $this->formCenario = ['nom_cenario' => '', 'dsc_tipo' => 'Tendencial', 'dsc_descricao' => '', 'txt_implicacoes' => '', 'txt_resposta_estrategica' => '', 'num_probabilidade' => 3, 'num_impacto' => 3];
        $this->showModalCenario = true;
    }

    public function editarCenario(string $id): void
    {
        $this->autorizarNaUnidade('editar');
        $c = CenarioProspectivo::findOrFail($id);
        abort_unless($this->peiAtivo && $c->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->garantirDaUnidade($c->cod_organizacao);
        $this->cenarioEditId = $id;
        $this->formCenario = [
            'nom_cenario' => $c->nom_cenario,
            'dsc_tipo' => $c->dsc_tipo,
            'dsc_descricao' => $c->dsc_descricao ?? '',
            'txt_implicacoes' => $c->txt_implicacoes ?? '',
            'txt_resposta_estrategica' => $c->txt_resposta_estrategica ?? '',
            'num_probabilidade' => $c->num_probabilidade,
            'num_impacto' => $c->num_impacto,
        ];
        $this->showModalCenario = true;
    }

    public function salvarCenario(): void
    {
        $this->autorizarNaUnidade($this->cenarioEditId ? 'editar' : 'criar');
        $this->validate([
            'formCenario.nom_cenario' => 'required|string|max:150',
            'formCenario.dsc_tipo' => 'required|in:Otimista,Tendencial,Pessimista',
            'formCenario.num_probabilidade' => 'required|integer|min:1|max:5',
            'formCenario.num_impacto' => 'required|integer|min:1|max:5',
        ], ['formCenario.nom_cenario.required' => 'Informe o nome do cenário.']);

        $data = array_merge($this->formCenario, [
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
        ]);

        if ($this->cenarioEditId) {
            $c = CenarioProspectivo::findOrFail($this->cenarioEditId);
            $this->garantirDaUnidade($c->cod_organizacao);
            $c->update($data);
        } else {
            CenarioProspectivo::create($data);
        }

        $this->showModalCenario = false;
        $this->cenarioEditId = null;
        $this->dispatch('notify', message: 'Cenário prospectivo salvo.', style: 'success');
    }

    public function excluirCenario(string $id): void
    {
        $this->autorizarNaUnidade('excluir');
        $cenario = CenarioProspectivo::findOrFail($id);
        abort_unless($this->peiAtivo && $cenario->cod_pei === $this->peiAtivo->cod_pei, 403);
        $this->garantirDaUnidade($cenario->cod_organizacao);
        $cenario->delete();
        $this->dispatch('notify', message: 'Cenário removido.', style: 'warning');
    }

    public function delete($id)
    {
        $this->autorizarNaUnidade('excluir');
        $item = $this->itemDaAnalise($id);
        $item->delete();
        $this->carregarDados();
        $this->dispatch('notify', message: 'Item removido com sucesso!', style: 'warning');
    }

    public function resetForm()
    {
        $this->itemId = null;
        $this->dsc_categoria = '';
        $this->dsc_item = '';
        $this->num_impacto = 3;
        $this->num_gravidade = 3;
        $this->num_urgencia = 3;
        $this->num_tendencia = 3;
        $this->txt_observacao = '';
    }

    public function render()
    {
        $partes = $this->peiAtivo
            ? ParteInteressada::where('cod_pei', $this->peiAtivo->cod_pei)->orderBy('num_influencia', 'desc')->orderBy('num_interesse', 'desc')->get()
            : collect();

        // Cenários e TOWS são da unidade: listavam os de TODAS as unidades.
        $cenarios = $this->peiAtivo
            ? CenarioProspectivo::where('cod_pei', $this->peiAtivo->cod_pei)
                ->when(
                    $this->organizacaoId,
                    fn ($q) => $q->where('cod_organizacao', $this->organizacaoId),
                    fn ($q) => Auth::user()->aplicarEscopoOrganizacional($q)
                )
                ->orderBy('num_ordem')->orderBy('dsc_tipo')->get()
            : collect();

        $tows = $this->peiAtivo
            ? EstrategiaTows::where('cod_pei', $this->peiAtivo->cod_pei)
                ->when(
                    $this->organizacaoId,
                    fn ($q) => $q->where('cod_organizacao', $this->organizacaoId),
                    fn ($q) => Auth::user()->aplicarEscopoOrganizacional($q)
                )
                ->with('objetivo')
                ->orderBy('dsc_tipo')
                ->get()
                ->groupBy('dsc_tipo')
            : collect();

        return view('livewire.p-e-i.analise-s-w-o-t', [
            'categorias' => AnaliseAmbiental::categoriasSWOT(),
            'partes' => $partes,
            'tiposParte' => ParteInteressada::TIPOS,
            'cenarios' => $cenarios,
            'tiposCenario' => CenarioProspectivo::TIPOS,
            'tows' => $tows,
            'tiposTows' => EstrategiaTows::TIPOS,
            // A tela só oferece o botão que o servidor aceita.
            ...$this->permissoes(),
        ]);
    }

    /** @return array<string, bool> */
    private function permissoes(): array
    {
        $naUnidade = fn (string $a) => $this->organizacaoId !== null
            && Gate::allows("modulo.{$a}", ['planejamento-estrategico', $this->organizacaoId]);
        $institucional = fn (string $a) => Gate::allows("modulo.{$a}", 'planejamento-estrategico')
            && Gate::allows('editar-institucional');

        return [
            'podeCriar' => $naUnidade('criar'),
            'podeEditar' => $naUnidade('editar'),
            'podeExcluir' => $naUnidade('excluir'),
            'podeCriarParte' => $institucional('criar'),
            'podeEditarParte' => $institucional('editar'),
            'podeExcluirParte' => $institucional('excluir'),
        ];
    }
}
