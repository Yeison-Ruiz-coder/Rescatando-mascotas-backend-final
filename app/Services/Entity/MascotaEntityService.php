<?php

namespace App\Services\Entity;

use App\Models\Mascota;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class MascotaEntityService
{
    use ImageUploadTrait;

    private const CACHE_TTL = 300;

    // ============================================
    // 🔥 OBTENER ENTIDAD CON CACHÉ
    // ============================================

    private function getFundacionCached()
    {
        $user = Auth::user();

        if ($user->tipo !== 'fundacion') {
            return null;
        }

        return Cache::remember('fundacion_user_' . $user->id, self::CACHE_TTL, function () use ($user) {
            $fundacion = Fundacion::where('user_id', $user->id)->first();

            if (!$fundacion) {
                $fundacion = Fundacion::create([
                    'Nombre_1' => $user->nombre ?? 'Fundación',
                    'Direccion' => $user->direccion ?? 'Pendiente',
                    'Telefono' => $user->telefono ?? '000000000',
                    'Email' => $user->email,
                    'registro_sanitario' => 'PENDIENTE_' . $user->id,
                    'user_id' => $user->id,
                    'ciudad' => $user->ciudad ?? null,
                ]);
            }

            return $fundacion;
        });
    }

    private function getVeterinariaCached()
    {
        $user = Auth::user();

        if ($user->tipo !== 'veterinaria') {
            return null;
        }

        return Cache::remember('veterinaria_user_' . $user->id, self::CACHE_TTL, function () use ($user) {
            $veterinaria = Veterinaria::where('user_id', $user->id)->first();

            if (!$veterinaria) {
                $veterinaria = Veterinaria::create([
                    'Nombre_vet' => $user->nombre ?? 'Veterinaria',
                    'user_id' => $user->id,
                    'Email' => $user->email,
                    'Direccion' => $user->direccion ?? 'Pendiente',
                    'Telefono' => $user->telefono ?? '000000000',
                    'ciudad' => $user->ciudad ?? null,
                ]);
            }

            return $veterinaria;
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

    public function getVeterinaria()
    {
        return $this->getVeterinariaCached();
    }

    // ============================================
    // ✅ OBTENER TODAS LAS MASCOTAS
    // ============================================

    public function getAllMascotas()
    {
        $user = Auth::user();

        if ($user->tipo === 'fundacion') {
            $fundacion = $this->getFundacionCached();
            if (!$fundacion) {
                throw new \Exception('Perfil de fundación no encontrado');
            }
            return Mascota::where('fundacion_id', $fundacion->id)->get();
        }

        if ($user->tipo === 'veterinaria') {
            $veterinaria = $this->getVeterinariaCached();
            if (!$veterinaria) {
                throw new \Exception('Perfil de veterinaria no encontrado');
            }
            return Mascota::where('veterinaria_id', $veterinaria->id)->get();
        }

        throw new \Exception('Tipo de usuario no válido para mascotas');
    }

    // ============================================
    // ✅ BUSCAR MASCOTA POR ID
    // ============================================

    public function findMascota(int $id)
    {
        $user = Auth::user();

        $query = Mascota::with(['razas', 'vacunas']);

        if ($user->tipo === 'fundacion') {
            $fundacion = $this->getFundacionCached();
            if (!$fundacion) {
                throw new \Exception('Perfil de fundación no encontrado');
            }
            $query->where('fundacion_id', $fundacion->id);
        } elseif ($user->tipo === 'veterinaria') {
            $veterinaria = $this->getVeterinariaCached();
            if (!$veterinaria) {
                throw new \Exception('Perfil de veterinaria no encontrado');
            }
            $query->where('veterinaria_id', $veterinaria->id);
        } else {
            throw new \Exception('Tipo de usuario no válido');
        }

        $mascota = $query->find($id);

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
    // ✅ ACTUALIZAR ESTADO
    // ============================================

    public function actualizarEstado(int $id, string $estado): Mascota
    {
        $mascota = $this->findMascota($id);
        $mascota->estado = $estado;
        $mascota->save();
        return $mascota->load(['razas', 'vacunas']);
    }

    // ============================================
    // ✅ CREAR MASCOTA
    // ============================================

    public function createMascota(array $data, $files = null)
    {
        $user = Auth::user();

        $mascotaData = [
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

        // 🔥 ASIGNAR fundacion_id o veterinaria_id según el tipo de usuario
        if ($user->tipo === 'fundacion') {
            $fundacion = $this->getFundacionCached();
            if (!$fundacion) {
                throw new \Exception('Perfil de fundación no encontrado');
            }
            $mascotaData['fundacion_id'] = $fundacion->id;
            $mascotaData['veterinaria_id'] = null;
        } elseif ($user->tipo === 'veterinaria') {
            $veterinaria = $this->getVeterinariaCached();
            if (!$veterinaria) {
                throw new \Exception('Perfil de veterinaria no encontrado');
            }
            $mascotaData['veterinaria_id'] = $veterinaria->id;
            $mascotaData['fundacion_id'] = null;
        } else {
            throw new \Exception('Tipo de usuario no válido para crear mascotas');
        }

        $mascota = new Mascota();
        $mascota->fill($mascotaData);

        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        $mascota->save();

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

        if (!empty($data['razas']) && is_array($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        if (!empty($data['vacunas']) && is_array($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        return $mascota->load(['razas', 'vacunas']);
    }

    // ============================================
    // 🔥 UTILIDADES DE IMAGEN
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

    // ============================================
    // 🔥 ACTUALIZAR MASCOTA
    // ============================================

    public function updateMascota(int $id, array $data, $files = null)
    {
        Log::info('=== UPDATE MASCOTA ===');
        Log::info('ID: ' . $id);

        $mascota = $this->findMascota($id);

        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            if ($mascota->foto_principal) {
                $this->deleteImage($mascota->foto_principal);
            }
            $data['foto_principal'] = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

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

        if (!empty($files['galeria_fotos']) && is_array($files['galeria_fotos'])) {
            foreach ($files['galeria_fotos'] as $foto) {
                if ($foto && $foto->isValid()) {
                    $url = $this->uploadImage($foto, 'mascotas/galeria');
                    $galeriaActual[] = $url;
                }
            }
        }

        $data['galeria_fotos'] = json_encode(array_values($galeriaActual));

        $mascota->update($data);

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

        if (isset($data['razas']) && is_array($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        if (isset($data['vacunas']) && is_array($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        return $mascota->load(['razas', 'vacunas']);
    }

    // ============================================
    // 🔥 ELIMINAR MASCOTA
    // ============================================

    public function deleteMascota(int $id)
    {
        $mascota = $this->findMascota($id);

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
    }

    // ============================================
    // 🔥 ALTERNAR DESTACADA
    // ============================================

    public function toggleDestacada(int $id): Mascota
    {
        $mascota = $this->findMascota($id);
        $mascota->destacada = !$mascota->destacada;
        $mascota->save();
        return $mascota;
    }
}
