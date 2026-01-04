<?php

namespace App\Services;

use App\Models\StrategicAlert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class NotificationService
{
    /**
     * Log a strategic alert and dispatch a Livewire notification.
     */
    public static function sendMentorAlert(string $title, string $message, string $icon = 'bi-info-circle', string $type = 'success')
    {
        $user = Auth::user();
        if (!$user) return;

        // 1. Save to Database
        StrategicAlert::create([
            'user_id' => $user->id,
            'cod_organizacao' => Session::get('organizacao_selecionada_id'),
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'type' => $type
        ]);

        // 2. Return data for Livewire Dispatch (calling component must dispatch it)
        return [
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'type' => $type
        ];
    }
}
