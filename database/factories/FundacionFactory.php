<?php

namespace Database\Factories;

use App\Models\Fundacion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FundacionFactory extends Factory
{
    protected $model = Fundacion::class;

    // 🖼️ MÁS IMÁGENES DE PORTADA (30+ opciones)
    private $imagenesPortada = [
        // Fundaciones reales (puedes reemplazar con URLs de imágenes de fundaciones de prueba o de tu propiedad)
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460777/images_nbmj0r.png",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460776/images_zvwjpm.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460775/images_12_jzipdk.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460773/images_11_enht32.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460772/images_10_q5blsf.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460771/images_9_jnoh4u.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460770/images_8_il9ir7.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460769/images_7_daakgy.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460768/images_6_jiuccf.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460767/images_5_oxamyo.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460766/images_4_kmxnzh.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460765/images_3_t5oefq.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460763/images_2_c4iy10.png",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460762/images_2_r5zjrc.jpg",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460761/images_1_jeedps.png",
        "https://res.cloudinary.com/dixyebg5i/image/upload/v1782460761/images_1_tefmge.jpg"

    ];

    // 🏙️ CIUDADES DE COLOMBIA
    private $ciudades = [
        'Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena',
        'Cúcuta', 'Bucaramanga', 'Pereira', 'Santa Marta', 'Ibagué',
        'Pasto', 'Manizales', 'Neiva', 'Armenia', 'Popayán',
        'Valledupar', 'Montería', 'Sincelejo', 'Riohacha', 'Quibdó',
        'Tunja', 'Florencia', 'Yopal', 'Mocoa', 'Leticia',
        'San Andrés', 'Providencia', 'Sogamoso', 'Duitama', 'Chía'
    ];

    // 🏢 TIPOS DE FUNDACIÓN
    private $tiposFundacion = [
        'Protección Animal', 'Rescate y Adopción', 'Esterilización',
        'Educación Animal', 'Brigadas Veterinarias', 'Hogar de Paso',
        'Santuario Animal', 'Clínica Veterinaria Solidaria'
    ];

    // 📋 NECESIDADES COMUNES
    private $necesidadesComunes = [
        'Comida para perros (criadores)',
        'Comida para gatos (criadores)',
        'Medicamentos antipulgas',
        'Vacunas (Rabia, Moquillo, Leucemia)',
        'Mantas y cobijas',
        'Juguetes para perros',
        'Juguetes para gatos',
        'Correas y collares',
        'Transportadoras',
        'Arenero para gatos',
        'Arena sanitaria',
        'Shampoo y productos de aseo',
        'Bebederos y comederos',
        'Jaulas de transporte',
        'Pañales desechables',
        'Leche maternizada',
        'Alimentos húmedos (latas)',
        'Suplementos nutricionales',
        'Gasas y vendas',
        'Desinfectantes',
        'Guantes quirúrgicos',
        'Jeringas y agujas',
        'Antibióticos',
        'Antiinflamatorios',
        'Desparasitantes',
        'Voluntarios para paseos',
        'Voluntarios para adopciones',
        'Voluntarios veterinarios',
        'Foster homes (hogares temporales)',
        'Transporte para rescates',
        'Donaciones económicas',
        'Equipo de oficina',
        'Computadores',
        'Impresora',
        'Cámaras de seguridad',
        'Vehículo para rescates'
    ];

    public function definition(): array
    {
        $nombreFundacion = $this->generarNombreFundacion();
        $ciudad = $this->faker->randomElement($this->ciudades);

        // Generar necesidades más realistas (5-8 necesidades)
        $necesidades = $this->faker->randomElements(
            $this->necesidadesComunes,
            $this->faker->numberBetween(5, 8)
        );

        return [
            // DATOS PRINCIPALES
            'Nombre_1' => $nombreFundacion,
            'Direccion' => $this->generarDireccion($ciudad),
            'Telefono' => $this->generarTelefonoColombia(),
            'Email' => $this->generarEmailFundacion($nombreFundacion),
            'registro_sanitario' => $this->generarRegistroSanitario(),

            // CAPACIDAD Y HORARIOS
            'capacidad_maxima' => $this->faker->numberBetween(20, 200),
            'horario_atencion' => $this->generarHorarioAtencion(),
            'recibe_voluntarios' => $this->faker->boolean(80),

            // UBICACIÓN
            'lat' => $this->faker->latitude(4.0, 4.9),
            'lng' => $this->faker->longitude(-74.5, -73.8),
            'radio_atencion' => $this->faker->numberBetween(5, 15),
            'ciudad' => $ciudad,

            // IMÁGENES
            'imagen_portada' => $this->faker->randomElement($this->imagenesPortada),
            'imagen_portada_public_id' => 'fundaciones/portada_' . $this->faker->uuid(),

            // INFORMACIÓN ADICIONAL
            'fecha_fundacion' => $this->faker->dateTimeBetween('-15 years', 'now'),
            'verificado' => $this->faker->boolean(60),
            'necesidades_actuales' => json_encode($necesidades),

            // DESCRIPCIÓN (agregar si tu modelo tiene este campo)
            // 'descripcion' => $this->generarDescripcion($nombreFundacion),
        ];
    }

    // ========== MÉTODOS PARA GENERAR DATOS REALISTAS ==========

    private function generarNombreFundacion(): string
    {
        $palabras = [
            'Huellas', 'Patitas', 'Corazón', 'Amor', 'Esperanza',
            'Vida', 'Alegría', 'Felicidad', 'Luz', 'Ángel',
            'Guardianes', 'Protectores', 'Amigos', 'Familia', 'Hogar'
        ];

        $animales = ['Perros', 'Gatos', 'Animales', 'Mascotas', 'Peludos'];
        $sufijos = ['Fundación', 'Asociación', 'Corporación', 'Organización', 'Rescate'];

        return $this->faker->randomElement($palabras) . ' ' .
               $this->faker->randomElement($animales) . ' ' .
               $this->faker->randomElement($sufijos);
    }

    private function generarDireccion(string $ciudad): string
    {
        $tiposVia = ['Calle', 'Carrera', 'Avenida', 'Diagonal', 'Transversal'];
        $tipoVia = $this->faker->randomElement($tiposVia);
        $numero = $this->faker->numberBetween(1, 200);
        $letra = $this->faker->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']);
        $barrios = ['El Poblado', 'Laureles', 'Chapinero', 'Usaquén', 'Ciudad Jardín', 'El Golf'];

        return "{$tipoVia} {$numero}{$letra} # {$this->faker->numberBetween(1, 100)}-{$this->faker->numberBetween(1, 100)}, {$this->faker->randomElement($barrios)}, {$ciudad}";
    }

    private function generarTelefonoColombia(): string
    {
        $operador = $this->faker->randomElement(['310', '311', '312', '313', '314', '315', '316', '317', '318', '319']);
        return $operador . ' ' . $this->faker->numberBetween(100, 999) . ' ' . $this->faker->numberBetween(1000, 9999);
    }

    private function generarEmailFundacion(string $nombre): string
    {
        $slug = strtolower(str_replace(' ', '', $nombre));
        $dominios = ['gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'fundaciones.co'];
        return $slug . '@' . $this->faker->randomElement($dominios);
    }

    private function generarRegistroSanitario(): string
    {
        return 'REG-' . $this->faker->unique()->bothify('###-###-###');
    }

    private function generarHorarioAtencion(): string
    {
        $horarios = [
            'Lun-Vie: 8am-6pm, Sáb: 9am-2pm',
            'Lun-Vie: 9am-7pm, Sáb: 8am-1pm',
            'Lun-Vie: 8am-5pm, Sáb: 10am-3pm',
            'Lun-Vie: 7am-6pm, Sáb-Dom: 9am-12pm',
            'Lun-Sáb: 8am-8pm, Dom: 10am-2pm',
            'Lun-Vie: 9am-9pm, Sáb: 8am-4pm',
            'Lun-Vie: 8am-7pm, Sáb: 9am-1pm',
            'Lun-Sáb: 7am-7pm',
        ];
        return $this->faker->randomElement($horarios);
    }

    private function generarDescripcion(string $nombre): string
    {
        $misiones = [
            "Somos una fundación dedicada a la protección y rescate de animales en situación de calle. Trabajamos incansablemente para encontrar hogares amorosos y brindar atención veterinaria a los más necesitados.",
            "Nuestra misión es promover el bienestar animal a través de la educación, esterilización y adopción responsable. Creemos en un mundo donde todos los animales tengan un hogar digno.",
            "En {$nombre} nos especializamos en el rescate y rehabilitación de animales maltratados. Ofrecemos atención médica, terapia y amor para que puedan ser adoptados.",
            "Somos un santuario animal que brinda refugio permanente a animales que han sido abandonados o maltratados. Creemos en segundas oportunidades y en el poder del amor incondicional."
        ];
        return $this->faker->randomElement($misiones);
    }

    // ========== MÉTODOS DE ESTADO ==========

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

    public function enCiudad(string $ciudad): static
    {
        return $this->state(fn (array $attributes) => [
            'ciudad' => $ciudad,
            'lat' => $this->faker->latitude(4.0, 4.9),
            'lng' => $this->faker->longitude(-74.5, -73.8),
        ]);
    }

    public function conCapacidad(int $min, int $max): static
    {
        return $this->state(fn (array $attributes) => [
            'capacidad_maxima' => $this->faker->numberBetween($min, $max),
        ]);
    }

    public function conNecesidades(array $necesidades): static
    {
        return $this->state(fn (array $attributes) => [
            'necesidades_actuales' => json_encode($necesidades),
        ]);
    }
}
