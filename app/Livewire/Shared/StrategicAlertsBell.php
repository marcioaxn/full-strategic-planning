<?php

namespace App\Livewire\Shared;

use App\Models\StrategicAlert;
use App\Support\Auditoria\FeedDeAtividade;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * O sino do topo: duas abas.
 *  - Alertas: os alertas estratégicos do usuário no escopo atual;
 *  - Atividade: o que outras pessoas fizeram (FeedDeAtividade), com contador
 *    do que é novo desde a última vez que a aba foi aberta.
 *
 * O feed só é montado com a aba aberta: no carregamento de cada página o sino
 * faz apenas as duas contagens.
 */
class StrategicAlertsBell extends Component
{
    public $unreadCount = 0;

    public int $atividadeNova = 0;

    #[Locked]
    public string $aba = 'alertas';

    /** Até quando o usuário tinha visto a atividade antes de abrir a aba: destaca o que era novo. */
    #[Locked]
    public ?string $vistoAnterior = null;

    protected $listeners = [
        'mentor-notification' => 'refreshCount', // Listen for new notifications
        'organizacaoSelecionada' => 'refreshCount',
    ];

    public function mount()
    {
        $this->refreshCount();
    }

    public function refreshCount()
    {
        if (! Auth::check()) {
            return;
        }

        $this->unreadCount = $this->alertasDoEscopo()->unread()->count();
        $this->atividadeNova = FeedDeAtividade::quantidadeNova(Auth::user());
    }

    /**
     * Chamado pelo wire:poll.60s.visible da view: só as duas contagens
     * (COUNT indexado, no escopo da unidade) — nunca a lista nem os nomes dos
     * registros. Nunca re-renderiza: os contadores do sino leem $wire pelo
     * Alpine e se atualizam sozinhos; com a lista aberta, ela não é remontada
     * nem perde a rolagem. A lista só se monta ao abrir a aba.
     */
    public function atualizarContadores(): void
    {
        $this->skipRender();

        if (! Auth::check()) {
            return;
        }

        $this->refreshCount();
    }

    /**
     * Marca como lidos só os alertas que o sino mostra no escopo atual (a
     * unidade selecionada e os gerais). 🔴 Usava só o user_id: estando na
     * unidade A, os alertas da B — que o usuário nunca viu — viravam "lidos".
     * Teste: MarcarNotificacoesLidasTest.
     */
    public function markAllAsRead()
    {
        if (! Auth::check()) {
            return;
        }

        $this->alertasDoEscopo()->unread()->update(['read_at' => now()]);
        $this->refreshCount();
    }

    public function abrirAlertas(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->aba = 'alertas';
    }

    /** Abrir a aba conta como ver a atividade: o contador zera. */
    public function abrirAtividade(): void
    {
        if (! Auth::check()) {
            return;
        }

        $this->aba = 'atividade';
        $this->vistoAnterior = FeedDeAtividade::vistoAte(Auth::user())->toDateTimeString();
        FeedDeAtividade::marcarVisto(Auth::user());
        $this->refreshCount();
    }

    public function marcarAtividadeComoLida(): void
    {
        if (! Auth::check()) {
            return;
        }

        FeedDeAtividade::marcarVisto(Auth::user());
        $this->vistoAnterior = now()->toDateTimeString();
        $this->refreshCount();
    }

    public function getRecentAlerts()
    {
        return $this->alertasDoEscopo()
            ->latest()
            ->take(5)
            ->get();
    }

    /** Os alertas do usuário no escopo que o sino exibe: a contagem, a lista e o "marcar todas" usam o mesmo. */
    private function alertasDoEscopo(): Builder
    {
        $orgId = Auth::user()?->organizacaoSelecionadaId();

        return StrategicAlert::query()
            ->where('user_id', Auth::id())
            ->where(function ($q) use ($orgId) {
                if ($orgId) {
                    $q->where('cod_organizacao', $orgId)->orWhereNull('cod_organizacao');
                }
            });
    }

    public function render()
    {
        $user = Auth::user();
        $naAtividade = $user && $this->aba === 'atividade';

        return view('livewire.shared.strategic-alerts-bell', [
            'alerts' => $user && ! $naAtividade ? $this->getRecentAlerts() : collect(),
            'atividades' => $naAtividade
                ? FeedDeAtividade::itens($user, $this->vistoAnterior ? Carbon::parse($this->vistoAnterior) : null)
                : [],
            'linkAuditoria' => $naAtividade && $user->can('modulo.acessar', 'auditoria') ? route('audit.index') : null,
        ]);
    }
}
