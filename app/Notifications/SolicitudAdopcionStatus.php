<?php

namespace App\Notifications\Adopcion;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SolicitudAdopcionStatus extends Notification
{
    use Queueable;

    protected $solicitud;
    protected $mascota;
    protected $estado;
    protected $razon;

    public function __construct($solicitud, $mascota, $estado, $razon = null)
    {
        $this->solicitud = $solicitud;
        $this->mascota = $mascota;
        $this->estado = $estado;
        $this->razon = $razon;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        $titulo = $this->estado === 'aprobada' ? 'Solicitud Aprobada' : 'Solicitud Rechazada';
        $color = $this->estado === 'aprobada' ? '#10b981' : '#ef4444';

        return [
            'titulo' => $titulo,
            'mensaje' => $this->estado === 'aprobada'
                ? "¡Felicidades! Tu solicitud para adoptar a {$this->mascota->nombre_mascota} fue aprobada."
                : "Tu solicitud para adoptar a {$this->mascota->nombre_mascota} fue rechazada.",
            'solicitud_id' => $this->solicitud->id,
            'mascota_id' => $this->mascota->id,
            'mascota_nombre' => $this->mascota->nombre_mascota,
            'estado' => $this->estado,
            'razon' => $this->razon,
            'url' => "/user/mis-solicitudes",
            'icono' => $this->estado === 'aprobada' ? 'fa-check-circle' : 'fa-times-circle',
            'color' => $color,
            'tipo' => 'solicitud_status',
        ];
    }

    public function toMail($notifiable)
    {
        $subject = $this->estado === 'aprobada'
            ? "¡Felicidades! Tu solicitud fue aprobada"
            : "Actualización de tu solicitud de adopción";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting("Hola {$notifiable->nombre}!");

        if ($this->estado === 'aprobada') {
            $mail->line("¡Excelentes noticias! Tu solicitud para adoptar a {$this->mascota->nombre_mascota} ha sido APROBADA.")
                 ->line("Un coordinador se pondrá en contacto contigo para los siguientes pasos.")
                 ->line("Preparate para recibir a tu nuevo compañero.")
                 ->action('Ver mis solicitudes', url("/user/mis-solicitudes"));
        } else {
            $mail->line("Lamentamos informarte que tu solicitud para adoptar a {$this->mascota->nombre_mascota} ha sido RECHAZADA.")
                 ->line("Motivo: {$this->razon}")
                 ->line("No te desanimes, hay muchas otras mascotas esperando un hogar.")
                 ->action('Ver otras mascotas', url("/mascotas"));
        }

        return $mail;
    }
}
