<?php

namespace Database\Factories;

use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    private $imagenesEventos = [
        'https://images.pexels.com/photos/16620576/pexels-photo-16620576.jpeg',
        'https://images.pexels.com/photos/9413379/pexels-photo-9413379.jpeg',
        'https://images.pexels.com/photos/16620577/pexels-photo-16620577.jpeg',
        'https://images.pexels.com/photos/9413378/pexels-photo-9413378.jpeg',
        'https://images.pexels.com/photos/16620578/pexels-photo-16620578.jpeg',
        'https://images.pexels.com/photos/9413377/pexels-photo-9413377.jpeg',
        'https://images.pexels.com/photos/16620579/pexels-photo-16620579.jpeg',
        'https://images.pexels.com/photos/37533934/pexels-photo-37533934.jpeg',
        'https://images.pexels.com/photos/16620580/pexels-photo-16620580.jpeg',
        'https://images.pexels.com/photos/33313535/pexels-photo-33313535.jpeg',
        'https://images.pexels.com/photos/16620581/pexels-photo-16620581.jpeg',
        'https://images.pexels.com/photos/28483933/pexels-photo-28483933.jpeg',
        'https://images.pexels.com/photos/33313536/pexels-photo-33313536.jpeg',
        'https://images.pexels.com/photos/16620582/pexels-photo-16620582.jpeg',
        'https://images.pexels.com/photos/33313537/pexels-photo-33313537.jpeg',
    ];

    private $lugaresPopayan = [
        'Parque Caldas',
        'Centro Comercial Campanario',
        'Universidad del Cauca',
        'Polideportivo Tulcán',
        'Casa de la Moneda',
        'Barrio La Esmeralda',
        'Barrio Modelo',
        'Vereda Clarete',
        'Vereda Julumito',
        'Terminal de Transportes de Popayán',
        'Parque Benito Juárez',
        'Centro Recreativo Pisojé',
    ];

    private $nombresEventos = [
        'Jornada de Adopción Canina',
        'Campaña de Esterilización Gratuita',
        'Vacunación para Mascotas',
        'Festival Animalista del Cauca',
        'Concierto Solidario por los Animales',
        'Feria de Bienestar Animal',
        'Recaudación para Refugios',
        'Encuentro de Mascotas Popayán',
        'Patitas al Parque',
        'Jornada de Educación Animal',
    ];

    private $organizadores = [
        'Fundación Huellas de Amor',
        'Fundación Patitas Cauca',
        'Fundación Amigos de los Animales',
        'Corporación Animalista del Cauca',
        'Grupo Rescate Animal Popayán',
        'Alcaldía de Popayán',
        'Universidad del Cauca',
    ];

    private $categorias = [
        'Adopción',
        'Vacunación',
        'Esterilización',
        'Bienestar Animal',
        'Recaudación',
        'Concierto Solidario',
    ];

    public function definition(): array
    {
        $fechaEvento = $this->faker->dateTimeBetween('now', '+6 months');

        $maxFechaFin = (clone $fechaEvento)->modify('+15 days');

        $tieneFechaFin = $this->faker->boolean(70);

        $fechaFin = $tieneFechaFin
            ? $this->faker->dateTimeBetween($fechaEvento, $maxFechaFin)
            : null;

        return [
            'nombre_evento' => $this->faker->randomElement($this->nombresEventos),

            'lugar_evento' => $this->faker->randomElement($this->lugaresPopayan),

            'descripcion' => $this->faker->randomElement([
                'Evento destinado al bienestar y protección de los animales del municipio.',
                'Jornada comunitaria para promover la adopción responsable y el cuidado animal.',
                'Actividad organizada para recaudar fondos destinados a refugios y fundaciones.',
                'Espacio educativo para fortalecer la tenencia responsable de mascotas.',
                'Evento abierto a toda la comunidad para apoyar el bienestar de perros y gatos.',
            ]),

            'fecha_evento' => $fechaEvento,
            'fecha_fin' => $fechaFin,

            'imagen_url' => $this->faker->randomElement($this->imagenesEventos),
            'imagen_public_id' => 'eventos/imagen_' . $this->faker->uuid(),

            'tipo' => $this->faker->randomElement([
                'fundacion',
                'admin'
            ]),

            'capacidad_maxima' => $this->faker->numberBetween(50, 500),

            'costo' => $this->faker->randomElement([
                'Gratis',
                '$10.000',
                '$20.000',
                '$30.000',
                '$50.000'
            ]),

            'organizador' => $this->faker->randomElement($this->organizadores),

            'telefono_contacto' => '3' . $this->faker->numerify('#########'),

            'email_contacto' => $this->faker->randomElement([
                'contacto@huellasdeamor.org',
                'info@patitascauca.org',
                'eventos@popayan.gov.co',
                'bienestaranimal@unicauca.edu.co',
                'adopciones@animalescauca.org'
            ]),

            'categoria' => $this->faker->randomElement($this->categorias),

            'tags' => json_encode([
                'popayan',
                'cauca',
                'animales'
            ]),

            'likes' => $this->faker->numberBetween(0, 1000),
        ];
    }

    public function pasado(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_evento' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'fecha_fin' => $this->faker->optional(0.5)
                ->dateTimeBetween('-6 months', '-1 hour'),
        ]);
    }

    public function proximo(): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_evento' => $this->faker->dateTimeBetween('+1 day', '+3 months'),
            'fecha_fin' => $this->faker->optional(0.7)
                ->dateTimeBetween('+2 days', '+4 months'),
        ]);
    }
}
