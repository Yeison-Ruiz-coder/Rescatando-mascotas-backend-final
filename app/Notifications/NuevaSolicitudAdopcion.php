<?php

namespace App\Notifications\Adopcion;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NuevaSolicitudAdopcion extends Notification
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
            'titulo' => 'Nueva solicitud de adopción',
            'mensaje' => "{$this->solicitante->nombre} quiere adoptar a {$this->mascota->nombre_mascota}",
            'solicitud_id' => $this->solicitud->id,
            'mascota_id' => $this->mascota->id,
            'mascota_nombre' => $this->mascota->nombre_mascota,
            'solicitante_nombre' => $this->solicitante->nombre,
            'solicitante_email' => $this->solicitante->email,
            'solicitante_telefono' => $this->solicitante->telefono,
            'url' => "/dashboard/solicitudes/{$this->solicitud->id}",
            'icono' => 'fa-paw',
            'color' => '#667eea',
            'tipo' => 'nueva_solicitud_adopcion',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Nueva solicitud de adopción para {$this->mascota->nombre_mascota}")
            ->greeting("Hola {$notifiable->nombre}!")
            ->line("{$this->solicitante->nombre} ha solicitado adoptar a {$this->mascota->nombre_mascota}.")
            ->line("")
            ->line("Detalles del solicitante:")
            ->line("Email: {$this->solicitante->email}")
            ->line("Teléfono: {$this->solicitante->telefono}")
            ->line("")
            ->action('Ver solicitud', url("/dashboard/solicitudes/{$this->solicitud->id}"))
            ->line("Por favor revisa la solicitud y responde lo antes posible.");
    }
}
