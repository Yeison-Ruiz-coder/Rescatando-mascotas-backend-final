<?php

namespace Database\Seeders;

use App\Models\Fundacion;
use App\Models\Mascota;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Seeder;

class SolicitudesSeeder extends Seeder
{
    public function run(): void
    {
        $fundacion = Fundacion::where('Email', 'contacto@patitasfelices.org')
            ->orWhere('Nombre_1', 'Fundación Patitas Felices')
            ->orWhere('Nombre_1', 'Patitas Felices')
            ->first();

        if (!$fundacion) {
            $this->command->warn('Fundación Patitas Felices no encontrada. No se crearon solicitudes de adopción.');
            return;
        }

        $mascotaIds = Mascota::where('fundacion_id', $fundacion->id)
            ->where('estado', 'En adopcion')
            ->pluck('id');

        if ($mascotaIds->isEmpty()) {
            $this->command->warn('No se encontraron mascotas en adopción para Patitas Felices. No se crearon solicitudes.');
            return;
        }

        $userIds = User::where('tipo', 'user')
            ->pluck('id');

        if ($userIds->isEmpty()) {
            $this->command->warn('No se encontraron usuarios tipo user. No se crearon solicitudes de adopción.');
            return;
        }

        $motivos = [
            'Quiero darle un hogar amoroso a esta mascota.',
            'Estoy en capacidad de cuidarla y brindarle seguridad.',
            'Busco una compañía responsable para mi familia.',
            'Deseo ayudar a esta mascota a encontrar un hogar estable.',
            'Tengo experiencia con mascotas y puedo brindarle atención.',
            'Mi hogar es ideal para su adopción responsable.',
            'Deseo apoyarla con un hogar lleno de cariño.',
            'Quiero brindarle un ambiente seguro y armonioso.',
            'Mi familia está lista para adoptar una mascota.',
            'Buscamos adoptarla y ofrecerle una segunda oportunidad.',
            'Tengo espacio y tiempo para su cuidado diario.',
            'Deseo una mascota con la que compartir momentos felices.'
        ];

        $estados = ['pendiente', 'en_revision', 'aprobada', 'rechazada'];
        $mascotasSeleccionadas = $mascotaIds->shuffle()->take(12);

        foreach ($mascotasSeleccionadas as $index => $mascotaId) {
            $user = User::find($userIds->random());
            $contenido = $motivos[$index] ?? 'Me gustaría adoptar esta mascota y brindarle un hogar seguro.';

            Solicitud::create([
                'tipo_solicitud' => 'adopcion',
                'contenido' => $contenido,
                'estado' => $estados[array_rand($estados)],
                'user_id' => $user?->id,
                'nombre_solicitante' => $user?->nombre . ' ' . $user?->apellidos,
                'email_solicitante' => $user?->email,
                'telefono_solicitante' => $user?->telefono,
                'solicitable_type' => Mascota::class,
                'solicitable_id' => $mascotaId,
                'datos_adicionales' => [
                    'experiencia_mascotas' => 'He tenido mascotas en mi hogar durante años.',
                    'tipo_vivienda' => 'Apartamento',
                    'motivo_adopcion' => $contenido,
                    'compromiso_cuidado' => true,
                    'compromiso_esterilizacion' => true,
                    'compromiso_seguimiento' => true,
                    'direccion' => 'Calle ' . rand(10, 90) . ' # ' . rand(10, 90) . '-' . rand(1, 50),
                    'ciudad' => 'Bogotá',
                    'departamento' => 'Cundinamarca',
                    'codigo_postal' => '110111',
                    'estado_civil' => 'soltero',
                    'cantidad_hijos' => rand(0, 3),
                    'ocupacion' => 'Profesional',
                    'es_propietario' => rand(0, 1) === 1,
                ],
            ]);
        }

        $this->command->info('✅ Se crearon 12 solicitudes de adopción para las mascotas de Patitas Felices.');
    }
}
