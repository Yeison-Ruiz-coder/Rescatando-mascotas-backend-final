<?php

namespace Database\Seeders;

use App\Models\Fundacion;
use App\Models\Mascota;
use App\Models\Rescate;
use App\Models\User;
use App\Models\Veterinaria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RescatesSeeder extends Seeder
{
    public function run(): void
    {
        $reportante = User::where('tipo', 'user')->inRandomOrder()->first();
        $fundacion = Fundacion::inRandomOrder()->first();
        $veterinaria = Veterinaria::inRandomOrder()->first();
        $mascota = Mascota::inRandomOrder()->first();

        if (!$reportante || (!$fundacion && !$veterinaria)) {
            return;
        }

        $fotoUrls = [
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1778139768/mascotas/fcnrvrzellqqdj0xecdv.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462481/e82a50e9587cf0292e16d8fd3752e048_-_copia_xyoezz.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462479/beneficios_de_tener_una_mascota-1_vnelpa.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462405/images_4_dlswmb.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462403/images_3_vtnxza.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462401/images_2_gpguag.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462400/images_1_kcf91v.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462399/657694610_222735447_1706x1280_kqophi.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782208985/mascotas/xq2ebzevgu0nygkqk9ff.jpg',
        ];

        $ubicaciones = [
            ['lugar' => 'Parque Caldas, Popayán', 'lat' => 2.4447, 'lng' => -76.6074],
            ['lugar' => 'Terminal de Transportes de Popayán', 'lat' => 2.4541, 'lng' => -76.6019],
            ['lugar' => 'Centro Comercial Campanario', 'lat' => 2.4721, 'lng' => -76.6105],
            ['lugar' => 'Universidad del Cauca', 'lat' => 2.4544, 'lng' => -76.6082],
            ['lugar' => 'Polideportivo Tulcán', 'lat' => 2.4499, 'lng' => -76.6011],
            ['lugar' => 'Casa de la Moneda', 'lat' => 2.4478, 'lng' => -76.6077],
            ['lugar' => 'Barrio La Esmeralda', 'lat' => 2.4592, 'lng' => -76.6118],
            ['lugar' => 'Barrio Modelo', 'lat' => 2.4491, 'lng' => -76.6034],
        ];

        $descr = [
            'Perro herido encontrado cerca del parque, requiere atención urgente y traslado a clínica veterinaria.',
            'Gato abandonado en la terminal de transportes, presentaba deshidratación y necesita refugio.',
            'Conejo rescatado en el Centro Comercial Campanario, llegó con golpes y está en observación.',
            'Perro de la calle encontrado con posible fractura en la Universidad del Cauca.',
            'Ave herida aferior al Polideportivo Tulcán, requiere evaluación veterinaria inmediata.',
            'Perro en situación de abandono en Casa de la Moneda, buscando hogar temporal.',
            'Gato rescatado en Barrio La Esmeralda, en proceso de rehabilitación y búsqueda de adopción.',
            'Perro encontrado en Barrio Modelo con signos de desnutrición leve.',
        ];

        $estados = ['pendiente', 'en_proceso', 'completado'];
        $emergencias = ['herido', 'abandonado', 'urgente', 'otro'];
        $prioridades = ['alta', 'media', 'baja'];

        foreach ($ubicaciones as $index => $ubicacion) {
            $responsable = ($index % 2 === 0 && $fundacion) ? $fundacion : ($veterinaria ?: $fundacion);
            $tipoResponsable = $responsable instanceof Veterinaria ? Veterinaria::class : Fundacion::class;

            Rescate::create([
                'fecha_rescate' => Carbon::now()->subDays(2 + $index),
                'lugar_rescate' => $ubicacion['lugar'],
                'descripcion_rescate' => $descr[$index],
                'foto_principal' => $fotoUrls[$index % count($fotoUrls)],
                'foto_principal_public_id' => null,
                'galeria_fotos' => null,
                'galeria_fotos_public_ids' => null,
                'fotos_metadata' => null,
                'estado' => $estados[$index % count($estados)],
                'tipo_emergencia' => $emergencias[$index % count($emergencias)],
                'prioridad' => $prioridades[$index % count($prioridades)],
                'lat' => $ubicacion['lat'],
                'lng' => $ubicacion['lng'],
                'nombre_reportante' => $reportante->name,
                'email_reportante' => $reportante->email,
                'telefono_reportante' => '3' . rand(100000000, 999999999),
                'mascota_id' => $mascota?->id,
                'reporte_id' => null,
                'usuario_reporto_id' => $reportante->id,
                'entidad_responsable_type' => $tipoResponsable,
                'entidad_responsable_id' => $responsable->id,
                'gestionado_por' => null,
            ]);
        }
    }
}
