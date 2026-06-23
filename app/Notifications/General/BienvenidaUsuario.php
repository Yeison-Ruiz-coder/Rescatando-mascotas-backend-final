<?php

namespace App\Notifications\General;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class BienvenidaUsuario extends Notification
{
    use Queueable;

    protected $user;
    protected $password;

    public function __construct($user, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'titulo' => '¡Bienvenido a Huellas Felices!',
            'mensaje' => "Hola {$this->user->nombre}, gracias por unirte a nuestra comunidad.",
            'url' => "/",
            'icono' => 'fa-hand-peace',
            'color' => '#667eea',
            'tipo' => 'bienvenida',
        ];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject("¡Bienvenido a Huellas Felices!")
            ->greeting("Hola {$this->user->nombre}!")
            ->line("¡Gracias por unirte a nuestra comunidad de amantes de los animales!")
            ->line("En Huellas Felices puedes:")
            ->line("- Adoptar mascotas que buscan un hogar")
            ->line("- Apadrinar y ayudar a animales necesitados")
            ->line("- Reportar rescates y salvar vidas")
            ->line("- Conectar con fundaciones y veterinarias");

        if ($this->password) {
            $mail->line("Tu contraseña temporal es: **{$this->password}**")
                 ->line("Por favor cambia tu contraseña después de iniciar sesión.");
        }

        return $mail->action('Comenzar', url("/"));
    }
}
