<?php

namespace App\Livewire\RiskManagement;

use App\Models\RiskManagement\Risco;
use App\Models\StrategicPlanning\PEI;
use App\Models\StrategicPlanning\Objetivo;
use App\Models\Organization;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Session;

#[Layout('layouts.app')]
class ListarRiscos extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $filtroNivel = '';
    public $filtroCategoria = '';
    public $organizacaoId;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public $riscoId;
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

        $aiService = \App\Services\AI\AiServiceFactory::make();
        if (!$aiService) return;

        $objetivosList = $this->objetivos->pluck('nom_objetivo')->take(5)->implode(', ');

        $this->aiSuggestion = 'Pensando...';
        
        $prompt = "Com base nos seguintes Objetivos Estratégicos da organização: '{$objetivosList}', sugira 3 riscos potenciais que podem impedir o alcance dessas metas. 
        Para cada risco informe: Título, Categoria (Estratégico, Operacional, Financeiro, Reputacional), Descrição curta e uma sugestão de Medida de Mitigação.
        Responda OBRIGATORIAMENTE em formato JSON puro, contendo um array de objetos com os campos 'titulo', 'categoria', 'descricao' e 'mitigacao'.";
        
        $response = $aiService->suggest($prompt);
        $decoded = json_decode(str_replace(['```json', '```'], '', $response), true);

        if (is_array($decoded)) {
            $this->aiSuggestion = $decoded;
        } else {
            $this->aiSuggestion = null;
            session()->flash('error', 'Falha ao processar sugestões de risco. Tente novamente.');
        }
    }

    public function aplicarSugestao($titulo, $categoria, $descricao)
    {
        $this->resetForm();
        $this->form['dsc_titulo'] = $titulo;
        $this->form['dsc_categoria'] = $categoria;
        $this->form['txt_descricao'] = $descricao;
        $this->form['cod_responsavel_monitoramento'] = \Illuminate\Support\Facades\Auth::id();
        
        $this->showModal = true;
        $this->aiSuggestion = '';
    }

    public function atualizarPEI($id)
    {
        $this->peiAtivo = PEI::find($id);
        $this->carregarListasAuxiliares();
        $this->resetPage();
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
        $this->resetPage();
        $this->carregarListasAuxiliares();
    }

    public function carregarListasAuxiliares()
    {
        if ($this->peiAtivo) {
            $this->objetivos = Objetivo::whereHas('perspectiva', function($query) {
                $query->where('cod_pei', $this->peiAtivo->cod_pei);
            })->orderBy('nom_objetivo')->get();
        }

        if ($this->organizacaoId) {
            $this->usuarios = User::whereHas('organizacoes', function($q) {
                $q->where('tab_organizacoes.cod_organizacao', $this->organizacaoId);
            })->orderBy('name')->get();
        }
    }

    public function updatingSearch() { $this->resetPage(); }

    public function create()
    {
        $this->authorize('create', Risco::class);
        $this->resetForm();
        if (!$this->organizacaoId) {
            $this->dispatch('notify', message: 'Selecione uma organização.', style: 'warning');
            return;
        }
        $this->showModal = true;
    }

    public function edit($id)
    {
        $risco = Risco::with('objetivos')->findOrFail($id);
        $this->authorize('update', $risco);

        $this->riscoId = $id;
        $this->form = [
            'dsc_titulo' => $risco->dsc_titulo,
            'txt_descricao' => $risco->txt_descricao,
            'dsc_categoria' => $risco->dsc_categoria,
            'num_probabilidade' => $risco->num_probabilidade,
            'num_impacto' => $risco->num_impacto,
            'txt_causas' => $risco->txt_causas,
            'txt_consequencias' => $risco->txt_consequencias,
            'cod_responsavel_monitoramento' => $risco->cod_responsavel_monitoramento,
            'dsc_status' => $risco->dsc_status,
            'objetivos_vinculados' => $risco->objetivos->pluck('cod_objetivo')->toArray(),
        ];

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'form.dsc_titulo' => 'required|string|max:255',
            'form.dsc_categoria' => 'required',
            'form.num_probabilidade' => 'required|integer|min:1|max:5',
            'form.num_impacto' => 'required|integer|min:1|max:5',
            'form.cod_responsavel_monitoramento' => 'required|exists:users,id',
        ]);

        $data = $this->form;
        unset($data['objetivos_vinculados']);

        if ($this->riscoId) {
            $risco = Risco::findOrFail($this->riscoId);
            $this->authorize('update', $risco);
            $risco->update($data);
        } else {
            $this->authorize('create', Risco::class);
            $data['cod_pei'] = $this->peiAtivo->cod_pei;
            $data['cod_organizacao'] = $this->organizacaoId;
            $risco = Risco::create($data);
        }

        // Sincronizar objetivos
        $risco->objetivos()->sync($this->form['objetivos_vinculados']);

        $alert = \App\Services\NotificationService::sendMentorAlert(
            $this->riscoId ? 'Risco Atualizado!' : 'Risco Identificado!',
            'A matriz de riscos foi atualizada com sucesso.',
            'bi-shield-plus'
        );

        $this->dispatch('mentor-notification', ...$alert);

        $this->showModal = false;
    }

    public function confirmDelete($id)
    {
        $this->riscoId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $risco = Risco::findOrFail($this->riscoId);
        $this->authorize('delete', $risco);
        $risco->delete();
        $this->showDeleteModal = false;
        
        $alert = \App\Services\NotificationService::sendMentorAlert(
            'Risco Removido',
            'O item foi excluído do mapeamento de riscos.',
            'bi-trash',
            'warning'
        );

        $this->dispatch('mentor-notification', ...$alert);
    }

    public function resetForm()
    {
        $this->riscoId = null;
        $this->form = [
            'dsc_titulo' => '', 'txt_descricao' => '', 'dsc_categoria' => 'Operacional',
            'num_probabilidade' => 3, 'num_impacto' => 3, 'txt_causas' => '',
            'txt_consequencias' => '', 'cod_responsavel_monitoramento' => '',
            'dsc_status' => 'Identificado', 'objetivos_vinculados' => [],
        ];
    }

    public function render()
    {
        $query = Risco::query()->with(['responsavel', 'objetivos']);

        if ($this->organizacaoId) {
            $query->where('cod_organizacao', $this->organizacaoId);
        }

        if ($this->search) {
            $query->where('dsc_titulo', 'ilike', '%' . $this->search . '%');
        }

        if ($this->filtroCategoria) {
            $query->where('dsc_categoria', $this->filtroCategoria);
        }

        if ($this->filtroNivel) {
            if ($this->filtroNivel === 'Critico') $query->criticos();
            elseif ($this->filtroNivel === 'Baixo') $query->where('num_nivel_risco', '<', 5);
            // ... outros filtros
        }

        return view('livewire.risco.listar-riscos', [
            'riscos' => $query->orderBy('num_nivel_risco', 'desc')->paginate(10)
        ]);
    }
}
