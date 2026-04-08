<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use Illuminate\Console\Command;

class RepairUserProfiles extends Command
{
    protected $signature = 'users:repair-profiles';
    protected $description = 'Repara perfiles de usuarios que no tienen fundacion o veterinaria asociada';

    public function handle()
    {
        $users = User::where('tipo', 'fundacion')->get();

        foreach ($users as $user) {
            $fundacion = Fundacion::where('user_id', $user->id)->first();

            if (!$fundacion) {
                $this->info("Reparando usuario: {$user->email}");

                // Crear perfil de fundación
                Fundacion::create([
                    'Nombre_1' => $user->nombre ?? $user->email,
                    'Direccion' => $user->direccion ?? 'Pendiente',
                    'Telefono' => $user->telefono ?? '000000000',
                    'Email' => $user->email,
                    'registro_sanitario' => 'PENDIENTE_' . $user->id,
                    'capacidad_maxima' => null,
                    'user_id' => $user->id,
                ]);

                $this->info("✅ Perfil creado para {$user->email}");
            }
        }

        $this->info("Reparación completada");
    }
}
