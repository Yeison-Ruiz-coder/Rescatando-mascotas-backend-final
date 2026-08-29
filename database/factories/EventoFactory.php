<?php

namespace Database\Factories;

use App\Models\Evento;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    private static array $imagenesUsadas = [];

    public static function imagenesDisponibles(): array
    {
        return [
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459999/pexels-photo-28483933_mk0rv5.avif',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459997/pexels-photo-16620580_e9wgcw.avif',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1781809882/eventos/ciu4arlxkqduahlxkgqr.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1781491066/eventos/zqwjbadby4c9tdlo9p0y.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1779295892/eventos/mrzf15ucnpwvwa99rysl.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782627595/eventos/phpIBIKHw_ogl7by.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971511/pexels-jorge-torres-1790459776-28483938_hlsu9m.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971519/pexels-nandamends-16609287_ic8htk.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971519/pexels-sleeba-thomas-156395977-31008338_ihual1.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971520/pexels-rednguyen-17488598_qdabln.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971704/pexels-fatih-guney-337108406-16516866_yvzqao.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971704/pexels-conejodepapel-14647515_soncoq.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971704/pexels-giovanna-kamimura-399616174-33939737_ye5cjl.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971704/pexels-jess-arras-1241792238-36118553_l9nty9.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971705/pexels-tivasee-17374727-6430455_c9eamj.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971705/pexels-el-joven-zag-719710251-27017417_emhg1k.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1787971705/pexels-daniwithaniphotovideo-31744794_xiiuyj.jpg',
        ];
    }

    public static function resetImagenesUsadas(): void
    {
        self::$imagenesUsadas = [];
    }

    public static function imagenesUsadas(): array
    {
        return self::$imagenesUsadas;
    }

    public static function registrarImagenUsada(string $imagen): void
    {
        if (!in_array($imagen, self::$imagenesUsadas, true)) {
            self::$imagenesUsadas[] = $imagen;
        }
    }

    public static function obtenerImagenUnica(): string
    {
        $disponibles = array_values(array_diff(self::imagenesDisponibles(), self::$imagenesUsadas));

        if (empty($disponibles)) {
            self::$imagenesUsadas = [];
            $disponibles = self::imagenesDisponibles();
        }

        $imagen = $disponibles[array_rand($disponibles)];
        self::$imagenesUsadas[] = $imagen;

        return $imagen;
    }

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

    protected function withFaker()
    {
        return FakerFactory::create('es_CO');
    }

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

            'imagen_url' => self::obtenerImagenUnica(),
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
        return $this->state(fn(array $attributes) => [
            'fecha_evento' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'fecha_fin' => $this->faker->optional(0.5)
                ->dateTimeBetween('-6 months', '-1 hour'),
        ]);
    }

    public function proximo(): static
    {
        return $this->state(fn(array $attributes) => [
            'fecha_evento' => $this->faker->dateTimeBetween('+1 day', '+3 months'),
            'fecha_fin' => $this->faker->optional(0.7)
                ->dateTimeBetween('+2 days', '+4 months'),
        ]);
    }
}
