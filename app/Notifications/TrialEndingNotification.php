<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Tenant;

class TrialEndingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Tenant $tenant,
        public int $daysLeft
    ) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Trial a terminar')
            ->line("O trial do tenant {$this->tenant->name} termina em {$this->daysLeft} dias.")
            ->action('Escolher um plano', url('/billing'))
            ->line('Evite interrupções no serviço.');
    }
}
