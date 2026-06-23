<?php

namespace App\Notifications\Rescate;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NuevoRescateReportado extends Notification
{
    use Queueable;

    protected $rescate;
    protected $reportante;

    public function __construct($rescate, $reportante)
    {
        $this->rescate = $rescate;
        $this->reportante = $reportante;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'titulo' => 'Nuevo rescate reportado',
            'mensaje' => "Se ha reportado un rescate en {$this->rescate->lugar_rescate}",
            'rescate_id' => $this->rescate->id,
            'lugar' => $this->rescate->lugar_rescate,
            'prioridad' => $this->rescate->prioridad,
            'url' => "/rescates/{$this->rescate->id}",
            'icono' => 'fa-exclamation-triangle',
            'color' => '#ef4444',
            'tipo' => 'nuevo_rescate',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Nuevo rescate reportado en {$this->rescate->lugar_rescate}")
            ->greeting("Hola {$notifiable->nombre}!")
            ->line("Se ha reportado un nuevo rescate que necesita atención.")
            ->line("Ubicación: {$this->rescate->lugar_rescate}")
            ->line("Prioridad: " . strtoupper($this->rescate->prioridad))
            ->action('Ver rescate', url("/rescates/{$this->rescate->id}"));
    }
}
