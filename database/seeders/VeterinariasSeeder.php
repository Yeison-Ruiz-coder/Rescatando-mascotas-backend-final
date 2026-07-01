<?php

namespace Database\Seeders;

use App\Models\Veterinaria;
use Illuminate\Database\Seeder;

class VeterinariasSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. VETERINARIAS CON FACTORY (MASIVAS)
        // ==========================================

        // Crear 30 veterinarias normales
        Veterinaria::factory()
            ->count(30)
            ->create();

        // Crear 10 veterinarias verificadas
        Veterinaria::factory()
            ->count(10)
            ->verificada()
            ->create();

        // Crear 5 veterinarias con urgencias 24h
        Veterinaria::factory()
            ->count(5)
            ->conUrgencias()
            ->create();

        // ==========================================
        // 2. VETERINARIAS ESPECÍFICAS (CASOS MANUALES)
        // ==========================================

        // Veterinaria destacada 1 - Usando tus imágenes de Cloudinary
        Veterinaria::create([
            'Nombre_vet' => 'Veterinaria Central 24/7',
            'descripcion' => 'La mejor atención veterinaria con urgencias las 24 horas. Contamos con equipos de última generación y personal altamente calificado.',
            'Direccion' => 'Calle Principal #123, Chapinero',
            'Telefono' => '3001234567',
            'Email' => 'central@veterinaria.com',
            'servicios' => json_encode(['Consulta general', 'Urgencias 24h', 'Cirugía', 'Hospitalización', 'Laboratorio']),
            'servicios_detallados' => json_encode([
                ['nombre' => 'Consulta', 'precio' => 45000],
                ['nombre' => 'Urgencias', 'precio' => 80000],
                ['nombre' => 'Cirugía', 'precio' => 250000],
            ]),
            'equipo_medico' => json_encode([
                'veterinarios' => 5,
                'asistentes' => 3,
                'equipos' => ['Ultrasonido', 'Rayos X', 'Tomógrafo', 'Laboratorio']
            ]),
            'horario_atencion' => '24/7 - Todos los días',
            'anios_experiencia' => 15,
            'urgencias_24h' => true,
            'convenios' => json_encode(['Arus', 'Seguros Bolívar']),
            'precio_consulta' => 45000,
            'acepta_seguros' => true,
            'valoracion_promedio' => 4.9,
            'total_valoraciones' => 328,

            // 🔥 Cloudinary - Usando una de TUS imágenes
            'logo' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1781146028/descargar_1_bf2lcg.jpg',
            'logo_public_id' => 'veterinarias/logo_central_24_7',
            'redes_sociales' => json_encode([
                'facebook' => 'https://facebook.com/veterinariacentral',
                'instagram' => 'https://instagram.com/vetcentral',
                'whatsapp' => 'https://wa.me/573001234567'
            ]),
            'whatsapp' => '3001234567',
            'sitio_web' => 'https://veterinariacentral.com',
            'verificado' => true,
            'documentos_verificacion' => json_encode(['certificado_sanitario.pdf', 'licencia_funcionamiento.pdf']),
            'cobertura_zona' => json_encode(['Chapinero', 'Usaquén', 'Santa Bárbara']),
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca',
            'lat' => 4.6351,
            'lng' => -74.0635,
            'radio_atencion' => 15,
        ]);

        // Veterinaria destacada 2 - Especialistas en gatos
        Veterinaria::create([
            'Nombre_vet' => 'Felinus - Clínica para Gatos',
            'descripcion' => 'Especialistas en medicina felina. Ambiente libre de estrés para tu gato.',
            'Direccion' => 'Calle 85 # 15-30, Chicó',
            'Telefono' => '3019876543',
            'Email' => 'hola@felinus.com',
            'servicios' => json_encode(['Consulta especializada', 'Odontología felina', 'Vacunación', 'Ecografía']),
            'servicios_detallados' => json_encode([
                ['nombre' => 'Consulta felina', 'precio' => 55000],
                ['nombre' => 'Odontología', 'precio' => 120000],
                ['nombre' => 'Ecografía', 'precio' => 180000],
            ]),
            'equipo_medico' => json_encode([
                'veterinarios' => 3,
                'asistentes' => 2,
                'equipos' => ['Ecógrafo', 'Odontológico', 'Laboratorio']
            ]),
            'horario_atencion' => 'Lun-Sáb: 9am-7pm',
            'anios_experiencia' => 8,
            'urgencias_24h' => false,
            'convenios' => json_encode(['Sura']),
            'precio_consulta' => 55000,
            'acepta_seguros' => true,
            'valoracion_promedio' => 4.8,
            'total_valoraciones' => 156,

            // 🔥 Usando tu segunda imagen
            'logo' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1781146031/descargar_2_bs8hyk.jpg',
            'logo_public_id' => 'veterinarias/logo_felinus',
            'redes_sociales' => json_encode([
                'facebook' => 'https://facebook.com/felinus',
                'instagram' => 'https://instagram.com/felinus'
            ]),
            'whatsapp' => '3019876543',
            'sitio_web' => 'https://felinus.com',
            'verificado' => true,
            'cobertura_zona' => json_encode(['Chicó', 'Rosales', 'Cedritos']),
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca',
            'lat' => 4.6683,
            'lng' => -74.0486,
            'radio_atencion' => 10,
        ]);
    }
}
