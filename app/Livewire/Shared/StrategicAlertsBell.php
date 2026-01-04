<?php

namespace App\Livewire\Shared;

use App\Models\StrategicAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class StrategicAlertsBell extends Component
{
    public $unreadCount = 0;
    
    protected $listeners = [
        'mentor-notification' => 'refreshCount', // Listen for new notifications
        'organizacaoSelecionada' => 'refreshCount'
    ];

    public function mount()
    {
        $this->refreshCount();
    }

    public function refreshCount()
    {
        if (!Auth::check()) return;

        $orgId = Session::get('organizacao_selecionada_id');
        
        $this->unreadCount = StrategicAlert::where('user_id', Auth::id())
            ->where(function($q) use ($orgId) {
                if ($orgId) $q->where('cod_organizacao', $orgId)->orWhereNull('cod_organizacao');
            })
            ->unread()
            ->count();
    }

    public function markAllAsRead()
    {
        StrategicAlert::where('user_id', Auth::id())->unread()->update(['read_at' => now()]);
        $this->refreshCount();
    }

    public function getRecentAlerts()
    {
        $orgId = Session::get('organizacao_selecionada_id');
        
        return StrategicAlert::where('user_id', Auth::id())
            ->where(function($q) use ($orgId) {
                if ($orgId) $q->where('cod_organizacao', $orgId)->orWhereNull('cod_organizacao');
            })
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.shared.strategic-alerts-bell', [
            'alerts' => $this->getRecentAlerts()
        ]);
    }
}
