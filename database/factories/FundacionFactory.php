<?php

namespace Database\Factories;

use App\Models\Fundacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundacionFactory extends Factory
{
    protected $model = Fundacion::class;

    private $imagenesPortada = [
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1781145846/descargar_1_nynmgd.jpg',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1781145847/descargar_cgcgvf.jpg',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1781145849/fundacion_mqjzvs.jpg',
    ];

    public function definition(): array
    {
        $necesidades = ['Comida para perros', 'Medicamentos', 'Mantas', 'Juguetes', 'Correas', 'Arenero para gatos'];

        return [
            'Nombre_1' => $this->faker->company() . ' ' . $this->faker->randomElement(['Fundación', 'Rescatando Patitas', 'Huellas Felices']),
            'Direccion' => $this->faker->unique()->streetAddress(),
            'Telefono' => $this->faker->unique()->phoneNumber(),
            'Email' => $this->faker->unique()->companyEmail(),
            'registro_sanitario' => 'REG-' . $this->faker->unique()->bothify('###-###'),
            'capacidad_maxima' => $this->faker->numberBetween(20, 200),
            'necesidades_actuales' => json_encode($this->faker->randomElements($necesidades, $this->faker->numberBetween(2, 5))),
            'horario_atencion' => 'Lun-Vie: 9am-6pm, Sáb: 10am-2pm',
            'recibe_voluntarios' => $this->faker->boolean(80),
            'lat' => $this->faker->latitude(4.5, 4.8),
            'lng' => $this->faker->longitude(-74.2, -74.0),
            'radio_atencion' => $this->faker->numberBetween(5, 15),

            // Cloudinary
            'imagen_portada' => $this->faker->randomElement($this->imagenesPortada),
            'imagen_portada_public_id' => 'fundaciones/portada_' . $this->faker->uuid(),

            'verificado' => $this->faker->boolean(60),
            'ciudad' => $this->faker->city(),
            'fecha_fundacion' => $this->faker->dateTimeBetween('-10 years', 'now'),
        ];
    }

    public function verificada(): static
    {
        return $this->state(fn (array $attributes) => [
            'verificado' => true,
            'capacidad_maxima' => $this->faker->numberBetween(50, 200),
        ]);
    }

    public function conVoluntarios(): static
    {
        return $this->state(fn (array $attributes) => [
            'recibe_voluntarios' => true,
        ]);
    }
}
