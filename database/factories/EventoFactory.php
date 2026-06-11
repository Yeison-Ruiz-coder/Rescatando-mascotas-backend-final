<?php

namespace Database\Factories;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    private $imagenesEventos = [
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1779295783/eventos/j2sedqzhdk0g7rp094qg.jpg',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1779295482/eventos/k7trnoc91o3t17lluwaf.jpg',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1779296017/eventos/rboy7qdpjud8gzcrsjia.jpg',
    ];

    public function definition(): array
    {
        $fechaEvento = $this->faker->dateTimeBetween('now', '+6 months');

        // Calcular fecha fin máxima (hasta 15 días después del evento)
        $maxFechaFin = (clone $fechaEvento)->modify('+15 days');

        // 70% de probabilidad de tener fecha fin (evento de varios días)
        $tieneFechaFin = $this->faker->boolean(70);
        $fechaFin = $tieneFechaFin
            ? $this->faker->dateTimeBetween($fechaEvento, $maxFechaFin)
            : null;

        return [
            'nombre_evento' => $this->faker->sentence(3),
            'lugar_evento' => $this->faker->address(),
            'descripcion' => $this->faker->paragraphs(2, true),
            'fecha_evento' => $fechaEvento,
            'fecha_fin' => $fechaFin,

            // Cloudinary
            'imagen_url' => $this->faker->randomElement($this->imagenesEventos),
            'imagen_public_id' => 'eventos/imagen_' . $this->faker->uuid(),

            'tipo' => $this->faker->randomElement(['fundacion', 'admin']),
            'capacidad_maxima' => $this->faker->optional()->numberBetween(20, 500),
            'costo' => $this->faker->optional()->randomElement(['Gratis', '$10', '$20', '$50']),
            'organizador' => $this->faker->name(),
            'telefono_contacto' => $this->faker->phoneNumber(),
            'email_contacto' => $this->faker->email(),
            'categoria' => $this->faker->randomElement(['Adopción', 'Vacunación', 'Jornada Médica', 'Recaudación', 'Concierto']),
            'tags' => json_encode($this->faker->words(3)),
            'likes' => $this->faker->numberBetween(0, 1000),
        ];
    }

    public function pasado(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_evento' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'fecha_fin' => $this->faker->optional(0.5)->dateTimeBetween('-6 months', '-1 hour'),
        ]);
    }

    public function proximo(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_evento' => $this->faker->dateTimeBetween('+1 day', '+3 months'),
            'fecha_fin' => $this->faker->optional(0.7)->dateTimeBetween('+2 days', '+4 months'),
        ]);
    }
}
