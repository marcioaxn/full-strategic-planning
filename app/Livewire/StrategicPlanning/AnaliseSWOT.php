<?php

namespace App\Livewire\StrategicPlanning;

use App\Models\StrategicPlanning\AnaliseAmbiental;
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

    // Modal
    public bool $showModal = false;
    public $itemId;
    public $dsc_categoria;
    public $dsc_item = '';
    public $num_impacto = 3;
    public $txt_observacao = '';

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
        } catch (\Exception $e) {
            \Log::error('Erro IA SWOT: ' . $e->getMessage());
            $this->aiSuggestion = null;
            session()->flash('error', 'Não foi possível gerar sugestões.');
        }
    }

    public function adicionarSugerido($categoria, $item)
    {
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
        $key = $map[$categoria];
        
        if (isset($this->aiSuggestion[$key])) {
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
        $this->itemId = $id;
        $this->dsc_categoria = $item->dsc_categoria;
        $this->dsc_item = $item->dsc_item;
        $this->num_impacto = $item->num_impacto;
        $this->txt_observacao = $item->txt_observacao;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'dsc_item' => 'required|string|max:500',
            'num_impacto' => 'required|integer|min:1|max:5',
            'txt_observacao' => 'nullable|string|max:1000',
        ]);

        $data = [
            'cod_pei' => $this->peiAtivo->cod_pei,
            'cod_organizacao' => $this->organizacaoId,
            'dsc_tipo_analise' => AnaliseAmbiental::TIPO_SWOT,
            'dsc_categoria' => $this->dsc_categoria,
            'dsc_item' => $this->dsc_item,
            'num_impacto' => $this->num_impacto,
            'txt_observacao' => $this->txt_observacao,
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

    public function delete($id)
    {
        AnaliseAmbiental::findOrFail($id)->delete();
        $this->carregarDados();
        session()->flash('status', 'Item removido com sucesso!');
    }

    public function resetForm()
    {
        $this->itemId = null;
        $this->dsc_categoria = '';
        $this->dsc_item = '';
        $this->num_impacto = 3;
        $this->txt_observacao = '';
    }

    public function render()
    {
        return view('livewire.p-e-i.analise-s-w-o-t', [
            'categorias' => AnaliseAmbiental::categoriasSWOT(),
        ]);
    }
}
