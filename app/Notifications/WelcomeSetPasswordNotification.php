<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        // Validade do link, em minutos, conforme a configuração de reset de senha.
        $broker = config('auth.defaults.passwords', 'users');
        $minutos = (int) config("auth.passwords.{$broker}.expire", 60);

        return (new MailMessage)
            ->subject('Bem-vindo ao Sistema de Planejamento Estratégico Integrado (PEI)')
            ->markdown('emails.welcome-set-password', [
                'nome'    => $notifiable->name,
                'url'     => $url,
                'minutos' => $minutos,
            ]);
    }
}
