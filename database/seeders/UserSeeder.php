<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // 1. PERFIL ADMINISTRADOR
            [
                'nombre' => 'Admin',
                'apellidos' => 'Principal',
                'biografia' => 'Administrador principal del sistema de adopción de mascotas. Encargado de verificar fundaciones y veterinarias.',
                'redes_sociales' => json_encode([
                    'facebook' => 'https://facebook.com/admin.principal',
                    'instagram' => 'https://instagram.com/admin_pets',
                    'linkedin' => 'https://linkedin.com/in/admin-principal'
                ]),
                'email' => 'admin@sistema.com',
                'password' => Hash::make('Admin123'),
                'tipo' => 'admin',
                'estado' => 'activo',
                'veces_reportado' => 0,
                'total_mascotas_adoptadas' => 0,
                'total_donaciones' => 0,
                'puntos' => 1000,
                'rango' => 'Administrador',
                'fecha_nacimiento' => '1990-01-15',
                'direccion' => 'Av. Principal 123, Oficina 5',
                'pais' => 'Colombia',
                'ciudad' => 'Bogotá',
                'codigo_postal' => '110111',
                'lat' => 4.7110,
                'lng' => -74.0721,
                'telefono' => '3001234567',
                'avatar' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459468/pexels-photo-23692686_gvwcyx.avif',
                'avatar_public_id' => 'avatars/admin_principal',
                'tipo_documento' => 'Cédula de Ciudadanía',
                'numero_documento' => '1234567890',
                'documento_verificado' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'telefono_verificado' => true,
                'preferencias_notificaciones' => json_encode([
                    'email' => true,
                    'sms' => true,
                    'push' => true,
                    'recordatorios' => true,
                    'ofertas' => false
                ]),
                'idioma' => 'es',
                'tema' => 'dark',
                'ultimo_acceso' => now(),
                'ultima_ip' => '192.168.1.1',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => null,
                'updated_by' => null
            ],

            // 2. PERFIL USUARIO NORMAL
            [
                'nombre' => 'María',
                'apellidos' => 'González Pérez',
                'biografia' => 'Amante de los animales con 3 mascotas adoptadas. Busco ayudar a que más peluditos encuentren un hogar.',
                'redes_sociales' => json_encode([
                    'facebook' => 'https://facebook.com/maria.gonzalez',
                    'instagram' => 'https://instagram.com/maria_adopta',
                    'tiktok' => 'https://tiktok.com/@maria_pets'
                ]),
                'email' => 'maria@email.com',
                'password' => Hash::make('Usuario123'),
                'tipo' => 'user',
                'estado' => 'activo',
                'veces_reportado' => 0,
                'total_mascotas_adoptadas' => 3,
                'total_donaciones' => 150000.00,
                'puntos' => 450,
                'rango' => 'Adoptador Experto',
                'fecha_nacimiento' => '1995-05-20',
                'direccion' => 'Calle del Sol 45, Apto 202',
                'pais' => 'Colombia',
                'ciudad' => 'Medellín',
                'codigo_postal' => '050021',
                'lat' => 6.2442,
                'lng' => -75.5812,
                'telefono' => '3009876543',
                'avatar' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459467/pexels-photo-29862005_dyimeq.avif',
                'avatar_public_id' => 'avatars/maria_gonzalez',
                'tipo_documento' => 'Cédula de Ciudadanía',
                'numero_documento' => '1098765432',
                'documento_verificado' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'telefono_verificado' => true,
                'preferencias_notificaciones' => json_encode([
                    'email' => true,
                    'sms' => false,
                    'push' => true,
                    'recordatorios' => true,
                    'ofertas' => true
                ]),
                'idioma' => 'es',
                'tema' => 'light',
                'ultimo_acceso' => now(),
                'ultima_ip' => '192.168.1.45',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1
            ],

            // 3. PERFIL FUNDACIÓN
            [
                'nombre' => 'Fundación',
                'apellidos' => 'Patitas Felices',
                'biografia' => 'Fundación dedicada al rescate y rehabilitación de animales en situación de calle. Llevamos 10 años ayudando a peluditos.',
                'redes_sociales' => json_encode([
                    'facebook' => 'https://facebook.com/patitasfelices',
                    'instagram' => 'https://instagram.com/patitas_felices',
                    'youtube' => 'https://youtube.com/@patitasfelices',
                    'twitter' => 'https://twitter.com/patitasfelices'
                ]),
                'email' => 'fundacion@ejemplo.com',
                'password' => Hash::make('Fundacion123'),
                'tipo' => 'fundacion',
                'estado' => 'activo',
                'veces_reportado' => 0,
                'total_mascotas_adoptadas' => 245,
                'total_donaciones' => 1250000.00,
                'puntos' => 2500,
                'rango' => 'Fundación Verificada',
                'fecha_nacimiento' => null,
                'direccion' => 'Carrera 25 # 15-30, Barrio Los Álamos',
                'pais' => 'Colombia',
                'ciudad' => 'Cali',
                'codigo_postal' => '760001',
                'lat' => 3.4516,
                'lng' => -76.5320,
                'telefono' => '3123456789',
                'avatar' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459465/pexels-photo-6235116_xenwoa.avif',
                'avatar_public_id' => 'avatars/fundacion_patitas',
                'tipo_documento' => 'NIT',
                'numero_documento' => '900123456-7',
                'documento_verificado' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'telefono_verificado' => true,
                'preferencias_notificaciones' => json_encode([
                    'email' => true,
                    'sms' => true,
                    'push' => false,
                    'recordatorios' => true,
                    'ofertas' => false
                ]),
                'idioma' => 'es',
                'tema' => 'light',
                'ultimo_acceso' => now()->subHours(2),
                'ultima_ip' => '186.116.45.23',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1
            ],

            // 4. PERFIL VETERINARIA
            [
                'nombre' => 'Veterinaria',
                'apellidos' => 'San Antonio',
                'biografia' => 'Clínica veterinaria con más de 15 años de experiencia. Ofrecemos servicios médicos de alta calidad para tus mascotas.',
                'redes_sociales' => json_encode([
                    'facebook' => 'https://facebook.com/vetsanantonio',
                    'instagram' => 'https://instagram.com/vet_sanantonio',
                    'linkedin' => 'https://linkedin.com/company/veterinaria-san-antonio'
                ]),
                'email' => 'veterinaria@ejemplo.com',
                'password' => Hash::make('Veterinaria123'),
                'tipo' => 'veterinaria',
                'estado' => 'activo',
                'veces_reportado' => 0,
                'total_mascotas_adoptadas' => 0,
                'total_donaciones' => 500000.00,
                'puntos' => 1800,
                'rango' => 'Clínica Verificada',
                'fecha_nacimiento' => null,
                'direccion' => 'Calle 72 # 10-42, Chapinero',
                'pais' => 'Colombia',
                'ciudad' => 'Bogotá',
                'codigo_postal' => '110221',
                'lat' => 4.6351,
                'lng' => -74.0635,
                'telefono' => '6017456789',
                'avatar' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459465/pexels-photo-6816858_o6opp2.avif',
                'avatar_public_id' => 'avatars/veterinaria_san_antonio',
                'tipo_documento' => 'NIT',
                'numero_documento' => '901234567-8',
                'documento_verificado' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'telefono_verificado' => true,
                'preferencias_notificaciones' => json_encode([
                    'email' => true,
                    'sms' => false,
                    'push' => true,
                    'recordatorios' => true,
                    'ofertas' => false
                ]),
                'idioma' => 'es',
                'tema' => 'light',
                'ultimo_acceso' => now()->subHours(5),
                'ultima_ip' => '190.85.123.67',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1
            ]
        ];

        $userProfiles = [
            ['nombre' => 'Juan', 'apellidos' => 'Martínez López', 'email' => 'juan.martinez@ejemplo.com', 'telefono' => '3001112223'],
            ['nombre' => 'Catalina', 'apellidos' => 'Ramírez Castro', 'email' => 'catalina.ramirez@ejemplo.com', 'telefono' => '3001112224'],
            ['nombre' => 'Andrés', 'apellidos' => 'Gómez Rojas', 'email' => 'andres.gomez@ejemplo.com', 'telefono' => '3001112225'],
            ['nombre' => 'Laura', 'apellidos' => 'Torres Vega', 'email' => 'laura.torres@ejemplo.com', 'telefono' => '3001112226'],
            ['nombre' => 'David', 'apellidos' => 'Rojas Molina', 'email' => 'david.rojas@ejemplo.com', 'telefono' => '3001112227'],
            ['nombre' => 'Sofía', 'apellidos' => 'Vargas Mendoza', 'email' => 'sofia.vargas@ejemplo.com', 'telefono' => '3001112228'],
            ['nombre' => 'Nicolás', 'apellidos' => 'Méndez Cruz', 'email' => 'nicolas.mendez@ejemplo.com', 'telefono' => '3001112229'],
            ['nombre' => 'Gabriela', 'apellidos' => 'Castro Peña', 'email' => 'gabriela.castro@ejemplo.com', 'telefono' => '3001112230'],
            ['nombre' => 'Felipe', 'apellidos' => 'Díaz Muñoz', 'email' => 'felipe.diaz@ejemplo.com', 'telefono' => '3001112231'],
            ['nombre' => 'Camila', 'apellidos' => 'Gutiérrez Pardo', 'email' => 'camila.gutierrez@ejemplo.com', 'telefono' => '3001112232']
        ];

        foreach ($userProfiles as $profile) {
            $users[] = [
                'nombre' => $profile['nombre'],
                'apellidos' => $profile['apellidos'],
                'biografia' => 'Usuario interesado en adopción responsable y cuidado de mascotas.',
                'redes_sociales' => json_encode([
                    'facebook' => 'https://facebook.com/' . strtolower(str_replace(' ', '', $profile['nombre'])) . '.',
                    'instagram' => 'https://instagram.com/' . strtolower(str_replace(' ', '', $profile['nombre'])) . '_pets'
                ]),
                'email' => $profile['email'],
                'password' => Hash::make('Usuario123'),
                'tipo' => 'user',
                'estado' => 'activo',
                'veces_reportado' => 0,
                'total_mascotas_adoptadas' => rand(0, 5),
                'total_donaciones' => rand(0, 200000),
                'puntos' => rand(100, 900),
                'rango' => 'Adoptador',
                'fecha_nacimiento' => '1990-01-01',
                'direccion' => 'Calle ' . rand(1, 150) . ' # ' . rand(1, 100) . '-' . rand(1, 99),
                'pais' => 'Colombia',
                'ciudad' => 'Bogotá',
                'codigo_postal' => '110111',
                'lat' => 4.6 + (rand(0, 100) / 1000),
                'lng' => -74.0 - (rand(0, 100) / 1000),
                'telefono' => $profile['telefono'],
                'avatar' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459464/pexels-photo-6234635_f6ewby.avif',
                'avatar_public_id' => 'avatars/usuario_' . strtolower($profile['nombre']),
                'tipo_documento' => 'Cédula de Ciudadanía',
                'numero_documento' => '1' . rand(10000000, 99999999),
                'documento_verificado' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'telefono_verificado' => true,
                'preferencias_notificaciones' => json_encode([
                    'email' => true,
                    'sms' => true,
                    'push' => true,
                    'recordatorios' => true,
                    'ofertas' => false
                ]),
                'idioma' => 'es',
                'tema' => 'light',
                'ultimo_acceso' => now()->subDays(rand(1, 30)),
                'ultima_ip' => '190.000.' . rand(1, 255) . '.' . rand(1, 255),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1
            ];
        }

        $fundacionProfiles = [
            ['nombre' => 'Fundación', 'apellidos' => 'Huellitas de Vida', 'email' => 'fundacion1@ejemplo.com', 'telefono' => '3101112223'],
            ['nombre' => 'Fundación', 'apellidos' => 'Amigos Peludos', 'email' => 'fundacion2@ejemplo.com', 'telefono' => '3101112224'],
            ['nombre' => 'Fundación', 'apellidos' => 'Corazón Animal', 'email' => 'fundacion3@ejemplo.com', 'telefono' => '3101112225'],
            ['nombre' => 'Fundación', 'apellidos' => 'Rescate Patitas', 'email' => 'fundacion4@ejemplo.com', 'telefono' => '3101112226'],
            ['nombre' => 'Fundación', 'apellidos' => 'Esperanza Animal', 'email' => 'fundacion5@ejemplo.com', 'telefono' => '3101112227'],
            ['nombre' => 'Fundación', 'apellidos' => 'Sonrisas Felinas', 'email' => 'fundacion6@ejemplo.com', 'telefono' => '3101112228'],
            ['nombre' => 'Fundación', 'apellidos' => 'Manos Solidarias', 'email' => 'fundacion7@ejemplo.com', 'telefono' => '3101112229'],
            ['nombre' => 'Fundación', 'apellidos' => 'Bienestar Canino', 'email' => 'fundacion8@ejemplo.com', 'telefono' => '3101112230'],
            ['nombre' => 'Fundación', 'apellidos' => 'Vida y Amor Animal', 'email' => 'fundacion9@ejemplo.com', 'telefono' => '3101112231'],
            ['nombre' => 'Fundación', 'apellidos' => 'Refugio Seguro', 'email' => 'fundacion10@ejemplo.com', 'telefono' => '3101112232']
        ];

        foreach ($fundacionProfiles as $profile) {
            $users[] = [
                'nombre' => $profile['nombre'],
                'apellidos' => $profile['apellidos'],
                'biografia' => 'Organización dedicada al rescate y cuidado de animales vulnerables.',
                'redes_sociales' => json_encode([
                    'facebook' => 'https://facebook.com/' . strtolower(str_replace(' ', '', $profile['apellidos'])),
                    'instagram' => 'https://instagram.com/' . strtolower(str_replace(' ', '', $profile['apellidos']))
                ]),
                'email' => $profile['email'],
                'password' => Hash::make('Fundacion123'),
                'tipo' => 'fundacion',
                'estado' => 'activo',
                'veces_reportado' => 0,
                'total_mascotas_adoptadas' => rand(20, 250),
                'total_donaciones' => rand(500000, 2000000),
                'puntos' => rand(1000, 3000),
                'rango' => 'Fundación Verificada',
                'fecha_nacimiento' => null,
                'direccion' => 'Carrera ' . rand(10, 90) . ' # ' . rand(10, 90) . '-' . rand(1, 50),
                'pais' => 'Colombia',
                'ciudad' => 'Cali',
                'codigo_postal' => '760001',
                'lat' => 3.45 + (rand(0, 50) / 1000),
                'lng' => -76.53 + (rand(0, 50) / 1000),
                'telefono' => $profile['telefono'],
                'avatar' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459465/pexels-photo-7474859_jsc2ob.avif',
                'avatar_public_id' => 'avatars/fundacion_' . strtolower(str_replace(' ', '_', $profile['apellidos'])),
                'tipo_documento' => 'NIT',
                'numero_documento' => '900' . rand(100000, 999999) . '-1',
                'documento_verificado' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'telefono_verificado' => true,
                'preferencias_notificaciones' => json_encode([
                    'email' => true,
                    'sms' => true,
                    'push' => false,
                    'recordatorios' => true,
                    'ofertas' => false
                ]),
                'idioma' => 'es',
                'tema' => 'light',
                'ultimo_acceso' => now()->subDays(rand(1, 30)),
                'ultima_ip' => '186.1.' . rand(1, 255) . '.' . rand(1, 255),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1
            ];
        }

        $veterinariaProfiles = [
            ['nombre' => 'Clínica', 'apellidos' => 'Vet Sana', 'email' => 'veterinaria1@ejemplo.com', 'telefono' => '3111112223'],
            ['nombre' => 'Clínica', 'apellidos' => 'Animalia', 'email' => 'veterinaria2@ejemplo.com', 'telefono' => '3111112224'],
            ['nombre' => 'Veterinaria', 'apellidos' => 'Vida Salud', 'email' => 'veterinaria3@ejemplo.com', 'telefono' => '3111112225'],
            ['nombre' => 'Clínica', 'apellidos' => 'Mascotas Felices', 'email' => 'veterinaria4@ejemplo.com', 'telefono' => '3111112226'],
            ['nombre' => 'Veterinaria', 'apellidos' => 'Buena Salud', 'email' => 'veterinaria5@ejemplo.com', 'telefono' => '3111112227'],
            ['nombre' => 'Clínica', 'apellidos' => 'Peludos', 'email' => 'veterinaria6@ejemplo.com', 'telefono' => '3111112228'],
            ['nombre' => 'Veterinaria', 'apellidos' => 'San Felipe', 'email' => 'veterinaria7@ejemplo.com', 'telefono' => '3111112229'],
            ['nombre' => 'Clínica', 'apellidos' => 'Patas Seguras', 'email' => 'veterinaria8@ejemplo.com', 'telefono' => '3111112230'],
            ['nombre' => 'Veterinaria', 'apellidos' => 'Las Palmas', 'email' => 'veterinaria9@ejemplo.com', 'telefono' => '3111112231'],
            ['nombre' => 'Clínica', 'apellidos' => 'Cerro Verde', 'email' => 'veterinaria10@ejemplo.com', 'telefono' => '3111112232']
        ];

        foreach ($veterinariaProfiles as $profile) {
            $users[] = [
                'nombre' => $profile['nombre'],
                'apellidos' => $profile['apellidos'],
                'biografia' => 'Clínica veterinaria especializada en atención integral para mascotas.',
                'redes_sociales' => json_encode([
                    'facebook' => 'https://facebook.com/' . strtolower(str_replace(' ', '', $profile['apellidos'])),
                    'instagram' => 'https://instagram.com/' . strtolower(str_replace(' ', '', $profile['apellidos']))
                ]),
                'email' => $profile['email'],
                'password' => Hash::make('Veterinaria123'),
                'tipo' => 'veterinaria',
                'estado' => 'activo',
                'veces_reportado' => 0,
                'total_mascotas_adoptadas' => rand(0, 20),
                'total_donaciones' => rand(100000, 700000),
                'puntos' => rand(1200, 2800),
                'rango' => 'Clínica Verificada',
                'fecha_nacimiento' => null,
                'direccion' => 'Carrera ' . rand(20, 99) . ' # ' . rand(10, 90) . '-' . rand(1, 50),
                'pais' => 'Colombia',
                'ciudad' => 'Bogotá',
                'codigo_postal' => '110221',
                'lat' => 4.63 + (rand(0, 50) / 1000),
                'lng' => -74.06 + (rand(0, 50) / 1000),
                'telefono' => $profile['telefono'],
                'avatar' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459464/pexels-photo-6234980_eiyrcc.avif',
                'avatar_public_id' => 'avatars/veterinaria_' . strtolower(str_replace(' ', '_', $profile['apellidos'])),
                'tipo_documento' => 'NIT',
                'numero_documento' => '901' . rand(100000, 999999) . '-1',
                'documento_verificado' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
                'telefono_verificado' => true,
                'preferencias_notificaciones' => json_encode([
                    'email' => true,
                    'sms' => false,
                    'push' => true,
                    'recordatorios' => true,
                    'ofertas' => false
                ]),
                'idioma' => 'es',
                'tema' => 'light',
                'ultimo_acceso' => now()->subDays(rand(1, 30)),
                'ultima_ip' => '190.85.' . rand(1, 255) . '.' . rand(1, 255),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1
            ];
        }

        DB::table('users')->insert($users);
    }
}
