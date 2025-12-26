<?php

namespace App\Livewire\Audit;

use OwenIt\Auditing\Models\Audit;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

#[Layout('layouts.app')]
class ListarLogs extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $search = '';
    public $filtroEvento = '';
    public $filtroUsuario = '';
    public $filtroModel = '';
    public $dataInicio;
    public $dataFim;

    public bool $showModal = false;
    public $auditSelecionada;

    protected $queryString = [
        'filtroEvento' => ['except' => ''],
        'filtroUsuario' => ['except' => ''],
        'filtroModel' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }
        $this->dataInicio = now()->subDays(7)->format('Y-m-d');
        $this->dataFim = now()->format('Y-m-d');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['search', 'filtroEvento', 'filtroUsuario', 'filtroModel', 'dataInicio', 'dataFim'])) {
            $this->resetPage();
        }
    }

    public function verDetalhes($id)
    {
        $this->auditSelecionada = Audit::with('user')->findOrFail($id);
        $this->showModal = true;
    }

    public function render()
    {
        $query = Audit::with('user')->latest();

        if ($this->filtroEvento) {
            $query->where('event', $this->filtroEvento);
        }

        if ($this->filtroUsuario) {
            $query->where('user_id', $this->filtroUsuario);
        }

        if ($this->filtroModel) {
            $query->where('auditable_type', 'like', '%' . $this->filtroModel . '%');
        }

        if ($this->dataInicio) {
            $query->whereDate('created_at', '>=', $this->dataInicio);
        }

        if ($this->dataFim) {
            $query->whereDate('created_at', '<=', $this->dataFim);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('ip_address', 'like', $this->search . '%')
                  ->orWhere('tags', 'like', '%' . $this->search . '%');
            });
        }

        // Obter lista de models únicos que possuem auditoria para o filtro
        $modelsDisponiveis = Audit::select('auditable_type')->distinct()->get()->pluck('auditable_type');
        $usuariosComAudit = User::whereHas('audits')->orderBy('name')->get();

        return view('livewire.audit.listar-logs', [
            'logs' => $query->paginate(15),
            'models' => $modelsDisponiveis,
            'usuarios' => $usuariosComAudit
        ]);
    }
}