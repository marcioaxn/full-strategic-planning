<?php

namespace App\Livewire\Shared;

use App\Models\StrategicAlert;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StrategicAlertsBell extends Component
{
    public $unreadCount = 0;

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
        return view('livewire.shared.strategic-alerts-bell', [
            'alerts' => $this->getRecentAlerts(),
        ]);
    }
}
