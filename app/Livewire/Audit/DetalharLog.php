<?php

namespace App\Livewire\Audit;

use App\Support\AuditoriaLegivel;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use OwenIt\Auditing\Models\Audit;

#[Layout('layouts.app')]
class DetalharLog extends Component
{
    #[Locked]
    public int $logId;

    /**
     * Onde abrir o registro auditado, quando ele tem tela própria.
     *
     * @var array<string, string>
     */
    private const TELAS = [
        'Objetivo' => 'objetivos.detalhes',
        'PlanoDeAcao' => 'planos.detalhes',
        'Indicador' => 'indicadores.detalhes',
        'Perspectiva' => 'pei.perspectivas.detalhes',
        'Valor' => 'pei.valores.detalhes',
        'Organization' => 'organizacoes.detalhes',
        'User' => 'usuarios.detalhes',
        'GrauSatisfacao' => 'graus-satisfacao.detalhes',
        'PEI' => 'pei.detalhes',
    ];

    public function mount(int $id): void
    {
        $this->authorize('modulo.acessar', 'auditoria');

        $this->logId = Audit::query()->findOrFail($id)->getKey();
    }

    public function render()
    {
        // A trilha é só do Super Admin: reautoriza a cada requisição, não só na entrada.
        $this->authorize('modulo.acessar', 'auditoria');

        $log = Audit::with('user')->findOrFail($this->logId);
        $existe = AuditoriaLegivel::registroExiste($log);
        $rota = self::TELAS[class_basename((string) $log->auditable_type)] ?? null;

        return view('livewire.audit.detalhar-log', [
            'log' => $log,
            'nomeRegistro' => AuditoriaLegivel::nomeDoRegistro($log),
            'mudancas' => AuditoriaLegivel::mudancas($log),
            'linkRegistro' => ($existe && $rota && Route::has($rota)) ? route($rota, $log->auditable_id) : null,
            'registroExiste' => $existe,
            'historico' => Audit::with('user')
                ->where('auditable_type', $log->auditable_type)
                ->where('auditable_id', $log->auditable_id)
                ->latest('id')
                ->limit(12)
                ->get(),
        ]);
    }
}
