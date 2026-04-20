<?php
// app/Console/Commands/CheckAbandonedRescates.php

namespace App\Console\Commands;

use App\Models\Rescate;
use App\Models\User;
use App\Models\Notificacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAbandonedRescates extends Command
{
    protected $signature = 'rescates:check-abandoned';
    protected $description = 'Notifica a los administradores sobre rescates pendientes por más de 24 horas';

    public function handle()
    {
        $hours = config('rescate.timeout_hours', 24);
        $rescates = Rescate::where('estado', 'pendiente')
            ->where('created_at', '<', now()->subHours($hours))
            ->get();

        if ($rescates->isEmpty()) {
            $this->info('No hay rescates abandonados.');
            return;
        }

        $admins = User::where('tipo', 'admin')->get();
        if ($admins->isEmpty()) {
            $this->warn('No hay administradores registrados para notificar.');
            return;
        }

        foreach ($rescates as $rescate) {
            foreach ($admins as $admin) {
                Notificacion::create([
                    'user_id'      => $admin->id,
                    'contenido'    => "Rescate #{$rescate->id} (ubicación: {$rescate->lugar_rescate}) no ha sido atendido en {$hours} horas. Prioridad: {$rescate->prioridad}.",
                    'creado_por_id'=> 1,
                ]);
            }
            // Opcional: aumentar prioridad
            if ($rescate->prioridad !== 'alta') {
                $rescate->prioridad = 'alta';
                $rescate->save();
                $this->line("Prioridad del rescate #{$rescate->id} elevada a 'alta'.");
            }
            $this->line("Notificado admin sobre rescate #{$rescate->id}");
        }

        Log::info("Se notificaron " . $rescates->count() . " rescates abandonados a los administradores.");
    }
}
