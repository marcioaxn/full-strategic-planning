<?php

namespace App\Livewire\Reports;

use App\Models\Reports\RelatorioAgendado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AgendarRelatorio extends Component
{
    public $tipoRelatorio;

    public $frequencia = 'mensal';

    public $filtros = [];

    public $dataInicio;

    public $showModal = false;

    protected $listeners = ['abrirAgendamento' => 'carregar'];

    public function carregar($tipo, $filtros)
    {
        $this->garantirAcesso(is_array($filtros) ? $filtros : []);

        $this->tipoRelatorio = $tipo;
        $this->filtros = $filtros;
        $this->dataInicio = now()->addDay()->format('Y-m-d H:i');
        $this->showModal = true;
    }

    public function salvar()
    {
        // Os filtros chegam do navegador e o agendamento roda depois, no cron, sem
        // usuário logado: a checagem de escopo tem de acontecer aqui, na gravação.
        $this->garantirAcesso(is_array($this->filtros) ? $this->filtros : []);

        $this->validate([
            'frequencia' => 'required|in:diario,semanal,mensal',
            'dataInicio' => 'required|date|after:now',
        ]);

        RelatorioAgendado::create([
            'user_id' => Auth::id(),
            'dsc_tipo_relatorio' => $this->tipoRelatorio,
            'dsc_frequencia' => $this->frequencia,
            'txt_filtros' => $this->filtros,
            'dte_proxima_execucao' => $this->dataInicio,
            'bln_ativo' => true,
        ]);

        $this->showModal = false;
        $this->dispatch('agendamentoCriado');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Relatório agendado com sucesso!',
        ]);
    }

    /**
     * Exige a capacidade de exportar relatórios e que a organização dos filtros
     * esteja no escopo do usuário. Sem organização, o relatório cobre todas as
     * unidades — só o Super Admin pode agendar esse recorte.
     *
     * @param  array<string, mixed>  $filtros
     */
    private function garantirAcesso(array $filtros): void
    {
        $user = Auth::user();
        $organizacaoId = $filtros['organizacao_id'] ?? null;
        $organizacaoId = is_string($organizacaoId) && $organizacaoId !== '' ? $organizacaoId : null;

        abort_unless(
            $user
                && Gate::forUser($user)->allows('modulo.exportar', 'relatorios')
                && ($organizacaoId ? $user->podeAcessarOrganizacao($organizacaoId) : $user->isSuperAdmin()),
            403,
            'Você não tem permissão para agendar relatórios desta organização.'
        );
    }

    public function render()
    {
        return view('livewire.reports.agendar-relatorio');
    }
}
