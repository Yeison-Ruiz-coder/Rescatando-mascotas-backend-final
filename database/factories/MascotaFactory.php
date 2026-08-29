<?php

namespace Database\Factories;

use App\Models\Mascota;
use App\Models\Fundacion;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class MascotaFactory extends Factory
{
    protected $model = Mascota::class;

    private static array $imagenesUsadas = [];
    private static array $imagenesUsadasPorCategoria = [];

    public static function resetImagenesUsadas(): void
    {
        self::$imagenesUsadas = [];
        self::$imagenesUsadasPorCategoria = [];
    }

    public static function imagenesUsadas(): array
    {
        return self::$imagenesUsadas;
    }

    public static function imagenesDisponibles(): array
    {
        $imagenes = [];

        foreach (['perro', 'gato', 'conejo', 'ave'] as $categoria) {
            $imagenes = array_merge($imagenes, self::imagenesDisponiblesPorCategoria($categoria));
        }

        return array_values(array_unique($imagenes));
    }

    public static function imagenesDisponiblesPorCategoria(string $categoria): array
    {
        $categoriaKey = strtolower($categoria);

        $stock = [
            'perro' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1778139768/mascotas/fcnrvrzellqqdj0xecdv.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462481/e82a50e9587cf0292e16d8fd3752e048_-_copia_xyoezz.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462479/beneficios_de_tener_una_mascota-1_vnelpa.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462405/images_4_dlswmb.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462403/images_3_vtnxza.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462401/images_2_gpguag.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462400/images_1_kcf91v.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462399/657694610_222735447_1706x1280_kqophi.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782208985/mascotas/xq2ebzevgu0nygkqk9ff.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782207528/mascotas/dlweft4a9v5q2t4scoen.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782206342/mascotas/fr4yu8ffo81zxqklnlnc.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782203952/mascotas/vce5r3wzcip8zhekdeio.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782156877/mascotas/x4ozee3asmd19qcfskjx.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1781490997/mascotas/higwgw7jdonppt1uwdhl.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1780601117/mascotas/ttdajdacngbu5nunxb09.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1779143939/mascotas/d4cjxmx6varszz0xw9qy.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1778442009/mascotas/n2cy2qozjwhqdjtpjnel.jpg',
            ],
            'gato' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1779298823/mascotas/a7xp51aaiu8pvjtybjsj.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462752/images_12_hkjek9.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462750/images_11_bdkono.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462749/images_10_irr7yi.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462747/images_9_xhpupm.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462745/images_8_exevdy.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462743/images_7_ug9gnv.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462741/images_6_l1lxaw.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462739/images_5_urnxg2.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462738/images_4_jrelg9.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462736/images_3_xnyw7a.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462536/OIP_flvjr2.webp',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462534/gatti-siamesi-immagini-e-foto-1_wcnv1m.webp',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782161863/mascotas/zvykvgr7ianq7qbwzllk.jpg',
            ],
            'conejo' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1779298999/mascotas/ydopizlj9e4y8siev2yk.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462512/images_qmuduk.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462510/images_13_wu7tep.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462508/images_2_g76ows.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462507/images_1_i79lg4.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782375283/mascotas/civnnmrzfl9uezhiaeje.jpg',
            ],
            'ave' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782463294/hapalopsittaca-fuertesi1_gl5pqj.webp',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782463288/cockatiel-4794968_1920_g8lbn3.webp',
            ],
        ];

        return $stock[$categoriaKey] ?? $stock['perro'];
    }

    public static function obtenerImagenUnica(?string $categoria = 'perro'): string
    {
        $categoriaKey = strtolower($categoria ?: 'perro');
        $usadas = self::$imagenesUsadasPorCategoria[$categoriaKey] ?? [];
        $disponibles = array_values(array_diff(self::imagenesDisponiblesPorCategoria($categoriaKey), $usadas));

        if (empty($disponibles)) {
            self::$imagenesUsadasPorCategoria[$categoriaKey] = [];
            $disponibles = self::imagenesDisponiblesPorCategoria($categoriaKey);
        }

        $imagen = $disponibles[array_rand($disponibles)];
        self::$imagenesUsadasPorCategoria[$categoriaKey][] = $imagen;
        self::$imagenesUsadas[] = $imagen;

        return $imagen;
    }

    // URLs base de Cloudinary para diferentes tipos de fotos
    private $imagenesCloudinary = [
        // Fotos principales
        'principal' => [
            'perro' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1778139768/mascotas/fcnrvrzellqqdj0xecdv.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462481/e82a50e9587cf0292e16d8fd3752e048_-_copia_xyoezz.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462479/beneficios_de_tener_una_mascota-1_vnelpa.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462405/images_4_dlswmb.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462403/images_3_vtnxza.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462401/images_2_gpguag.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462400/images_1_kcf91v.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462399/657694610_222735447_1706x1280_kqophi.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782208985/mascotas/xq2ebzevgu0nygkqk9ff.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782207528/mascotas/dlweft4a9v5q2t4scoen.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782206342/mascotas/fr4yu8ffo81zxqklnlnc.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782203952/mascotas/vce5r3wzcip8zhekdeio.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782156877/mascotas/x4ozee3asmd19qcfskjx.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1781490997/mascotas/higwgw7jdonppt1uwdhl.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1780601117/mascotas/ttdajdacngbu5nunxb09.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1779143939/mascotas/d4cjxmx6varszz0xw9qy.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1778442009/mascotas/n2cy2qozjwhqdjtpjnel.jpg'
            ],
            'gato' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1779298823/mascotas/a7xp51aaiu8pvjtybjsj.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462752/images_12_hkjek9.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462750/images_11_bdkono.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462749/images_10_irr7yi.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462747/images_9_xhpupm.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462745/images_8_exevdy.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462743/images_7_ug9gnv.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462741/images_6_l1lxaw.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462739/images_5_urnxg2.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462738/images_4_jrelg9.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462736/images_3_xnyw7a.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462536/OIP_flvjr2.webp',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462534/gatti-siamesi-immagini-e-foto-1_wcnv1m.webp',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782161863/mascotas/zvykvgr7ianq7qbwzllk.jpg'
            ],
            'conejo' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1779298999/mascotas/ydopizlj9e4y8siev2yk.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462512/images_qmuduk.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462510/images_13_wu7tep.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462508/images_2_g76ows.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462507/images_1_i79lg4.jpg',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782375283/mascotas/civnnmrzfl9uezhiaeje.jpg'
            ],
            'ave' => [
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782463294/hapalopsittaca-fuertesi1_gl5pqj.webp',
                'https://res.cloudinary.com/dixyebg5i/image/upload/v1782463288/cockatiel-4794968_1920_g8lbn3.webp'
            ],
        ],
        // Fotos de cachorros/bebés
        'cachorros' => [
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462750/images_11_bdkono.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462749/images_10_irr7yi.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782462481/e82a50e9587cf0292e16d8fd3752e048_-_copia_xyoezz.jpg'
        ],
        // Fotos de acción/jugando
        'accion' => [
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1780966318/mascotas/izh6m04j0ratewpjrd79.jpg'
        ]
    ];

    private $publicIdsCloudinary = [
        'perro' => 'mascotas/perro_ejemplo',
        'gato' => 'mascotas/gato_ejemplo',
        'conejo' => 'mascotas/conejo_ejemplo',
        'ave' => 'mascotas/ave_ejemplo'
    ];

    protected function withFaker()
    {
        return FakerFactory::create('es_CO');
    }

    public function definition(): array
    {
        $especies = ['Perro', 'Gato', 'Conejo', 'Ave'];
        $especie = $this->faker->randomElement($especies);
        $imagenKey = strtolower($especie);

        // ✅ FECHAS CORREGIDAS USANDO CARBON DIRECTAMENTE
        $ahora = \Carbon\Carbon::now();

        // Fecha de ingreso: entre 8 meses atrás y hoy
        $fechaIngreso = \Carbon\Carbon::now()->subMonths(rand(0, 8))->addDays(rand(0, 30));

        // Fecha de publicación: entre fecha de ingreso y hoy
        $diasDesdeIngreso = $ahora->diffInDays($fechaIngreso);
        $diasParaPublicacion = $diasDesdeIngreso > 0 ? rand(0, $diasDesdeIngreso) : 0;
        $fechaPublicacion = (clone $fechaIngreso)->addDays($diasParaPublicacion);

        // Si la fecha de publicación es igual o anterior a la de ingreso, la ajustamos
        if ($fechaPublicacion <= $fechaIngreso) {
            $fechaPublicacion = (clone $fechaIngreso)->addDays(rand(1, 5));
        }

        $edad = $this->faker->randomFloat(2, 0.3, 15);
        $esCachorro = $edad < 1;

        return [
            // ===== INFORMACIÓN BÁSICA =====
            'nombre_mascota' => $this->generarNombreMascota($especie),
            'especie' => $especie,
            'edad_aprox' => $edad,
            'peso_aprox' => $this->generarPeso($especie),
            'tamano' => $this->generarTamanio($especie),
            'color' => $this->generarColorRealista(),
            'genero' => $this->faker->randomElement(['Macho', 'Hembra']),
            'estado' => $this->faker->randomElement(['En adopcion', 'Rescatada', 'En acogida']),

            // ===== UBICACIÓN Y DESCRIPCIÓN =====
            'lugar_rescate' => $this->generarLugarRescate(),
            'descripcion' => $this->generarDescripcion($especie, $edad),
            'condiciones_especiales' => $this->generarCondicionesEspeciales(),
            'salud_general' => $this->faker->randomElement([
                'Excelente - Mascota completamente sana y activa',
                'Buena - Presenta buen estado de salud general',
                'Regular - Requiere atención veterinaria periódica',
                'En recuperación - Está en proceso de tratamiento médico'
            ]),

            // ===== SALUD Y CUIDADOS =====
            'esterilizado' => $this->faker->boolean($edad > 0.8 ? 75 : 20),
            'vacunado' => $this->faker->boolean(85),
            'desparasitado' => $this->faker->boolean(90),
            'enfermedades_cronicas' => $this->generarEnfermedadesCronicas(),
            'medicamentos' => $this->generarMedicamentos(),

            // ===== FOTOS Y MULTIMEDIA =====
            'foto_principal' => self::obtenerImagenUnica($imagenKey),
            'foto_principal_public_id' => $this->publicIdsCloudinary[$imagenKey] ?? $this->publicIdsCloudinary['perro'],
            'video_url' => $this->faker->optional(0.3)->url(),
            'video_public_id' => $this->faker->optional(0.3)->slug(),

            // ===== CARACTERÍSTICAS Y COMPORTAMIENTO =====
            'necesita_hogar_temporal' => $this->faker->boolean(15),
            'apto_con_ninos' => $this->faker->boolean(80),
            'apto_con_otros_animales' => $this->faker->boolean(75),
            'requisitos_adopcion' => $this->generarRequisitosAdopcion($especie),
            'hogar_recomendado' => $this->generarHogarRecomendado($especie),

            // ===== FECHAS =====
            'fecha_ingreso' => $fechaIngreso,
            'fecha_publicacion' => $fechaPublicacion,
            'fecha_salida' => null,

            // ===== RELACIONES =====
            'fundacion_id' => Fundacion::inRandomOrder()->first()?->id ?? Fundacion::factory(),
            'veterinaria_id' => null,
            'created_by' => User::where('tipo', 'admin')->first()?->id ?? 1,
            'updated_by' => User::where('tipo', 'admin')->first()?->id ?? 1,

            // ===== MÉTRICAS Y ESTADÍSTICAS =====
            'destacada' => $this->faker->boolean(10),
            'vistas' => $this->faker->numberBetween(0, 800),
            'interesados' => $this->faker->numberBetween(0, 60),
            'padrinos' => $this->generarPadrinos(),
        ];
    }

    // ============ MÉTODOS AUXILIARES PARA GENERAR DATOS REALISTAS ============

    private function generarNombreMascota($especie): string
    {
        $nombresPorEspecie = [
            'Perro' => ['Max', 'Luna', 'Rocky', 'Bella', 'Toby', 'Nala', 'Bruno', 'Kiara', 'Simba', 'Maya'],
            'Gato' => ['Michi', 'Luna', 'Simba', 'Nala', 'Tigre', 'Pelusa', 'Canela', 'Milo', 'Cleo', 'Olivia'],
            'Conejo' => ['Copito', 'Copo', 'Nieve', 'Peluchín', 'Mimoso', 'Lola', 'Tito', 'Mimosa', 'Algodón'],
            'Ave' => ['Piolín', 'Kiwi', 'Loro', 'Coco', 'Paco', 'Rita', 'Lola', 'Pícaro', 'Alas'],
        ];

        return $this->faker->randomElement($nombresPorEspecie[$especie] ?? ['Mascota']);
    }

    private function generarPeso($especie): float
    {
        $rangos = [
            'Perro' => [2, 45],
            'Gato' => [2.5, 8],
            'Conejo' => [0.8, 2.5],
            'Ave' => [0.05, 1.5],
        ];

        list($min, $max) = $rangos[$especie] ?? [1, 10];
        return $this->faker->randomFloat(2, $min, $max);
    }

    private function generarTamanio($especie): string
    {
        $tamanios = [
            'Perro' => ['pequeño', 'mediano', 'grande'],
            'Gato' => ['pequeño', 'mediano'],
            'Conejo' => ['pequeño', 'mediano'],
            'Ave' => ['pequeño'],
        ];

        return $this->faker->randomElement($tamanios[$especie] ?? ['mediano']);
    }

    private function generarColorRealista(): string
    {
        $colores = [
            'Blanco',
            'Negro',
            'Marrón',
            'Gris',
            'Café',
            'Beige',
            'Crema',
            'Dorado',
            'Plateado',
            'Atigrado',
            'Manchado',
            'Bicolor',
            'Tricolor',
            'Blanco con negro',
            'Marrón claro',
            'Gris oscuro',
            'Canela',
            'Naranja',
            'Atigrado naranja',
            'Azul grisáceo',
            'Lila'
        ];

        return $this->faker->randomElement($colores);
    }

    private function generarLugarRescate(): string
    {
        $lugares = [
            'Calle principal, zona norte',
            'Parque central de la ciudad',
            'Barrio residencial',
            'Zona rural, camino viejo',
            'Cerca del mercado municipal',
            'Urbanización Los Pinos',
            'Avenida de los Álamos',
            'Callejón sin salida',
            'Terreno baldío en construcción',
            'Playa del este',
            'Cerro de las flores',
            'Valle de los sueños'
        ];

        $ciudades = ['Bogotá', 'Medellín', 'Cali', 'Barranquilla', 'Cartagena', 'Bucaramanga'];

        return $this->faker->randomElement($lugares) . ', ' . $this->faker->randomElement($ciudades);
    }

    private function generarDescripcion($especie, $edad): string
    {
        $personalidad = $this->faker->randomElement([
            'cariñoso',
            'juguetón',
            'tranquilo',
            'enérgico',
            'dócil',
            'inteligente',
            'obediente',
            'curioso',
            'leal',
            'protector',
            'sociable',
            'tímido',
            'aventurero',
            'travieso',
            'paciente'
        ]);

        $habilidades = $this->faker->randomElement([
            'aprende comandos básicos',
            'sabe sentarse y dar la pata',
            'camina con correa fácilmente',
            'convive bien con otros animales',
            'es muy limpio',
            'le gusta jugar con pelotas',
            'disfruta los paseos',
            'es muy cariñoso con los niños'
        ]);

        $edadTexto = $edad < 1 ? 'cachorro' : ($edad < 3 ? 'joven' : 'adulto');
        $hospedajeIdeal = $this->faker->randomElement([
            'familias',
            'personas solteras',
            'parejas',
            'personas mayores'
        ]);

        $historias = [
            'Llegó tras ser rescatado de la calle y ahora busca un hogar con tranquilidad.',
            'Fue encontrado en buen estado y está listo para adaptarse a una nueva familia.',
            'Ha demostrado ser muy cariñoso y le encanta recibir atención.',
            'Se lleva bien con otros animales y disfruta de los abrazos.',
            'Tiene una gran energía y será el compañero perfecto para paseos diarios.'
        ];

        return "{$especie} {$edadTexto} de aproximadamente {$edad} años, muy {$personalidad}. " .
            "{$this->faker->randomElement($historias)} " .
            "Características especiales: {$habilidades}. " .
            "Busca un hogar donde pueda recibir todo el amor y cuidado que merece. " .
            "Es un compañero ideal para {$hospedajeIdeal}. " .
            $this->faker->optional(0.7)->randomElement($historias);
    }

    private function generarCondicionesEspeciales(): ?string
    {
        $condiciones = [
            'Requiere alimentación especial sin granos',
            'Necesita medicación diaria para la tiroides',
            'Tiene alergia a ciertos alimentos',
            'Requiere baños especiales por condición de piel',
            'Necesita ejercicios moderados por problemas articulares',
            'Requerirá cirugía de cadera en el futuro',
            'Tiene displasia leve de cadera',
            'Presenta ansiedad por separación',
            'Requiere atención dental regular',
            'Necesita suplementos vitamínicos'
        ];

        return $this->faker->optional(0.3)->randomElement($condiciones);
    }

    private function generarEnfermedadesCronicas(): ?array
    {
        $enfermedades = [
            'Diabetes tipo 1',
            'Artritis',
            'Alergias estacionales',
            'Problemas cardíacos',
            'Problemas renales',
            'Hipotiroidismo',
            'Epilepsia',
            'Asma',
            'Dermatitis atópica',
            'Problemas digestivos'
        ];

        $enfermedadesMascota = [];
        $numEnfermedades = $this->faker->numberBetween(0, 2);

        for ($i = 0; $i < $numEnfermedades; $i++) {
            $enfermedad = $this->faker->randomElement($enfermedades);
            if (!in_array($enfermedad, $enfermedadesMascota)) {
                $enfermedadesMascota[] = $enfermedad;
            }
        }

        return empty($enfermedadesMascota) ? null : $enfermedadesMascota;
    }

    private function generarMedicamentos(): ?array
    {
        $medicamentos = [
            'Antibióticos (Amoxicilina)',
            'Analgésicos (Meloxicam)',
            'Antiinflamatorios (Carprofeno)',
            'Vitaminas y suplementos',
            'Antiparasitarios',
            'Antihistamínicos',
            'Corticoides',
            'Insulina para diabetes',
            'Levetiracetam para epilepsia',
            'Levotiroxina para hipotiroidismo'
        ];

        $medicamentosMascota = [];
        $numMedicamentos = $this->faker->numberBetween(0, 2);

        for ($i = 0; $i < $numMedicamentos; $i++) {
            $medicamento = $this->faker->randomElement($medicamentos);
            if (!in_array($medicamento, $medicamentosMascota)) {
                $medicamentosMascota[] = $medicamento;
            }
        }

        return empty($medicamentosMascota) ? null : $medicamentosMascota;
    }

    private function generarRequisitosAdopcion($especie): array
    {
        $requisitosBase = [
            'Completar formulario de adopción',
            'Entrevista con el equipo de adopciones',
            'Visita domiciliaria para evaluar el entorno',
            'Compromiso de cuidado veterinario',
            'Firma de contrato de adopción responsable',
            'Seguro de responsabilidad civil opcional'
        ];

        $requisitosEspecie = [
            'Perro' => [
                'Espacio adecuado para pasear',
                'Compromiso de paseos diarios',
                'Cerca o jardín seguro'
            ],
            'Gato' => [
                'Ventanas con malla de seguridad',
                'Arenero y rascadores disponibles',
                'Espacio vertical para trepar'
            ],
            'Conejo' => [
                'Conejera o espacio amplio',
                'Alimentación con heno de calidad',
                'Zona segura para ejercitarse'
            ],
            'Ave' => [
                'Jaula espaciosa con juguetes',
                'Tiempo fuera de la jaula diario',
                'Alimentación variada y balanceada'
            ]
        ];

        $requisitos = array_merge($requisitosBase, $requisitosEspecie[$especie] ?? []);

        $numRequisitos = $this->faker->numberBetween(3, 5);
        shuffle($requisitos);

        return array_slice($requisitos, 0, $numRequisitos);
    }

    private function generarHogarRecomendado($especie): string
    {
        $hogares = [
            'Perro' => ['Casa con jardín amplio', 'Casa con patio cercado', 'Finca', 'Casa en zona suburbana'],
            'Gato' => ['Departamento amplio con ventanales', 'Casa con acceso a exterior seguro', 'Cualquier hogar con espacios verticales'],
            'Conejo' => ['Casa con espacios interiores seguros', 'Casa con jardín cercado', 'Departamento con balcón protegido'],
            'Ave' => ['Casa con buena iluminación natural', 'Espacio tranquilo sin corrientes de aire', 'Hogar sin otros animales depredadores']
        ];

        return $this->faker->randomElement($hogares[$especie] ?? ['Hogar con espacio adecuado para la mascota']);
    }

    private function generarPadrinos(): array
    {
        $padrinosPosibles = [
            ['nombre' => 'Fundación Patitas Felices', 'tipo' => 'organización'],
            ['nombre' => 'Clínica Veterinaria San Miguel', 'tipo' => 'veterinaria'],
            ['nombre' => 'Tienda de Mascotas El Amigo', 'tipo' => 'comercio'],
            ['nombre' => 'Juan Pérez', 'tipo' => 'particular'],
            ['nombre' => 'María González', 'tipo' => 'particular'],
            ['nombre' => 'Carlos Rodríguez', 'tipo' => 'particular'],
            ['nombre' => 'Ana Martínez', 'tipo' => 'particular'],
            ['nombre' => 'Empresa PetCare SA', 'tipo' => 'empresa'],
            ['nombre' => 'Refugio La Esperanza', 'tipo' => 'organización']
        ];

        $numPadrinos = $this->faker->numberBetween(0, 3);
        shuffle($padrinosPosibles);

        return array_slice($padrinosPosibles, 0, $numPadrinos);
    }

    // ============ ESTADOS PERSONALIZADOS ============

    public function destacada(): static
    {
        return $this->state(fn(array $attributes) => [
            'destacada' => true,
            'vistas' => $this->faker->numberBetween(500, 5000),
            'interesados' => $this->faker->numberBetween(50, 200),
            'estado' => 'En adopcion'
        ]);
    }

    public function adoptada(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'Adoptado',
            'fecha_salida' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'destacada' => false,
        ]);
    }

    public function cachorro(): static
    {
        return $this->state(fn(array $attributes) => [
            'edad_aprox' => $this->faker->randomFloat(2, 0.3, 0.9),
            'peso_aprox' => $this->faker->randomFloat(2, 1, 8),
            'esterilizado' => false,
        ]);
    }

    public function conVideo(): static
    {
        return $this->state(fn(array $attributes) => [
            'video_url' => $this->faker->optional(0.3)->url(),
            'video_public_id' => 'mascotas/video_' . $this->faker->uuid(),
        ]);
    }

    public function conFundacion($fundacionId): static
    {
        return $this->state(fn(array $attributes) => [
            'fundacion_id' => $fundacionId,
        ]);
    }

    public function rescatada(): static
    {
        return $this->state(fn(array $attributes) => [
            'estado' => 'Rescatada',
            'fecha_ingreso' => $this->faker->dateTimeBetween('-2 weeks', 'now'),
            'salud_general' => 'En recuperación - Requiere atención especial',
        ]);
    }

    public function conNecesidadesEspeciales(): static
    {
        return $this->state(fn(array $attributes) => [
            'necesita_hogar_temporal' => true,
            'condiciones_especiales' => 'Requiere atención médica continua y medicación diaria',
            'medicamentos' => json_encode(['Medicación diaria', 'Suplementos alimenticios']),
            'salud_general' => 'Regular - Requiere atención veterinaria periódica',
        ]);
    }

    public function muyBuscada(): static
    {
        return $this->state(fn(array $attributes) => [
            'vistas' => $this->faker->numberBetween(300, 800),
            'interesados' => $this->faker->numberBetween(30, 80),
            'destacada' => true,
        ]);
    }

    public function conMuchasFotos(): static
    {
        return $this->state(function (array $attributes) {
            $especie = $attributes['especie'] ?? 'Perro';
            $imagenKey = strtolower($especie);

            return [
                'foto_principal' => self::obtenerImagenUnica($imagenKey),
            ];
        });
    }

}
