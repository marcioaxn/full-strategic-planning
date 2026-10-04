<?php

namespace App\Livewire\PerformanceIndicators;

use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\Organization;
use App\Models\PerformanceIndicators\Indicador;
use App\Models\PerformanceIndicators\LinhaBaseIndicador;
use App\Models\PerformanceIndicators\MetaPorAno;
use App\Models\StrategicPlanning\GrauSatisfacao;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\StrategicPlanning\PEI;
use App\Models\SystemSetting;
use App\Services\AI\AiServiceFactory;
use App\Services\PeiGuidanceService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

// Sem #[Layout] fixo: o layout é escolhido no render(), porque esta tela
// também é servida ao visitante pelo Mapa Estratégico público.
class ListarIndicadores extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public $search = '';

    public $filtroVinculo = '';

    public $filtroObjetivo = '';

    // Só o servidor define (atualizarOrganizacao confere o escopo).
    #[Locked]
    public $organizacaoId;

    public $peiAtivo;

    // IA e Mentor
    #[Locked]
    public bool $aiEnabled = false;

    public $aiSuggestion = '';

    public $smartFeedback = '';

    // Modais
    public bool $showModal = false;

    public bool $showDeleteModal = false;

    public bool $showMetasModal = false;

    public bool $showLinhaBaseModal = false;

    // Só o servidor define (edit/confirmDelete, ambos autorizados).
    #[Locked]
    public $indicadorId;

    public $indicadorSelecionado;

    // Success Modal Properties
    public bool $showSuccessModal = false;

    public bool $showErrorModal = false;

    public string $successMessage = '';

    public string $errorMessage = '';

    public string $createdIndicadorName = '';

    // Form Indicador
    public $form = [
        'nom_indicador' => '',
        'dsc_indicador' => '',
        'dsc_tipo' => 'Objetivo',
        'dsc_calculation_type' => 'manual',
        'cod_objetivo' => '',
        'cod_plano_de_acao' => '',
        'txt_observacao' => '',
        'dsc_meta' => '',
        'dsc_unidade_medida' => 'Percentual (%)',
        'dsc_polaridade' => 'Positiva',
        'num_peso' => 1,
        'bln_acumulado' => 'Não',
        'dsc_formula' => '',
        'dsc_fonte' => '',
        'dsc_periodo_medicao' => 'Mensal',
        'dsc_referencial_comparativo' => '',
        'dsc_atributos' => '',
        'organizacoes_ids' => [],
        'smart' => ['especifico' => false, 'mensuravel' => false, 'atingivel' => false, 'relevante' => false, 'temporal' => false],
    ];

    // Form Metas/Linha Base
    public $metaAno;

    public $metaValor;

    public $linhaBaseAno;

    public $linhaBaseValor;

    // Listas Auxiliares
    public $objetivosAgrupados = [];

    public $planosAgrupados = [];

    public $organizacoesOptions = [];

    public $unidadesMedida = [];

    public $polaridades = [];

    public $calculationTypes = [];

    public $periodosOptions = ['Mensal', 'Bimestral', 'Trimestral', 'Semestral', 'Anual'];

    public $grausSatisfacao = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroVinculo' => ['except' => ''],
        'filtroObjetivo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI',
    ];

    public function mount()
    {
        // Logado: organização validada contra o escopo. Visitante da
        // Transparência: a seleção pública da sessão, como antes.
        $this->organizacaoId = Auth::check()
            ? Auth::user()->organizacaoSelecionadaId()
            : Session::get('organizacao_selecionada_id');

        if ($this->filtroObjetivo) {
            $obj = Objetivo::with('perspectiva.pei')->find($this->filtroObjetivo);
            if ($obj) {
                $this->peiAtivo = $obj->perspectiva->pei;
            }
        }

        if (! $this->peiAtivo) {
            $this->peiAtivo = PEI::find(Session::get('pei_selecionado_id')) ?? PEI::ativos()->first();
        }

        $this->carregarListasAuxiliares();

        $this->aiEnabled = SystemSetting::getValue('ai_enabled', true);
    }

    public function atualizarOrganizacao($id)
    {
        // Método público (e ouvinte de evento): o ID vem do cliente. Logado, só
        // dentro do próprio escopo; o visitante da área pública só consulta.
        abort_unless(! $id || ! Auth::check() || Auth::user()->podeAcessarOrganizacao($id), 403);

        // Logado sem Super Admin: "nenhuma organização" não é "todas".
        if (! $id && Auth::check() && ! Auth::user()->isSuperAdmin()) {
            $id = Auth::user()->organizacaoSelecionadaId();
        }

        $this->organizacaoId = $id;
        $this->resetPage();
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarListasAuxiliares();
        $this->resetPage();
    }

    public function carregarListasAuxiliares()
    {
        $this->grausSatisfacao = GrauSatisfacao::doPei($this->peiAtivo?->cod_pei)->get();
        $this->unidadesMedida = Indicador::UNIDADES_MEDIDA;
        $this->polaridades = Indicador::POLARIDADES;
        $this->calculationTypes = Indicador::CALCULATION_TYPES;
        $this->organizacoesOptions = Organization::getTreeForSelector();

        if ($this->peiAtivo) {
            // Agrupar objetivos por perspectiva para o select
            $objetivos = Objetivo::whereHas('perspectiva', function ($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            })->with('perspectiva')->get();

            $this->objetivosAgrupados = $objetivos->groupBy(function ($obj) {
                return $obj->perspectiva->dsc_perspectiva;
            })->map(function ($group) {
                return $group->map(function ($obj) {
                    return [
                        'cod_objetivo' => $obj->cod_objetivo,
                        'nom_objetivo' => $obj->nom_objetivo,
                    ];
                });
            })->toArray();

            // Agrupar planos por objetivo
            // Só as iniciativas das unidades do usuário: a lista oferecia as de todas.
            $planos = PlanoDeAcao::whereHas('objetivo.perspectiva', function ($q) {
                $q->where('cod_pei', $this->peiAtivo->cod_pei);
            })
                ->when(Auth::check() && ! Auth::user()->isSuperAdmin(), fn ($q) => $q->whereIn(
                    'action_plan.tab_plano_de_acao.cod_organizacao',
                    Auth::user()->organizacaoIdsPermitidas()->all()
                ))
                ->with('objetivo')->get();

            $this->planosAgrupados = $planos->groupBy(function ($plano) {
                return $plano->objetivo->nom_objetivo ?? 'Sem Objetivo';
            })->map(function ($group) {
                return $group->map(function ($plano) {
                    return [
                        'cod_plano_de_acao' => $plano->cod_plano_de_acao,
                        'dsc_plano_de_acao' => $plano->dsc_plano_de_acao,
                    ];
                });
            })->toArray();
        }
    }

    public function create(PeiGuidanceService $service)
    {
        // Autorização ANTES do pré-requisito. Com a ordem invertida, quem não
        // tem permissão (inclusive o visitante) saía pelo return do bloqueio
        // sem nunca passar pela verificação — e a tela é pública para leitura.
        $this->authorize('modulo.criar', 'indicadores');

        $bloqueio = $service->verificarPreRequisitos('indicadores', $this->peiAtivo?->cod_pei ?? null);
        if ($bloqueio) {
            $this->dispatch('notify', message: $bloqueio['mensagem'], style: 'warning');

            return;
        }
        $this->authorize('create', [Indicador::class, $this->organizacaoId]);
        $this->resetForm();
        if ($this->organizacaoId) {
            $this->form['organizacoes_ids'] = [$this->organizacaoId];
        }
        $this->showModal = true;
    }

    public function edit($id)
    {
        $indicador = Indicador::with('organizacoes')->findOrFail($id);
        $this->authorize('update', $indicador);
        $this->indicadorId = $id;
        $this->form = [
            'nom_indicador' => $indicador->nom_indicador,
            'dsc_indicador' => $indicador->dsc_indicador,
            // O vínculo decide o tipo; "Estratégico" (legado) e "Plano" viram os dois valores da tela.
            'dsc_tipo' => $indicador->cod_plano_de_acao ? 'Iniciativa' : 'Objetivo',
            'dsc_calculation_type' => $indicador->dsc_calculation_type ?? 'manual',
            'cod_objetivo' => $indicador->cod_objetivo,
            'cod_plano_de_acao' => $indicador->cod_plano_de_acao,
            'txt_observacao' => $indicador->txt_observacao,
            'dsc_meta' => $indicador->dsc_meta,
            'dsc_unidade_medida' => $indicador->dsc_unidade_medida,
            'dsc_polaridade' => $indicador->dsc_polaridade ?? 'Positiva',
            'num_peso' => $indicador->num_peso,
            'bln_acumulado' => $indicador->bln_acumulado,
            'dsc_formula' => $indicador->dsc_formula,
            'dsc_fonte' => $indicador->dsc_fonte,
            'dsc_periodo_medicao' => $indicador->dsc_periodo_medicao,
            'dsc_referencial_comparativo' => $indicador->dsc_referencial_comparativo,
            'dsc_atributos' => $indicador->dsc_atributos,
            'organizacoes_ids' => $indicador->organizacoes->pluck('cod_organizacao')->toArray(),
            'smart' => $indicador->json_smart ?? ['especifico' => false, 'mensuravel' => false, 'atingivel' => false, 'relevante' => false, 'temporal' => false],
        ];
        $this->showModal = true;
    }

    public function save()
    {
        // 🔴 A tela enviava "Plano" e o servidor só conferia a iniciativa quando
        // o tipo era "Iniciativa": com "Plano", nenhuma validação nem authorize
        // rodava e o cod_plano_de_acao vindo do navegador era gravado. O tipo
        // agora é uma lista fechada; "Plano" de snapshot antigo é normalizado.
        if (($this->form['dsc_tipo'] ?? null) === 'Plano') {
            $this->form['dsc_tipo'] = 'Iniciativa';
        }

        // Regras de validação base
        $rules = [
            'form.nom_indicador' => 'required|string|max:255',
            'form.dsc_tipo' => ['required', Rule::in(['Objetivo', 'Iniciativa'])],
            'form.dsc_calculation_type' => ['required', Rule::in(['manual', 'action_plan'])],
            'form.dsc_polaridade' => ['nullable', Rule::in(array_keys(Indicador::POLARIDADES))],
            'form.dsc_unidade_medida' => 'required|string|max:191',
            'form.organizacoes_ids' => 'required|array|min:1',
            'form.organizacoes_ids.*' => 'uuid',
        ];

        // Validação condicional: Se tipo é Plano E cálculo é automático, plano é obrigatório
        if ($this->form['dsc_tipo'] === 'Iniciativa') {
            $rules['form.cod_plano_de_acao'] = 'required|uuid|exists:tab_plano_de_acao,cod_plano_de_acao';

            // Se cálculo automático, reforçar mensagem
            if ($this->form['dsc_calculation_type'] === 'action_plan') {
                $rules['form.cod_plano_de_acao'] = 'required|uuid|exists:tab_plano_de_acao,cod_plano_de_acao';
            }
        } elseif ($this->form['dsc_tipo'] === 'Objetivo') {
            $rules['form.cod_objetivo'] = 'required|uuid|exists:tab_objetivo,cod_objetivo';
        }

        $messages = [
            'form.cod_plano_de_acao.required' => 'Para indicadores com cálculo automático, é obrigatório selecionar uma Iniciativa.',
            'form.cod_objetivo.required' => 'Selecione o Objetivo Estratégico vinculado a este indicador.',
        ];

        $this->validate($rules, $messages);

        $this->autorizarGravacao();

        try {
            // Só os campos do formulário: o array público aceita chave nova vinda
            // do navegador, e $this->form inteiro iria para o update().
            $data = Arr::only($this->form, [
                'nom_indicador', 'dsc_indicador', 'dsc_tipo', 'dsc_calculation_type', 'cod_objetivo',
                'cod_plano_de_acao', 'txt_observacao', 'dsc_meta', 'dsc_unidade_medida', 'dsc_polaridade',
                'num_peso', 'bln_acumulado', 'dsc_formula', 'dsc_fonte', 'dsc_periodo_medicao',
                'dsc_referencial_comparativo', 'dsc_atributos',
            ]);
            $orgIds = array_values((array) ($this->form['organizacoes_ids'] ?? []));
            $data['json_smart'] = array_map('boolval', array_intersect_key(
                (array) ($this->form['smart'] ?? []),
                array_flip(['especifico', 'mensuravel', 'atingivel', 'relevante', 'temporal'])
            ));

            if ($data['dsc_tipo'] === 'Objetivo') {
                $data['cod_plano_de_acao'] = null;
                $data['dsc_calculation_type'] = 'manual';
            } else {
                $data['cod_objetivo'] = null;
            }

            DB::transaction(function () use ($data, $orgIds) {
                if ($this->indicadorId) {
                    $indicador = Indicador::findOrFail($this->indicadorId);
                    $indicador->update($data);
                    $indicador->organizacoes()->sync($orgIds);
                    $this->successMessage = 'As configurações do indicador foram atualizadas com sucesso e as organizações vinculadas já refletem as mudanças.';
                } else {
                    $indicador = Indicador::create($data);
                    $indicador->organizacoes()->sync($orgIds);
                    $this->successMessage = 'O novo indicador foi registrado com sucesso e vinculado às unidades organizacionais selecionadas.';
                }
            });

            $this->createdIndicadorName = $this->form['nom_indicador'];
            $this->showModal = false;
            $this->showSuccessModal = true;

        } catch (\Exception $e) {
            // Sem isto, a causa real desaparece: o cliente recebe uma
            // orientação genérica e não sobra rastro nenhum para investigar.
            report($e);

            $this->errorMessage = 'Não foi possível processar o registro do indicador. Por favor, revise as informações e tente novamente.';
            $this->showErrorModal = true;
        }
    }

    /**
     * Organizações e iniciativa vêm do navegador: cada uma é conferida.
     *
     * 🔴 Antes só se checava o perfil: o Administrador da unidade A criava
     * indicador vinculado à B, e o Gestor de uma iniciativa trocava o vínculo
     * para uma iniciativa de outra unidade.
     */
    private function autorizarGravacao(): void
    {
        abort_unless(Auth::check(), 403);
        $usuario = Auth::user();
        $orgs = array_values(array_filter((array) ($this->form['organizacoes_ids'] ?? [])));
        $existente = $this->indicadorId ? Indicador::with(['organizacoes', 'planoDeAcao'])->findOrFail($this->indicadorId) : null;

        if ($existente) {
            $this->authorize('update', $existente);
            $atuais = $existente->organizacoes->pluck('cod_organizacao')->all();
            // Unidade acrescentada OU retirada do vínculo: só quem a administra.
            // Antes só a acrescentada era conferida — o Gestor tirava o indicador
            // da unidade do Administrador original.
            $alteradas = array_merge(array_diff($orgs, $atuais), array_diff($atuais, $orgs));
            foreach ($alteradas as $org) {
                abort_unless($usuario->isSuperAdmin() || $usuario->ehAdministradorEm($org), 403);
            }
        } else {
            foreach ($orgs as $org) {
                abort_unless($usuario->podeAcessarOrganizacao($org), 403);
                $this->authorize('create', [Indicador::class, $org]);
            }
        }

        $codPlano = ($this->form['dsc_tipo'] ?? '') === 'Iniciativa' ? ($this->form['cod_plano_de_acao'] ?? null) : null;
        if (! $codPlano) {
            // Fora de "Iniciativa", vínculo com iniciativa nunca é gravado.
            $this->form['cod_plano_de_acao'] = null;
        }

        if ($codPlano && $codPlano !== $existente?->cod_plano_de_acao) {
            // A iniciativa escolhida precisa ser uma que o usuário pode editar.
            $this->authorize('update', PlanoDeAcao::findOrFail($codPlano));
        }

        // Quem não administra nenhuma das unidades (o Gestor) só cria indicador
        // de uma iniciativa pela qual responde — nunca de objetivo.
        if (! $existente && ! $usuario->isSuperAdmin()) {
            $administra = collect($orgs)->contains(fn ($org) => $usuario->ehAdministradorEm($org));
            abort_unless($administra || ($codPlano && $usuario->ehGestorDaIniciativa($codPlano)), 403);
        }
    }

    // --- Gestão de Metas ---
    public function abrirMetas($id)
    {
        $this->indicadorSelecionado = Indicador::with('metasPorAno')->findOrFail($id);
        $this->authorize('update', $this->indicadorSelecionado);
        $this->metaAno = now()->year;
        $this->metaValor = '';
        $this->showMetasModal = true;
    }

    /**
     * 🔴 A AUTORIZAÇÃO ESTAVA SÓ EM abrirMetas().
     *
     * Todo método público de um componente Livewire é invocável direto do
     * navegador — o cliente não precisa passar pelo modal para chegar aqui.
     * Autorizar ao ABRIR e não ao SALVAR protege a tela, não o dado. E desde
     * que /indicadores passou a ser servida também na área pública de
     * transparência, a chamada nem exige sessão: /livewire/update não passa
     * pelo middleware que barra escrita nas rotas públicas.
     */
    public function salvarMeta()
    {
        abort_unless($this->indicadorSelecionado, 403);
        $this->authorize('update', $this->indicadorSelecionado);

        $this->validate([
            'metaAno' => 'required|integer|min:2000|max:2100',
            'metaValor' => 'required|numeric',
        ]);

        MetaPorAno::updateOrCreate(
            ['cod_indicador' => $this->indicadorSelecionado->cod_indicador, 'num_ano' => $this->metaAno],
            ['meta' => $this->metaValor]
        );

        $this->abrirMetas($this->indicadorSelecionado->cod_indicador); // Refresh
        $this->dispatch('notify', message: 'Meta salva!', style: 'success');
    }

    /**
     * 🔴 APAGAVA POR ID, SEM AUTORIZAÇÃO E SEM CONFERIR O DONO.
     *
     * Recebia um id qualquer e apagava. Nem verificava se a meta pertencia ao
     * indicador aberto — bastava chamar o método com o id de outra para apagar
     * a meta de qualquer indicador do sistema, de qualquer organização.
     */
    public function excluirMeta($id)
    {
        abort_unless($this->indicadorSelecionado, 403);
        $this->authorize('update', $this->indicadorSelecionado);

        $meta = MetaPorAno::findOrFail($id);

        abort_unless($meta->cod_indicador === $this->indicadorSelecionado->cod_indicador, 403);

        $meta->delete();
        $this->abrirMetas($this->indicadorSelecionado->cod_indicador);
    }

    // --- Gestão de Linha de Base ---
    public function abrirLinhaBase($id)
    {
        $this->indicadorSelecionado = Indicador::with('linhaBase')->findOrFail($id);
        $this->authorize('update', $this->indicadorSelecionado);
        $this->linhaBaseAno = now()->year - 1;
        $this->linhaBaseValor = '';
        $this->showLinhaBaseModal = true;
    }

    public function salvarLinhaBase()
    {
        abort_unless($this->indicadorSelecionado, 403);
        $this->authorize('update', $this->indicadorSelecionado);

        $this->validate([
            'linhaBaseAno' => 'required|integer|min:2000|max:2100',
            'linhaBaseValor' => 'required|numeric',
        ]);

        LinhaBaseIndicador::updateOrCreate(
            ['cod_indicador' => $this->indicadorSelecionado->cod_indicador, 'num_ano' => $this->linhaBaseAno],
            ['num_linha_base' => $this->linhaBaseValor]
        );

        $this->abrirLinhaBase($this->indicadorSelecionado->cod_indicador);
        $this->dispatch('notify', message: 'Linha de base salva!', style: 'success');
    }

    /** Mesmo defeito de excluirMeta(): apagava por id, sem dono e sem guarda. */
    public function excluirLinhaBase($id)
    {
        abort_unless($this->indicadorSelecionado, 403);
        $this->authorize('update', $this->indicadorSelecionado);

        $linha = LinhaBaseIndicador::findOrFail($id);

        abort_unless($linha->cod_indicador === $this->indicadorSelecionado->cod_indicador, 403);

        $linha->delete();
        $this->abrirLinhaBase($this->indicadorSelecionado->cod_indicador);
    }

    public function confirmDelete($id)
    {
        $this->authorize('delete', Indicador::findOrFail($id));
        $this->indicadorId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $indicador = Indicador::findOrFail($this->indicadorId);
        $this->authorize('delete', $indicador);
        $indicador->delete();
        $this->showDeleteModal = false;

        $this->dispatch('mentor-notification',
            title: 'Indicador Removido',
            message: 'O KPI foi excluído com sucesso.',
            icon: 'bi-trash',
            type: 'warning'
        );
    }

    public function resetForm()
    {
        $this->indicadorId = null;
        $this->form = [
            'nom_indicador' => '', 'dsc_indicador' => '', 'dsc_tipo' => 'Objetivo',
            'dsc_calculation_type' => 'manual',
            'cod_objetivo' => '', 'cod_plano_de_acao' => '', 'txt_observacao' => '',
            'dsc_meta' => '', 'dsc_unidade_medida' => 'Percentual (%)', 'dsc_polaridade' => 'Positiva', 'num_peso' => 1, 'bln_acumulado' => 'Não',
            'dsc_formula' => '', 'dsc_fonte' => '', 'dsc_periodo_medicao' => 'Mensal',
            'dsc_referencial_comparativo' => '', 'dsc_atributos' => '',
            'organizacoes_ids' => [],
            'smart' => ['especifico' => false, 'mensuravel' => false, 'atingivel' => false, 'relevante' => false, 'temporal' => false],
        ];
    }

    public function closeSuccessModal()
    {
        $this->showSuccessModal = false;
        $this->createdIndicadorName = '';
        $this->resetForm();
    }

    public function closeErrorModal()
    {
        $this->showErrorModal = false;
    }

    /**
     * Sugestão de indicadores pela IA configurada.
     *
     * Antes devolvia sempre as mesmas duas sugestões fixas, para qualquer
     * organização e qualquer ciclo, sob um botão "Sugerir com IA": a tela
     * dizia que a IA tinha pensado algo que ninguém pensou.
     */
    public function pedirAjudaIA()
    {
        // Tela também pública: visitante não aciona a IA (serviço pago).
        abort_unless(Auth::check(), 403);

        if (! $this->aiEnabled) {
            return;
        }

        $aiService = AiServiceFactory::make();
        if (! $aiService) {
            session()->flash('error', 'Nenhum provedor de IA configurado. Configure em Configurações do Sistema.');

            return;
        }

        $objetivos = $this->peiAtivo
            ? Objetivo::whereHas('perspectiva', fn ($q) => $q->where('cod_pei', $this->peiAtivo->cod_pei))
                ->orderBy('nom_objetivo')->limit(15)->pluck('nom_objetivo')->implode('; ')
            : '';
        $organizacao = $this->organizacaoId ? Organization::find($this->organizacaoId)?->nom_organizacao : null;
        $unidades = implode(', ', Indicador::UNIDADES_MEDIDA);

        $prompt = 'Sugira 3 indicadores de desempenho (KPIs) SMART'
            .($organizacao ? " para a organização '{$organizacao}'" : '')
            .($objetivos ? ", alinhados a estes objetivos estratégicos: {$objetivos}" : '')
            .". A unidade deve ser uma destas: {$unidades}."
            ." Responda OBRIGATORIAMENTE em JSON puro: um array de objetos com os campos 'nome', 'descricao', 'unidade' e 'formula'.";

        try {
            $decoded = json_decode(str_replace(['```json', '```'], '', $aiService->suggest($prompt)), true);
        } catch (\Throwable $e) {
            report($e);
            $decoded = null;
        }

        if (! is_array($decoded)) {
            $this->aiSuggestion = '';
            session()->flash('error', 'Falha ao processar sugestões da IA. Tente novamente.');

            return;
        }

        $this->aiSuggestion = collect($decoded)
            ->filter(fn ($s) => is_array($s) && ! empty($s['nome']))
            ->map(fn ($s) => [
                'nome' => (string) $s['nome'],
                'descricao' => (string) ($s['descricao'] ?? ''),
                'unidade' => in_array($s['unidade'] ?? null, Indicador::UNIDADES_MEDIDA, true) ? $s['unidade'] : Indicador::UNIDADES_MEDIDA[0],
                'formula' => (string) ($s['formula'] ?? ''),
            ])
            ->values()
            ->all();
    }

    public function aplicarSugestao($nome, $desc, $unidade, $formula)
    {
        $this->resetForm();
        $this->form['nom_indicador'] = $nome;
        $this->form['dsc_indicador'] = $desc;
        $this->form['dsc_unidade_medida'] = $unidade;
        $this->form['dsc_formula'] = $formula;
        if ($this->organizacaoId) {
            $this->form['organizacoes_ids'] = [$this->organizacaoId];
        }
        $this->aiSuggestion = '';
        $this->showModal = true;
    }

    public function render()
    {
        $query = Indicador::query()->with(['objetivo', 'planoDeAcao', 'evolucoes', 'metasPorAno', 'planosDeAcaoVinculados']);

        // Se há filtro por objetivo específico, prioriza esse filtro
        if ($this->filtroObjetivo) {
            // Busca indicadores diretamente vinculados ao objetivo
            // OU vinculados a iniciativas desse objetivo
            $query->where(function ($q) {
                $q->where('cod_objetivo', $this->filtroObjetivo)
                    ->orWhereHas('planoDeAcao', function ($sub) {
                        $sub->where('cod_objetivo', $this->filtroObjetivo);
                    });
            });
        }

        // O filtro de objetivo SE SOMA ao de unidade (antes o substituía).
        if ($this->organizacaoId) {
            // A unidade selecionada e as subordinadas, dentro do que o usuário
            // alcança. Visitante: só a unidade exata, como antes.
            $usuario = auth()->user();
            $orgIds = Organization::descendentesEProprio($this->organizacaoId);
            if ($usuario && ! $usuario->isSuperAdmin()) {
                $orgIds = array_values(array_intersect($orgIds, $usuario->organizacaoIdsPermitidas()->all()));
            } elseif (! $usuario) {
                $orgIds = [$this->organizacaoId];
            }

            $query->where(function ($q) use ($orgIds) {
                $q->whereIn('tab_indicador.cod_indicador', function ($sub) use ($orgIds) {
                    $sub->select('cod_indicador')
                        ->from('performance_indicators.rel_indicador_objetivo_organizacao')
                        ->whereIn('cod_organizacao', $orgIds);
                })->orWhereHas('planoDeAcao', function ($sub) use ($orgIds) {
                    $sub->whereHas('organizacoes', function ($subOrg) use ($orgIds) {
                        $subOrg->whereIn('tab_organizacoes.cod_organizacao', $orgIds);
                    });
                });
            });
        }

        // Só o ciclo selecionado no topo. Sem isto, com o ciclo 2040-2043
        // selecionado a tela listava os indicadores de 2023-2027 — e "Lançar
        // Evolução" gravava num ciclo que o usuário não estava vendo.
        if ($this->peiAtivo && ! $this->filtroObjetivo) {
            $codPei = $this->peiAtivo->cod_pei;
            $query->where(function ($q) use ($codPei) {
                $q->whereHas('objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $codPei))
                    ->orWhereHas('planoDeAcao.objetivo.perspectiva', fn ($p) => $p->where('cod_pei', $codPei));
            });
        }

        if ($this->search) {
            $query->where('nom_indicador', 'ilike', '%'.$this->search.'%');
        }

        if ($this->filtroVinculo === 'Objetivo') {
            $query->deObjetivo();
        } elseif ($this->filtroVinculo === 'Iniciativa') {
            $query->dePlano();
        }

        // Layout dinâmico: o visitante chega aqui pelo Mapa Estratégico público
        // e não tem menu autenticado. Mesmo critério do MapaEstrategico.
        return view('livewire.indicador.listar-indicadores', [
            'indicadores' => $query->paginate(10),
        ])
            ->layout(Auth::check() ? 'layouts.app' : 'layouts.public');
    }
}
