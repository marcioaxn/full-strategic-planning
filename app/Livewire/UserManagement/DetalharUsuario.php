<?php

namespace App\Livewire\UserManagement;

use App\Models\ActionPlan\Entrega;
use App\Models\ActionPlan\PlanoDeAcao;
use App\Models\User;
use App\Support\RotuloAuditoria;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use OwenIt\Auditing\Models\Audit;

#[Layout('layouts.app')]
class DetalharUsuario extends Component
{
    use AuthorizesRequests;

    public $user;

    public $estatisticas = [];

    public $planosResponsavel = [];

    public $entregasResponsavel = []; // Placeholder, pois Entrega não tem user direto na estrutura atual conhecida

    public function mount($id)
    {
        $this->user = User::with(['organizacoes'])->findOrFail($id);
        $this->authorize('view', $this->user);

        // Buscar planos onde o usuário é Gestor Responsável
        // Usando a tabela pivô rel_users_tab_organizacoes_tab_perfil_acesso
        // PerfilAcesso::GESTOR_RESPONSAVEL (assumindo ID fixo ou buscando)

        // Como não tenho a classe PerfilAcesso fácil aqui para pegar constantes, vou assumir IDs ou fazer query genérica
        // Mas para simplificar neste momento, vou listar as organizações e deixar planos como TODO se a query for complexa demais sem os IDs.

        // Tentativa de buscar Planos via relação inversa se existisse.
        // Vou usar uma query manual na tabela pivot se conseguir, mas User tem o relacionamento perfisAcesso().

        // $this->user->perfisAcesso() retorna os perfis. O pivot tem cod_plano_de_acao.
        // Vamos pegar os planos através disso.

        $planosIds = DB::table('rel_users_tab_organizacoes_tab_perfil_acesso')
            ->where('user_id', $id)
            ->whereNotNull('cod_plano_de_acao')
            ->pluck('cod_plano_de_acao');

        $this->planosResponsavel = PlanoDeAcao::whereIn('cod_plano_de_acao', $planosIds)->get();

        $this->estatisticas = [
            'qtd_organizacoes' => $this->user->organizacoes->count(),
            'qtd_planos' => $this->planosResponsavel->count(),
            // Era o número fixo 0 ("implementar depois"): a tela afirmava que
            // ninguém concluiu entrega nenhuma.
            'entregas_concluidas' => Entrega::where('bln_status', 'Concluído')
                ->whereHas('responsaveis', fn ($q) => $q->where('users.id', $this->user->id))
                ->count(),
        ];

        // "Último acesso" era o texto fixo "--". Não há registro de login; a
        // tabela de sessões guarda a última atividade de cada sessão aberta.
        $ultima = DB::table(config('session.table', 'sessions'))->where('user_id', $this->user->id)->max('last_activity');
        $this->ultimaAtividade = $ultima ? Carbon::createFromTimestamp($ultima)->format('d/m/Y H:i') : null;

        // Histórico de ações do usuário: é dado de auditoria, então só para quem
        // acessa a Auditoria. A aba dizia "disponível em breve".
        $this->podeVerHistorico = auth()->user()->can('modulo.acessar', 'auditoria');
        if ($this->podeVerHistorico) {
            $rotulos = ['created' => 'Criou', 'updated' => 'Alterou', 'deleted' => 'Excluiu', 'restored' => 'Restaurou'];
            $this->historico = Audit::where('user_id', $this->user->id)
                ->latest()
                ->limit(30)
                ->get()
                ->map(fn ($a) => [
                    'quando' => $a->created_at?->format('d/m/Y H:i'),
                    'acao' => $rotulos[$a->event] ?? ucfirst((string) $a->event),
                    'registro' => RotuloAuditoria::registro($a->auditable_type),
                    'id' => $a->id,
                ])
                ->all();
        }
    }

    public bool $podeVerHistorico = false;

    public ?string $ultimaAtividade = null;

    /** @var list<array{quando: string|null, acao: string, registro: string, id: int|string}> */
    public array $historico = [];

    public function render()
    {
        return view('livewire.user-management.detalhar-usuario');
    }
}
