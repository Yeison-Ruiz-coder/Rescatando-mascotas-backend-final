<?php

namespace Database\Factories;

use App\Models\Mascota;
use Illuminate\Database\Eloquent\Factories\Factory;

class MascotaFactory extends Factory
{
    protected $model = Mascota::class;

    // URLs base de Cloudinary (reutilizables)
    private $imagenesCloudinary = [
        'perro' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1778139768/mascotas/fcnrvrzellqqdj0xecdv.jpg',
        'gato' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1779298823/mascotas/a7xp51aaiu8pvjtybjsj.jpg',
        'conejo' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1779298999/mascotas/ydopizlj9e4y8siev2yk.jpg',
        'ave' => 'https://res.cloudinary.com/demo/image/upload/bird.jpg',
    ];

    private $publicIdsCloudinary = [
        'perro' => 'demo/dog',
        'gato' => 'demo/cat',
        'conejo' => 'demo/rabbit',
        'ave' => 'demo/bird',
    ];

    public function definition(): array
    {
        $especie = $this->faker->randomElement(['Perro', 'Gato', 'Conejo', 'Ave']);
        $imagenKey = strtolower($especie);

        return [
            'nombre_mascota' => $this->faker->firstName(),
            'especie' => $especie,
            'edad_aprox' => $this->faker->randomFloat(2, 0.5, 15),
            'peso_aprox' => $this->faker->randomFloat(2, 2, 40),
            'tamano' => $this->faker->randomElement(['pequeño', 'mediano', 'grande']),
            'color' => $this->faker->safeColorName(),
            'genero' => $this->faker->randomElement(['Macho', 'Hembra']),
            'estado' => 'En adopcion',

            // 🔥 Cloudinary: usando imágenes predefinidas
            'foto_principal' => $this->imagenesCloudinary[$imagenKey],
            'foto_principal_public_id' => $this->publicIdsCloudinary[$imagenKey],
            'galeria_fotos' => json_encode([
                $this->imagenesCloudinary[$imagenKey],
                $this->imagenesCloudinary[$imagenKey] . '?c_scale,w_400',
                $this->imagenesCloudinary[$imagenKey] . '?e_sepia',
            ]),

            'descripcion' => $this->faker->paragraph(),
            'esterilizado' => $this->faker->boolean(70),
            'vacunado' => $this->faker->boolean(80),
            'desparasitado' => $this->faker->boolean(90),
            'fecha_ingreso' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'vistas' => $this->faker->numberBetween(0, 500),
        ];
    }

    // Estados personalizados para diferentes situaciones
    public function destacada(): static
    {
        return $this->state(fn (array $attributes) => [
            'destacada' => true,
            'vistas' => $this->faker->numberBetween(500, 5000),
        ]);
    }

    public function adoptada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => 'Adoptado',
            'fecha_salida' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    public function conVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'video_url' => 'https://res.cloudinary.com/demo/video/upload/sample.mp4',
            'video_public_id' => 'demo/sample_video',
        ]);
    }
}
