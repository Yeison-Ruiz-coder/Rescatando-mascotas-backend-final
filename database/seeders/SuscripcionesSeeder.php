<?php

namespace Database\Seeders;

use App\Models\Fundacion;
use App\Models\Mascota;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuscripcionesSeeder extends Seeder
{
    public function run(): void
    {
        $fundacion = Fundacion::where('Email', 'contacto@patitasfelices.org')
            ->orWhere('Nombre_1', 'Fundación Patitas Felices')
            ->orWhere('Nombre_1', 'Patitas Felices')
            ->first();

        if (!$fundacion) {
            $this->command->warn('Fundación Patitas Felices no encontrada. No se crearon suscripciones.');
            return;
        }

        $mascotasIds = Mascota::where('fundacion_id', $fundacion->id)
            ->pluck('id');

        if ($mascotasIds->isEmpty()) {
            $this->command->warn('No se encontraron mascotas para Patitas Felices. No se crearon suscripciones.');
            return;
        }

        $userIds = User::where('tipo', 'user')
            ->pluck('id');

        if ($userIds->isEmpty()) {
            $this->command->warn('No se encontraron usuarios tipo user. No se crearon suscripciones.');
            return;
        }

        $mensajes = [
            'Apoyo mensual para mantener a los peluditos.',
            'Donación periódica para su comida y cuidado.',
            'Pequeño aporte para su veterinario.',
            'Suscripción para ayudar en su rehabilitación.',
            'Apoyo constante para la alimentación.',
            'Cuido mensual para sus gastos médicos.',
            'Aporte para su traslado y atención.',
            'Donación recurrente para su bienestar.',
            'Apoyo para la atención en veterinaria.',
            'Suscripción para su hogar y alimento.',
            'Contribución mensual para su cuidado.',
            'Ayuda para su proceso de adopción.'
        ];

        $frecuencias = ['mensual', 'trimestral', 'anual'];
        $estados = ['activo', 'pausado', 'cancelado', 'pendiente'];

        $mascotasSeleccionadas = $mascotasIds->shuffle()->take(12);

        foreach ($mascotasSeleccionadas as $index => $mascotaId) {
            Suscripcion::create([
                'user_id' => $userIds->random(),
                'mascota_id' => $mascotaId,
                'monto_mensual' => rand(25000, 120000),
                'frecuencia' => $frecuencias[array_rand($frecuencias)],
                'fecha_inicio' => now()->subDays(rand(1, 60))->toDateString(),
                'fecha_fin' => null,
                'mensaje_apoyo' => $mensajes[$index] ?? 'Apoyo mensual para la mascota.',
                'estado' => $estados[array_rand($estados)],
            ]);
        }

        $this->command->info('✅ Se crearon 12 suscripciones para mascotas de Patitas Felices.');
    }
}
