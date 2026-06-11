<?php

namespace Database\Factories;

use App\Models\Veterinaria;
use Illuminate\Database\Eloquent\Factories\Factory;

class VeterinariaFactory extends Factory
{
    protected $model = Veterinaria::class;

    // URLs base de Cloudinary para logos
    private $logosCloudinary = [
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1781146028/descargar_1_bf2lcg.jpg',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1781146031/descargar_2_bs8hyk.jpg',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1781146030/descargar_hd9pdc.jpg',
    ];

    // 🆕 Galería con imágenes reales de Cloudinary
    private $galeriaCloudinary = [
        'https://res.cloudinary.com/demo/image/upload/sample.jpg',
        'https://res.cloudinary.com/demo/image/upload/dog.jpg',
        'https://res.cloudinary.com/demo/image/upload/cat.jpg',
        'https://res.cloudinary.com/demo/image/upload/sample_people.jpg',
        'https://res.cloudinary.com/demo/image/upload/cld-sample-4.jpg',
        'https://res.cloudinary.com/demo/image/upload/cld-sample-5.jpg',
    ];

    public function definition(): array
    {
        $servicios = ['Consulta general', 'Vacunación', 'Cirugía', 'Hospitalización', 'Laboratorio', 'Radiología', 'Odontología', 'Peluquería'];
        $serviciosSeleccionados = $this->faker->randomElements($servicios, $this->faker->numberBetween(3, 6));

        return [
            'Nombre_vet' => $this->faker->company() . ' ' . $this->faker->randomElement(['Veterinaria', 'Animal Clinic', 'Pet Care']),
            'descripcion' => $this->faker->paragraphs(3, true),
            'Direccion' => $this->faker->unique()->streetAddress(),
            'Telefono' => $this->faker->unique()->phoneNumber(),
            'Email' => $this->faker->unique()->companyEmail(),
            'servicios' => json_encode($serviciosSeleccionados),
            'servicios_detallados' => json_encode([
                ['nombre' => 'Consulta', 'precio' => $this->faker->randomFloat(2, 20, 50)],
                ['nombre' => 'Vacuna', 'precio' => $this->faker->randomFloat(2, 15, 40)],
                ['nombre' => 'Desparasitación', 'precio' => $this->faker->randomFloat(2, 10, 30)],
            ]),
            'equipo_medico' => json_encode([
                'veterinarios' => $this->faker->numberBetween(2, 10),
                'asistentes' => $this->faker->numberBetween(1, 5),
                'equipos' => ['Ultrasonido', 'Rayos X', 'Laboratorio']
            ]),
            'horario_atencion' => 'Lun-Vie: 8am-7pm, Sáb: 9am-2pm',
            'anios_experiencia' => $this->faker->numberBetween(1, 30),
            'urgencias_24h' => $this->faker->boolean(30),
            'convenios' => json_encode($this->faker->randomElements(['Arus', 'Seguros Bolívar', 'Sura', 'Allianz'], $this->faker->numberBetween(0, 3))),
            'precio_consulta' => $this->faker->randomFloat(2, 25000, 80000),
            'acepta_seguros' => $this->faker->boolean(50),
            'valoracion_promedio' => $this->faker->randomFloat(2, 3, 5),
            'total_valoraciones' => $this->faker->numberBetween(1, 500),

            // Cloudinary - LOGO (tus imágenes)
            'logo' => $this->faker->randomElement($this->logosCloudinary),
            'logo_public_id' => 'veterinarias/logo_' . $this->faker->uuid(),

            // 🔥 CORREGIDO: Galería con Cloudinary, no placeholders
            'galeria_fotos' => json_encode($this->faker->randomElements($this->galeriaCloudinary, 3)),

            'redes_sociales' => json_encode([
                'facebook' => 'https://facebook.com/' . $this->faker->userName(),
                'instagram' => 'https://instagram.com/' . $this->faker->userName(),
            ]),
            'whatsapp' => $this->faker->phoneNumber(),
            'sitio_web' => $this->faker->url(),
            'verificado' => $this->faker->boolean(70),
            'documentos_verificacion' => json_encode(['documento1.pdf', 'documento2.pdf']),
            'cobertura_zona' => json_encode(['Zona Norte', 'Zona Centro', 'Zona Sur']),
            'ciudad' => $this->faker->city(),
            'departamento' => $this->faker->state(),
            'lat' => $this->faker->latitude(4.5, 4.8),
            'lng' => $this->faker->longitude(-74.2, -74.0),
            'radio_atencion' => $this->faker->numberBetween(5, 20),
        ];
    }

    // Estados personalizados
    public function verificada(): static
    {
        return $this->state(fn (array $attributes) => [
            'verificado' => true,
            'valoracion_promedio' => $this->faker->randomFloat(2, 4, 5),
        ]);
    }

    public function conUrgencias(): static
    {
        return $this->state(fn (array $attributes) => [
            'urgencias_24h' => true,
            'horario_atencion' => '24/7 - Todos los días',
        ]);
    }
}
