<?php

namespace App\Notifications\Adopcion;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NuevaSolicitudAdopcionAdmin extends Notification
{
    use Queueable;

    protected $solicitud;
    protected $mascota;
    protected $solicitante;

    public function __construct($solicitud, $mascota, $solicitante)
    {
        $this->solicitud = $solicitud;
        $this->mascota = $mascota;
        $this->solicitante = $solicitante;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'titulo' => 'Nueva solicitud en el sistema',
            'mensaje' => "{$this->solicitante->nombre} solicitó adoptar a {$this->mascota->nombre_mascota}",
            'solicitud_id' => $this->solicitud->id,
            'mascota_id' => $this->mascota->id,
            'mascota_nombre' => $this->mascota->nombre_mascota,
            'solicitante_nombre' => $this->solicitante->nombre,
            'url' => "/admin/solicitudes/{$this->solicitud->id}",
            'icono' => 'fa-clipboard-list',
            'color' => '#ff6b9d',
            'tipo' => 'nueva_solicitud_admin',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Nueva solicitud de adopción - {$this->mascota->nombre_mascota}")
            ->greeting("Hola Admin {$notifiable->nombre}!")
            ->line("{$this->solicitante->nombre} solicitó adoptar a {$this->mascota->nombre_mascota}.")
            ->line("")
            ->line("Detalles:")
            ->line("Email: {$this->solicitante->email}")
            ->line("Teléfono: {$this->solicitante->telefono}")
            ->line("Mascota: {$this->mascota->nombre_mascota} ({$this->mascota->especie})")
            ->line("")
            ->action('Revisar solicitud', url("/admin/solicitudes/{$this->solicitud->id}"))
            ->line("Esta solicitud requiere tu atención.");
    }
}
