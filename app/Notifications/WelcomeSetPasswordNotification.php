<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeSetPasswordNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Bem-vindo ao Sistema de Planejamento Estrategico')
            ->greeting('Bem-vindo, '.$notifiable->name)
            ->line('Seu cadastro foi criado no Sistema de Planejamento Estrategico.')
            ->line('Para concluir o acesso, cadastre sua senha pelo link abaixo. O link tem validade limitada por seguranca.')
            ->action('Cadastrar minha senha', $url)
            ->line('Se voce nao esperava este convite, ignore esta mensagem.');
    }
}
