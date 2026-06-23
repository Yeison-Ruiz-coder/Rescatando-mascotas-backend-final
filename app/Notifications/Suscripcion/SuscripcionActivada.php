<?php

namespace App\Notifications\Suscripcion;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SuscripcionActivada extends Notification
{
    use Queueable;

    protected $suscripcion;
    protected $mascota;

    public function __construct($suscripcion, $mascota)
    {
        $this->suscripcion = $suscripcion;
        $this->mascota = $mascota;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'titulo' => 'Suscripción activada',
            'mensaje' => "¡Ahora eres padrino/madrina de {$this->mascota->nombre_mascota}!",
            'suscripcion_id' => $this->suscripcion->id,
            'mascota_id' => $this->mascota->id,
            'mascota_nombre' => $this->mascota->nombre_mascota,
            'monto' => $this->suscripcion->monto_mensual,
            'url' => "/user/mis-suscripciones",
            'icono' => 'fa-star',
            'color' => '#f59e0b',
            'tipo' => 'suscripcion_activada',
        ];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("¡Suscripción activada para {$this->mascota->nombre_mascota}!")
            ->greeting("Hola {$notifiable->nombre}!")
            ->line("¡Excelentes noticias! Tu suscripción ha sido activada exitosamente.")
            ->line("Ahora eres el padrino/madrina de {$this->mascota->nombre_mascota}.")
            ->line("Monto mensual: $" . number_format($this->suscripcion->monto_mensual, 0, ',', '.'))
            ->action('Ver mis suscripciones', url("/user/mis-suscripciones"))
            ->line("¡Gracias por tu apoyo! Estás haciendo una gran diferencia.");
    }
}
