<?php

namespace Database\Factories;

use App\Models\Veterinaria;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as FakerFactory;

class VeterinariaFactory extends Factory
{
    protected $model = Veterinaria::class;

    // Configurar Faker en español colombiano
    protected function withFaker()
    {
        return FakerFactory::create('es_CO');
    }

    // URLs base de Cloudinary para logos
    private $logosCloudinary = [
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459468/pexels-photo-23692686_gvwcyx.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459467/pexels-photo-29862005_dyimeq.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459467/pexels-photo-30577796_dbjwod.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459466/pexels-photo-19490053_pke9pq.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459465/pexels-photo-6235116_xenwoa.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459465/pexels-photo-6816858_o6opp2.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459464/pexels-photo-6234635_f6ewby.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459465/pexels-photo-7474859_jsc2ob.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459464/pexels-photo-6234980_eiyrcc.avif',
        'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459463/pexels-photo-5731861_xlofmm.avif'
    ];

    private $galeriaCloudinary = [
        'https://res.cloudinary.com/demo/image/upload/sample.jpg',
        'https://res.cloudinary.com/demo/image/upload/dog.jpg',
        'https://res.cloudinary.com/demo/image/upload/cat.jpg',
        'https://res.cloudinary.com/demo/image/upload/sample_people.jpg',
        'https://res.cloudinary.com/demo/image/upload/cld-sample-4.jpg',
        'https://res.cloudinary.com/demo/image/upload/cld-sample-5.jpg',
    ];

    // 🆕 Array con 20 descripciones en español
    private $descripciones = [
        "Somos una clínica veterinaria con más de 15 años de experiencia en el cuidado integral de mascotas. Contamos con instalaciones modernas, equipos de diagnóstico de última generación y un equipo de profesionales apasionados por la salud animal. Ofrecemos servicios de consulta general, vacunación, cirugía y hospitalización las 24 horas.",

        "En nuestra veterinaria entendemos que tu mascota es parte de la familia. Por eso, brindamos atención personalizada y de calidad con un enfoque en la medicina preventiva. Realizamos chequeos regulares, vacunación, desparasitación y contamos con especialistas en todas las áreas de la medicina veterinaria.",

        "Centro veterinario especializado en el bienestar de perros y gatos. Nuestro compromiso es ofrecer servicios médicos de excelencia con calidez humana. Contamos con quirófano equipado, laboratorio clínico, radiología digital y ecografías para diagnósticos precisos y tratamientos efectivos.",

        "Clínica veterinaria con más de 20 años de trayectoria en el sector. Somos referentes en cirugías especializadas, tratamientos oncológicos y medicina interna. Nuestro equipo está en constante actualización para ofrecer las mejores soluciones médicas para tu mascota.",

        "Veterinaria comprometida con la salud pública y el bienestar animal. Realizamos campañas de esterilización, vacunación gratuita y jornadas de educación para dueños responsables. Además, ofrecemos servicios de urgencias 24/7 para atender cualquier emergencia.",

        "Hospital veterinario de alta complejidad con tecnología de punta. Contamos con unidad de cuidados intensivos, área de aislamiento, sala de rayos X digital y ecógrafo Doppler. Nuestros especialistas en neurología, traumatología y oftalmología veterinaria garantizan la mejor atención.",

        "Somos una veterinaria familiar que ofrece atención médica integral para mascotas. Nos especializamos en medicina preventiva, odontología veterinaria y nutrición animal. Creemos que una mascota saludable es una mascota feliz, y trabajamos cada día para lograrlo.",

        "Clínica veterinaria con enfoque holístico en la salud animal. Combinamos la medicina convencional con terapias alternativas como acupuntura, fisioterapia y rehabilitación. Nuestro objetivo es proporcionar una vida larga y saludable a tus compañeros de vida.",

        "Centro de salud animal con instalaciones de primer nivel. Disponemos de consultorios totalmente equipados, sala de hospitalización con monitoreo constante y equipo de cirugía laparoscópica. Brindamos atención personalizada y seguimiento médico continuo.",

        "Veterinaria especializada en animales exóticos y de compañía. Atendemos perros, gatos, conejos, hurones, aves y reptiles. Nuestro equipo cuenta con amplia experiencia en especies no convencionales y estamos capacitados para ofrecer diagnósticos y tratamientos especializados.",

        "Clínica veterinaria con espíritu de servicio y amor por los animales. Ofrecemos paquetes de salud preventiva, planes de vacunación personalizados y descuentos en servicios para clientes frecuentes. Tu mascota recibirá atención de calidad a precios accesibles.",

        "Hospital veterinario con más de 30 años de experiencia en el cuidado animal. Somos pioneros en trasplantes de médula ósea, hemodiálisis y cirugías de columna en perros y gatos. Contamos con el respaldo de las mejores universidades y centros de investigación.",

        "Veterinaria moderna y dinámica que se adapta a las necesidades de tu mascota. Brindamos servicios de consulta a domicilio, urgencias móviles y telemedicina. Nuestro equipo está disponible 24 horas para resolver cualquier duda o emergencia que puedas tener.",

        "Centro veterinario especializado en reproducción y neonatología. Ofrecemos servicios de inseminación artificial, fertilización in vitro y seguimiento de gestación. Además, contamos con unidad de cuidados intensivos neonatales para cachorros y gatitos prematuros.",

        "Clínica veterinaria con enfoque en el cuidado geriátrico de mascotas. Entendemos las necesidades especiales de animales mayores y ofrecemos tratamientos para artritis, diabetes, enfermedades renales y cardíacas. Brindamos calidad de vida en la tercera edad de tu compañero.",

        "Veterinaria con servicios integrales de diagnóstico por imágenes. Contamos con resonancia magnética, tomografía computarizada, ecografía y radiología digital. Nuestros especialistas en diagnóstico garantizan la detección temprana de enfermedades.",

        "Centro de rehabilitación y fisioterapia veterinaria. Ofrecemos tratamientos de hidroterapia, láser terapéutico, electroacupuntura y ejercicios de rehabilitación. Ayudamos a mascotas con problemas de movilidad, recuperación postquirúrgica y condiciones crónicas.",

        "Clínica veterinaria ecológica comprometida con el medio ambiente. Utilizamos productos biodegradables, energías renovables y promovemos prácticas sostenibles. Además, ofrecemos servicios de salud preventiva y bienestar para tu mascota y el planeta.",

        "Veterinaria con servicio de urgencias y emergencias las 24 horas del día. Contamos con médicos veterinarios de guardia, equipo de transfusión sanguínea, ventilador mecánico y monitores multiparamétricos. Atendemos cualquier emergencia con rapidez y profesionalismo.",

        "Centro veterinario con programas de bienestar y nutrición personalizada. Realizamos asesorías dietéticas, planes de ejercicio y control de peso. Creemos en la importancia de una alimentación balanceada y un estilo de vida saludable para la felicidad de tu mascota."
    ];

    public function definition(): array
    {
        // Servicios en español
        $servicios = [
            'Consulta general', 'Vacunación', 'Cirugía', 'Hospitalización',
            'Laboratorio clínico', 'Radiología', 'Odontología veterinaria',
            'Peluquería y estética', 'Urgencias 24 horas', 'Ecografías',
            'Endoscopias', 'Fisioterapia'
        ];

        $serviciosSeleccionados = $this->faker->randomElements($servicios, $this->faker->numberBetween(3, 6));

        // Nombres de veterinarias en español
        $prefijos = ['Clínica', 'Hospital', 'Centro', 'Consultorio', 'Unidad', 'Instituto', 'Servicio'];
        $nombres = ['Veterinario', 'Animal', 'Mascota', 'Canino', 'Felino', 'Vet', 'Pet', 'Animales'];
        $sufijos = ['del Parque', 'del Centro', 'del Norte', 'del Sur', 'del Bosque', 'de la Montaña', 'de los Andes'];

        $nombreCompleto = $this->faker->randomElement($prefijos) . ' ' .
                         $this->faker->randomElement($nombres) . ' ' .
                         $this->faker->randomElement($sufijos);

        // Ciudades colombianas
        $ciudades = ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena', 'Bucaramanga',
                    'Pereira', 'Manizales', 'Pasto', 'Cúcuta', 'Ibagué', 'Villavicencio', 'Santa Marta'];

        $departamentos = ['Cundinamarca', 'Antioquia', 'Valle del Cauca', 'Atlántico', 'Bolívar',
                         'Santander', 'Risaralda', 'Caldas', 'Nariño', 'Norte de Santander', 'Tolima'];

        // Horarios en español
        $horarios = [
            'Lun-Vie: 8:00 AM - 7:00 PM, Sáb: 9:00 AM - 2:00 PM',
            'Lun-Sáb: 7:00 AM - 8:00 PM, Dom: 8:00 AM - 12:00 PM',
            'Lun-Vie: 8:30 AM - 6:30 PM, Sáb: 9:00 AM - 1:00 PM',
            'Lun-Sáb: 8:00 AM - 7:00 PM (24/7 para urgencias)',
            'Lun-Vie: 7:30 AM - 8:00 PM, Sáb: 8:00 AM - 4:00 PM'
        ];

        return [
            'Nombre_vet' => $nombreCompleto,
            'descripcion' => $this->faker->randomElement($this->descripciones), // 🆕 Descripción en español
            'Direccion' => $this->faker->unique()->streetAddress(),
            'Telefono' => $this->faker->unique()->phoneNumber(),
            'Email' => $this->faker->unique()->companyEmail(),
            'servicios' => json_encode($serviciosSeleccionados),
            'servicios_detallados' => json_encode([
                ['nombre' => 'Consulta', 'precio' => $this->faker->randomFloat(2, 30000, 70000)],
                ['nombre' => 'Vacuna', 'precio' => $this->faker->randomFloat(2, 20000, 50000)],
                ['nombre' => 'Desparasitación', 'precio' => $this->faker->randomFloat(2, 15000, 35000)],
            ]),
            'equipo_medico' => json_encode([
                'veterinarios' => $this->faker->numberBetween(2, 10),
                'asistentes' => $this->faker->numberBetween(1, 5),
                'equipos' => ['Ultrasonido', 'Rayos X', 'Laboratorio']
            ]),
            'horario_atencion' => $this->faker->randomElement($horarios),
            'anios_experiencia' => $this->faker->numberBetween(1, 30),
            'urgencias_24h' => $this->faker->boolean(30),
            'convenios' => json_encode($this->faker->randomElements(['Arus', 'Seguros Bolívar', 'Sura', 'Allianz'], $this->faker->numberBetween(0, 3))),
            'precio_consulta' => $this->faker->randomFloat(2, 25000, 80000),
            'acepta_seguros' => $this->faker->boolean(50),
            'valoracion_promedio' => $this->faker->randomFloat(2, 3, 5),
            'total_valoraciones' => $this->faker->numberBetween(1, 500),
            'logo' => $this->faker->randomElement($this->logosCloudinary),
            'logo_public_id' => 'veterinarias/logo_' . $this->faker->uuid(),
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
            'ciudad' => $this->faker->randomElement($ciudades),
            'departamento' => $this->faker->randomElement($departamentos),
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
