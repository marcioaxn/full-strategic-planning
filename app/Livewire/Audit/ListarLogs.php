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

    protected function getQuery()
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

        return $query;
    }

    public function exportar()
    {
        $query = $this->getQuery();
        
        $filename = "audit_logs_" . now()->format('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($query) {
            $file = fopen('php://output', 'w');
            // Adicionar BOM para Excel ler UTF-8 corretamente
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, ['ID', 'Data', 'Usuario', 'Evento', 'Modulo', 'ID Objeto', 'IP'], ';');

            $query->chunk(200, function($logs) use ($file) {
                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->id,
                        $log->created_at->format('d/m/Y H:i:s'),
                        $log->user->name ?? 'Sistema',
                        $log->event,
                        str_replace('App\\Models\\', '', $log->auditable_type),
                        $log->auditable_id,
                        $log->ip_address
                    ], ';');
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $query = $this->getQuery();

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