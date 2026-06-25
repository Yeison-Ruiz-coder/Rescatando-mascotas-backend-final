<?php

namespace App\Services\Entity;

use App\Models\Mascota;
use App\Models\Fundacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MascotaEntityService
{
    use ImageUploadTrait;

    // ============================================
    // 🔥 CONSTANTES DE CACHÉ
    // ============================================
    private const CACHE_TTL = 300; // 5 minutos
    private const CACHE_PREFIX = 'mascotas_fundacion_';

    // ============================================
    // 🔥 FUNDACIÓN CON CACHÉ
    // ============================================

    // En getFundacionCached()
    private function getFundacionCached()
    {
        $user = Auth::user();

        Log::info('🔍 getFundacionCached - Usuario:', [
            'id' => $user?->id,
            'tipo' => $user?->tipo,
            'email' => $user?->email,
        ]);

        if ($user->tipo !== 'fundacion') {
            Log::warning('⚠️ Usuario no es fundación:', ['tipo' => $user?->tipo]);
            return null;
        }

        $cacheKey = 'fundacion_user_' . $user->id;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            $fundacion = Fundacion::where('user_id', $user->id)->first();

            Log::info('🔍 Buscando fundación:', [
                'user_id' => $user->id,
                'found' => $fundacion ? 'SÍ' : 'NO',
                'fundacion_id' => $fundacion?->id,
            ]);

            if (!$fundacion) {
                $fundacion = Fundacion::create([
                    'Nombre_1' => $user->nombre ?? 'Fundación de ' . $user->email,
                    'user_id' => $user->id,
                    'Email' => $user->email,
                    'Direccion' => $user->direccion ?? 'Por definir',
                    'Telefono' => $user->telefono ?? '000000000',
                    'registro_sanitario' => 'PENDIENTE_' . $user->id,
                    'ciudad' => $user->ciudad ?? null,
                    'recibe_voluntarios' => false,
                    'capacidad_maxima' => 0,
                ]);
                Log::info('✅ Fundación creada:', ['id' => $fundacion->id]);
            }

            return $fundacion;
        });
    }

    public function getEntidad()
    {
        $user = Auth::user();

        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        return null;
    }

    public function getFundacion()
    {
        return $this->getFundacionCached();
    }

    // ============================================
    // 🔥 MÉTODOS DE CACHÉ
    // ============================================

    private function clearMascotasCache(int $fundacionId): void
    {
        $keys = Cache::get('mascotas_cache_keys_' . $fundacionId, []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('mascotas_cache_keys_' . $fundacionId);

        // También limpiar caché de fundación
        $user = Auth::user();
        if ($user) {
            Cache::forget('fundacion_user_' . $user->id);
        }

        Log::info('🧹 Caché de mascotas limpiado para fundación ' . $fundacionId);
    }

    private function rememberMascotasCacheKey(int $fundacionId, string $cacheKey): void
    {
        $keys = Cache::get('mascotas_cache_keys_' . $fundacionId, []);
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put('mascotas_cache_keys_' . $fundacionId, $keys, self::CACHE_TTL);
        }
    }

    // ============================================
    // ✅ OBTENER MASCOTAS - OPTIMIZADO
    // ============================================

    /**
     * ✅ OBTENER MASCOTAS - SIN VALIDACIÓN DE FUNDACIÓN
     */
    public function getAllMascotas(array $filters = [], int $perPage = 15)
    {
        $user = Auth::user();

        // 🔥 Si no hay usuario autenticado, devolver error
        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        // 🔥 Si no es fundación, devolver error
        if ($user->tipo !== 'fundacion') {
            throw new \Exception('El usuario debe ser de tipo fundación');
        }

        // 🔥 Buscar la fundación del usuario
        $fundacion = Fundacion::where('user_id', $user->id)->first();

        // 🔥 Si no tiene fundación, crearla automáticamente
        if (!$fundacion) {
            $fundacion = Fundacion::create([
                'Nombre_1' => $user->nombre ?? 'Fundación de ' . $user->email,
                'user_id' => $user->id,
                'Email' => $user->email,
                'Direccion' => $user->direccion ?? 'Por definir',
                'Telefono' => $user->telefono ?? '000000000',
                'registro_sanitario' => 'PENDIENTE_' . $user->id,
                'ciudad' => $user->ciudad ?? null,
                'recibe_voluntarios' => false,
                'capacidad_maxima' => 0,
            ]);
            Log::info('✅ Fundación creada automáticamente:', ['id' => $fundacion->id, 'user_id' => $user->id]);
        }

        // 🔥 Si la fundación existe pero no tiene ID válido, crear una
        if (!$fundacion || !$fundacion->id) {
            throw new \Exception('No se pudo obtener o crear la fundación');
        }

        $cacheKey = self::CACHE_PREFIX . $fundacion->id . '_' . md5(json_encode($filters) . $perPage);

        $this->rememberMascotasCacheKey($fundacion->id, $cacheKey);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($fundacion, $filters, $perPage) {
            $query = Mascota::where('fundacion_id', $fundacion->id)
                ->select([
                    'id',
                    'nombre_mascota',
                    'especie',
                    'genero',
                    'edad_aprox',
                    'estado',
                    'foto_principal',
                    'created_at',
                    'descripcion',
                    'lugar_rescate',
                    'tamano',
                    'color',
                    'peso_aprox',
                ])
                ->with([
                    'razas' => function ($q) {
                        $q->select('id', 'nombre_raza');
                    },
                    'vacunas' => function ($q) {
                        $q->select('id', 'nombre');
                    }
                ]);

            // Filtros
            if (!empty($filters['buscar'])) {
                $search = $filters['buscar'];
                $query->where(function ($q) use ($search) {
                    $q->where('nombre_mascota', 'like', '%' . $search . '%')
                        ->orWhere('descripcion', 'like', '%' . $search . '%')
                        ->orWhere('especie', 'like', '%' . $search . '%')
                        ->orWhere('lugar_rescate', 'like', '%' . $search . '%');
                });
            }

            if (!empty($filters['especie'])) {
                $query->where('especie', $filters['especie']);
            }

            if (!empty($filters['genero'])) {
                $query->where('genero', $filters['genero']);
            }

            if (!empty($filters['estado'])) {
                $query->where('estado', $filters['estado']);
            }

            if (!empty($filters['tamano'])) {
                $query->where('tamano', $filters['tamano']);
            }

            $query->orderBy('created_at', 'desc');

            return $query->paginate($perPage);
        });
    }

    // ============================================
    // ✅ BUSCAR UNA MASCOTA POR ID
    // ============================================

    public function findMascota(int $id)
    {
        $fundacion = $this->getFundacionCached();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascota = Mascota::where('fundacion_id', $fundacion->id)
            ->with([
                'razas' => function ($q) {
                    $q->select('id', 'nombre_raza');
                },
                'vacunas' => function ($q) {
                    $q->select('id', 'nombre');
                }
            ])
            ->find($id);

        if (!$mascota) {
            throw new ModelNotFoundException('Mascota no encontrada');
        }

        // Normalizar galeria_fotos
        if ($mascota->galeria_fotos) {
            if (is_string($mascota->galeria_fotos)) {
                $galeria = json_decode($mascota->galeria_fotos, true);
                if (is_array($galeria)) {
                    $mascota->galeria_fotos = array_values(array_filter($galeria, function ($item) {
                        return is_string($item) && !empty($item);
                    }));
                } else {
                    $mascota->galeria_fotos = [];
                }
            } elseif (is_array($mascota->galeria_fotos)) {
                $mascota->galeria_fotos = array_values(array_filter($mascota->galeria_fotos, function ($item) {
                    return is_string($item) && !empty($item);
                }));
            }
        } else {
            $mascota->galeria_fotos = [];
        }

        return $mascota;
    }

    // ============================================
    // ✅ ACTUALIZAR ESTADO DE MASCOTA
    // ============================================

    public function actualizarEstado(int $id, string $estado): Mascota
    {
        $fundacion = $this->getFundacionCached();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascota = Mascota::where('fundacion_id', $fundacion->id)
            ->findOrFail($id);

        $mascota->estado = $estado;
        $mascota->save();

        // 🔥 LIMPIAR CACHÉ
        $this->clearMascotasCache($fundacion->id);

        return $mascota->load(['razas', 'vacunas']);
    }

    // ============================================
    // ✅ CREAR MASCOTA
    // ============================================

    public function createMascota(array $data, $files = null)
    {
        $fundacion = $this->getFundacionCached();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascotaData = [
            'fundacion_id' => $fundacion->id,
            'nombre_mascota' => $data['nombre_mascota'] ?? 'Mascota',
            'especie' => $data['especie'] ?? null,
            'edad_aprox' => isset($data['edad_aprox']) && $data['edad_aprox'] !== '' ? (float) $data['edad_aprox'] : null,
            'genero' => $data['genero'] ?? null,
            'estado' => $data['estado'] ?? 'En adopcion',
            'lugar_rescate' => $data['lugar_rescate'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'condiciones_especiales' => $data['condiciones_especiales'] ?? null,
            'necesita_hogar_temporal' => isset($data['necesita_hogar_temporal']) ? (bool) $data['necesita_hogar_temporal'] : false,
            'apto_con_ninos' => isset($data['apto_con_ninos']) ? (bool) $data['apto_con_ninos'] : true,
            'apto_con_otros_animales' => isset($data['apto_con_otros_animales']) ? (bool) $data['apto_con_otros_animales'] : true,
            'fecha_ingreso' => $data['fecha_ingreso'] ?? now()->format('Y-m-d'),
            'peso_aprox' => isset($data['peso_aprox']) && $data['peso_aprox'] !== '' ? (float) $data['peso_aprox'] : null,
            'tamano' => $data['tamano'] ?? null,
            'color' => $data['color'] ?? null,
            'salud_general' => $data['salud_general'] ?? null,
            'esterilizado' => isset($data['esterilizado']) ? (bool) $data['esterilizado'] : false,
            'desparasitado' => isset($data['desparasitado']) ? (bool) $data['desparasitado'] : false,
            'vacunado' => isset($data['vacunado']) ? (bool) $data['vacunado'] : false,
            'video_url' => $data['video_url'] ?? null,
        ];

        $mascota = new Mascota();
        $mascota->fill($mascotaData);

        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        $mascota->save();

        // Galería
        if (!empty($files['galeria_fotos']) && is_array($files['galeria_fotos'])) {
            $galeriaPaths = [];
            foreach ($files['galeria_fotos'] as $foto) {
                if ($foto && $foto->isValid()) {
                    $path = $this->uploadImage($foto, 'mascotas/galeria');
                    $galeriaPaths[] = $path;
                }
            }
            if (!empty($galeriaPaths)) {
                $mascota->galeria_fotos = json_encode($galeriaPaths);
                $mascota->save();
            }
        }

        // Arrays JSON
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas'])) {
            $mascota->enfermedades_cronicas = json_encode(array_values($data['enfermedades_cronicas']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['medicamentos']) && is_array($data['medicamentos'])) {
            $mascota->medicamentos = json_encode(array_values($data['medicamentos']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion'])) {
            $mascota->requisitos_adopcion = json_encode(array_values($data['requisitos_adopcion']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // Razas
        if (!empty($data['razas']) && is_array($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        // Vacunas
        if (!empty($data['vacunas']) && is_array($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        // 🔥 LIMPIAR CACHÉ
        $this->clearMascotasCache($fundacion->id);

        return $mascota->load(['razas', 'vacunas']);
    }

    // ============================================
    // ✅ ACTUALIZAR MASCOTA
    // ============================================

    public function updateMascota(int $id, array $data, $files = null)
    {
        Log::info('=== UPDATE MASCOTA ===');
        Log::info('ID: ' . $id);

        $mascota = $this->findMascota($id);
        $fundacionId = $mascota->fundacion_id;

        // Foto principal
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            if ($mascota->foto_principal) {
                $this->deleteImage($mascota->foto_principal);
            }
            $data['foto_principal'] = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        // Galería actual
        $galeriaActual = [];
        if ($mascota->galeria_fotos) {
            if (is_string($mascota->galeria_fotos)) {
                $galeriaActual = json_decode($mascota->galeria_fotos, true) ?: [];
            } elseif (is_array($mascota->galeria_fotos)) {
                $galeriaActual = $mascota->galeria_fotos;
            }
        }
        $galeriaActual = array_values(array_filter($galeriaActual, function ($item) {
            return is_string($item) && !empty($item);
        }));

        // Eliminar fotos marcadas
        if (isset($data['fotos_eliminar']) && is_array($data['fotos_eliminar'])) {
            foreach ($data['fotos_eliminar'] as $fotoPath) {
                if (!$fotoPath || !is_string($fotoPath)) continue;

                $fotoAEliminar = null;
                $indexToRemove = null;

                foreach ($galeriaActual as $index => $existingFoto) {
                    $normalizedExisting = $this->normalizeImageUrl($existingFoto);
                    $normalizedToDelete = $this->normalizeImageUrl($fotoPath);

                    if ($normalizedExisting === $normalizedToDelete) {
                        $fotoAEliminar = $existingFoto;
                        $indexToRemove = $index;
                        break;
                    }
                }

                if ($fotoAEliminar) {
                    $publicId = $this->extractPublicIdFromUrl($fotoAEliminar);
                    if ($publicId) {
                        $this->deleteImage($publicId);
                    }
                    unset($galeriaActual[$indexToRemove]);
                }
            }
            $galeriaActual = array_values($galeriaActual);
        }

        // Agregar nuevas fotos
        if (!empty($files['galeria_fotos']) && is_array($files['galeria_fotos'])) {
            foreach ($files['galeria_fotos'] as $foto) {
                if ($foto && $foto->isValid()) {
                    $url = $this->uploadImage($foto, 'mascotas/galeria');
                    $galeriaActual[] = $url;
                }
            }
        }

        $data['galeria_fotos'] = json_encode(array_values($galeriaActual));

        // Actualizar
        $mascota->update($data);

        // Arrays JSON
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas'])) {
            $mascota->enfermedades_cronicas = json_encode(array_values($data['enfermedades_cronicas']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['medicamentos']) && is_array($data['medicamentos'])) {
            $mascota->medicamentos = json_encode(array_values($data['medicamentos']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion'])) {
            $mascota->requisitos_adopcion = json_encode(array_values($data['requisitos_adopcion']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // Razas
        if (isset($data['razas']) && is_array($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        // Vacunas
        if (isset($data['vacunas']) && is_array($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        // 🔥 LIMPIAR CACHÉ
        $this->clearMascotasCache($fundacionId);

        return $mascota->load(['razas', 'vacunas']);
    }

    // ============================================
    // ✅ ELIMINAR MASCOTA
    // ============================================

    public function deleteMascota(int $id)
    {
        $mascota = $this->findMascota($id);
        $fundacionId = $mascota->fundacion_id;

        if ($mascota->foto_principal && is_string($mascota->foto_principal)) {
            $this->deleteImage($mascota->foto_principal);
        }

        if ($mascota->galeria_fotos) {
            $galeria = is_string($mascota->galeria_fotos)
                ? json_decode($mascota->galeria_fotos, true)
                : $mascota->galeria_fotos;

            if (is_array($galeria)) {
                foreach ($galeria as $foto) {
                    if ($foto && is_string($foto)) {
                        $this->deleteImage($foto);
                    }
                }
            }
        }

        $mascota->razas()->detach();
        $mascota->vacunas()->detach();
        $mascota->delete();

        // 🔥 LIMPIAR CACHÉ
        $this->clearMascotasCache($fundacionId);
    }

    /**
     * ✅ ALTERNAR DESTACADA
     */
    public function toggleDestacada(int $id): Mascota
    {
        $mascota = $this->findMascota($id);
        $fundacionId = $mascota->fundacion_id;

        $mascota->destacada = !$mascota->destacada;
        $mascota->save();

        // 🔥 LIMPIAR CACHÉ
        $this->clearMascotasCache($fundacionId);

        return $mascota;
    }

    // ============================================
    // ✅ UTILIDADES DE IMAGEN
    // ============================================

    private function normalizeImageUrl(?string $url): ?string
    {
        if (!$url) return null;

        if (strpos($url, 'cloudinary.com') !== false) {
            if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\?|$)/', $url, $matches)) {
                $normalized = preg_replace('/\.[^.]+$/', '', $matches[1]);
                return $normalized;
            }
        }

        if (strpos($url, 'mascotas/') === 0) {
            $normalized = preg_replace('/\.[^.]+$/', '', $url);
            return $normalized;
        }

        return $url;
    }

    private function extractPublicIdFromUrl(string $url): ?string
    {
        if (strpos($url, 'cloudinary.com') === false && strpos($url, '/') === false) {
            return $url;
        }

        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-zA-Z]+)?$/', $url, $matches)) {
            $publicId = preg_replace('/\.[^.]+$/', '', $matches[1]);
            return $publicId;
        }

        if (strpos($url, 'mascotas/') === 0) {
            return preg_replace('/\.[^.]+$/', '', $url);
        }

        return null;
    }
}
