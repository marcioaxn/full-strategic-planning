<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\AnaliseAmbiental;
use App\Models\StrategicPlanning\CenarioProspectivo;
use App\Models\StrategicPlanning\ParteInteressada;
use App\Models\StrategicPlanning\PEI;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class AnaliseSWOT extends Component
{
    use AuthorizesRequests;

    public $peiAtivo;
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
    public $itemId;
    public $dsc_categoria;
    public $dsc_item = '';
    public $num_impacto   = 3;
    public $num_gravidade = 3;
    public $num_urgencia  = 3;
    public $num_tendencia = 3;
    public $txt_observacao = '';

    // Partes Interessadas
    public bool $showModalParte = false;
    public ?string $parteEditId = null;
    public array $formParte = [
        'nom_parte'                  => '',
        'dsc_tipo'                   => 'Externo',
        'num_interesse'              => 3,
        'num_influencia'             => 3,
        'txt_estrategia_engajamento' => '',
    ];

    // Cenários Prospectivos
    public bool $showModalCenario = false;
    public ?string $cenarioEditId = null;
    public array $formCenario = [
        'nom_cenario'              => '',
        'dsc_tipo'                 => 'Tendencial',
        'dsc_descricao'            => '',
        'txt_implicacoes'          => '',
        'txt_resposta_estrategica' => '',
        'num_probabilidade'        => 3,
        'num_impacto'              => 3,
    ];

    public bool $aiEnabled = false;
    public $aiSuggestion = '';

    protected $listeners = [
        'organizacaoSelecionada' => 'atualizarOrganizacao',
        'peiSelecionado' => 'atualizarPEI'
    ];

    public function mount()
    {
        $this->aiEnabled = \App\Models\SystemSetting::getValue('ai_enabled', true);
        $this->carregarPEI();
        $this->atualizarOrganizacao(Session::get('organizacao_selecionada_id'));
    }

    public function pedirAjudaIA()
    {
        if (!$this->aiEnabled) return;

        if (empty($this->organizacaoNome)) {
            session()->flash('error', 'Selecione uma organização antes de usar o Agente IA.');
            return;
        }

        try {
            $aiService = \App\Services\AI\AiServiceFactory::make();
            if (!$aiService) return;

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
            \Log::error('Erro IA SWOT: ' . $e->getMessage());
            $this->aiSuggestion = null;
            session()->flash('error', 'Não foi possível gerar sugestões.');
        }
    }

    public function adicionarSugerido($categoria, $item)
    {
        if (!$this->peiAtivo) return;

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
            'Ameaça' => 'ameacas'
        ];
        $key = $map[$categoria] ?? null;

        if ($key && isset($this->aiSuggestion[$key])) {
            $this->aiSuggestion[$key] = array_filter($this->aiSuggestion[$key], fn($i) => $item !== $i);
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

        if (!$this->peiAtivo) {
            $this->peiAtivo = PEI::ativos()->first();
        }
    }

    public function atualizarOrganizacao($id)
    {
        $this->organizacaoId = $id;
        $this->organizacaoNome = $id ? \App\Models\Organization::find($id)?->nom_organizacao : null;
        $this->carregarDados();
    }

    public function carregarDados()
    {
        if (!$this->peiAtivo) return;

        $query = AnaliseAmbiental::swot()
            ->where('cod_pei', $this->peiAtivo->cod_pei)
            ->ordenado();

        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        }

        $itens = $query->get();

        $this->forcas = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_FORCA)->values()->toArray();
        $this->fraquezas = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_FRAQUEZA)->values()->toArray();
        $this->oportunidades = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_OPORTUNIDADE)->values()->toArray();
        $this->ameacas = $itens->where('dsc_categoria', AnaliseAmbiental::SWOT_AMEACA)->values()->toArray();
    }

    public function toggleModoVisualizacao()
    {
        $this->modoVisualizacao = !$this->modoVisualizacao;
    }

    public function create($categoria)
    {
        $this->resetForm();
        $this->dsc_categoria = $categoria;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $item = AnaliseAmbiental::findOrFail($id);
        $this->itemId       = $id;
        $this->dsc_categoria = $item->dsc_categoria;
        $this->dsc_item      = $item->dsc_item;
        $this->num_impacto   = $item->num_impacto;
        $this->num_gravidade = $item->num_gravidade ?? 3;
        $this->num_urgencia  = $item->num_urgencia ?? 3;
        $this->num_tendencia = $item->num_tendencia ?? 3;
        $this->txt_observacao = $item->txt_observacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'dsc_item'      => 'required|string|max:500',
            'num_impacto'   => 'required|integer|min:1|max:5',
            'num_gravidade' => 'required|integer|min:1|max:5',
            'num_urgencia'  => 'required|integer|min:1|max:5',
            'num_tendencia' => 'required|integer|min:1|max:5',
            'txt_observacao'=> 'nullable|string|max:1000',
        ]);

        $data = [
            'cod_pei'          => $this->peiAtivo->cod_pei,
            'cod_organizacao'  => $this->organizacaoId,
            'dsc_tipo_analise' => AnaliseAmbiental::TIPO_SWOT,
            'dsc_categoria'    => $this->dsc_categoria,
            'dsc_item'         => $this->dsc_item,
            'num_impacto'      => $this->num_impacto,
            'num_gravidade'    => $this->num_gravidade,
            'num_urgencia'     => $this->num_urgencia,
            'num_tendencia'    => $this->num_tendencia,
            'txt_observacao'   => $this->txt_observacao,
        ];

        if ($this->itemId) {
            AnaliseAmbiental::findOrFail($this->itemId)->update($data);
            $message = 'Item atualizado com sucesso!';
        } else {
            AnaliseAmbiental::create($data);
            $message = 'Item adicionado com sucesso!';
        }

        $this->showModal = false;
        $this->carregarDados();
        session()->flash('status', $message);
    }

    // ── Partes Interessadas ──────────────────────────────────────────────────

    public function novaParte(): void
    {
        $this->parteEditId = null;
        $this->formParte   = ['nom_parte' => '', 'dsc_tipo' => 'Externo', 'num_interesse' => 3, 'num_influencia' => 3, 'txt_estrategia_engajamento' => ''];
        $this->showModalParte = true;
    }

    public function editarParte(string $id): void
    {
        $p = ParteInteressada::findOrFail($id);
        $this->parteEditId = $id;
        $this->formParte   = [
            'nom_parte'                  => $p->nom_parte,
            'dsc_tipo'                   => $p->dsc_tipo,
            'num_interesse'              => $p->num_interesse,
            'num_influencia'             => $p->num_influencia,
            'txt_estrategia_engajamento' => $p->txt_estrategia_engajamento ?? '',
        ];
        $this->showModalParte = true;
    }

    public function salvarParte(): void
    {
        $this->validate([
            'formParte.nom_parte'     => 'required|string|max:150',
            'formParte.num_interesse' => 'required|integer|min:1|max:5',
            'formParte.num_influencia'=> 'required|integer|min:1|max:5',
        ], ['formParte.nom_parte.required' => 'Informe o nome da parte interessada.']);

        $data = array_merge($this->formParte, ['cod_pei' => $this->peiAtivo->cod_pei]);

        $this->parteEditId
            ? ParteInteressada::findOrFail($this->parteEditId)->update($data)
            : ParteInteressada::create($data);

        $this->showModalParte = false;
        $this->parteEditId    = null;
        $this->dispatch('notify', message: 'Parte interessada salva.', style: 'success');
    }

    public function excluirParte(string $id): void
    {
        ParteInteressada::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Parte interessada removida.', style: 'warning');
    }

    // ── Cenários Prospectivos ─────────────────────────────────────────────────

    public function novoCenario(): void
    {
        $this->cenarioEditId = null;
        $this->formCenario = ['nom_cenario' => '', 'dsc_tipo' => 'Tendencial', 'dsc_descricao' => '', 'txt_implicacoes' => '', 'txt_resposta_estrategica' => '', 'num_probabilidade' => 3, 'num_impacto' => 3];
        $this->showModalCenario = true;
    }

    public function editarCenario(string $id): void
    {
        $c = CenarioProspectivo::findOrFail($id);
        $this->cenarioEditId = $id;
        $this->formCenario = [
            'nom_cenario'              => $c->nom_cenario,
            'dsc_tipo'                 => $c->dsc_tipo,
            'dsc_descricao'            => $c->dsc_descricao ?? '',
            'txt_implicacoes'          => $c->txt_implicacoes ?? '',
            'txt_resposta_estrategica' => $c->txt_resposta_estrategica ?? '',
            'num_probabilidade'        => $c->num_probabilidade,
            'num_impacto'              => $c->num_impacto,
        ];
        $this->showModalCenario = true;
    }

    public function salvarCenario(): void
    {
        $this->validate([
            'formCenario.nom_cenario' => 'required|string|max:150',
            'formCenario.dsc_tipo'    => 'required|in:Otimista,Tendencial,Pessimista',
            'formCenario.num_probabilidade' => 'required|integer|min:1|max:5',
            'formCenario.num_impacto'       => 'required|integer|min:1|max:5',
        ], ['formCenario.nom_cenario.required' => 'Informe o nome do cenário.']);

        $data = array_merge($this->formCenario, [
            'cod_pei'         => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
        ]);

        $this->cenarioEditId
            ? CenarioProspectivo::findOrFail($this->cenarioEditId)->update($data)
            : CenarioProspectivo::create($data);

        $this->showModalCenario = false;
        $this->cenarioEditId    = null;
        $this->dispatch('notify', message: 'Cenário prospectivo salvo.', style: 'success');
    }

    public function excluirCenario(string $id): void
    {
        CenarioProspectivo::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Cenário removido.', style: 'warning');
    }

    public function delete($id)
    {
        AnaliseAmbiental::findOrFail($id)->delete();
        $this->carregarDados();
        session()->flash('status', 'Item removido com sucesso!');
    }

    public function resetForm()
    {
        $this->itemId        = null;
        $this->dsc_categoria = '';
        $this->dsc_item      = '';
        $this->num_impacto   = 3;
        $this->num_gravidade = 3;
        $this->num_urgencia  = 3;
        $this->num_tendencia = 3;
        $this->txt_observacao = '';
    }

    public function render()
    {
        $partes = $this->peiAtivo
            ? ParteInteressada::where('cod_pei', $this->peiAtivo->cod_pei)->orderBy('num_influencia', 'desc')->orderBy('num_interesse', 'desc')->get()
            : collect();

        $cenarios = $this->peiAtivo
            ? CenarioProspectivo::where('cod_pei', $this->peiAtivo->cod_pei)->orderBy('num_ordem')->orderBy('dsc_tipo')->get()
            : collect();

        return view('livewire.p-e-i.analise-s-w-o-t', [
            'categorias'   => AnaliseAmbiental::categoriasSWOT(),
            'partes'       => $partes,
            'tiposParte'   => ParteInteressada::TIPOS,
            'cenarios'     => $cenarios,
            'tiposCenario' => CenarioProspectivo::TIPOS,
        ]);
    }
}
