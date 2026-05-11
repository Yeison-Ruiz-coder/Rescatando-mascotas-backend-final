<?php

namespace App\Services\Entity;

use App\Models\Mascota;
use App\Models\Fundacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class MascotaEntityService
{
    use ImageUploadTrait;

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
        $user = Auth::user();

        if ($user->tipo !== 'fundacion') {
            return null;
        }

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
    }

    public function getAllMascotas()
    {
        $fundacion = $this->getFundacion();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        return Mascota::where('fundacion_id', $fundacion->id)->get();
    }

    public function findMascota(int $id)
    {
        $fundacion = $this->getFundacion();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascota = Mascota::where('fundacion_id', $fundacion->id)
            ->with(['razas', 'vacunas'])
            ->find($id);

        if (!$mascota) {
            throw new ModelNotFoundException('Mascota no encontrada');
        }

        // ✅ Normalizar galeria_fotos para que siempre sea array de strings
        if ($mascota->galeria_fotos) {
            if (is_string($mascota->galeria_fotos)) {
                $galeria = json_decode($mascota->galeria_fotos, true);
                if (is_array($galeria)) {
                    // Filtrar solo strings válidos (eliminar objetos vacíos)
                    $mascota->galeria_fotos = array_values(array_filter($galeria, function ($item) {
                        return is_string($item) && !empty($item);
                    }));
                } else {
                    $mascota->galeria_fotos = [];
                }
            } elseif (is_array($mascota->galeria_fotos)) {
                // Filtrar solo strings válidos
                $mascota->galeria_fotos = array_values(array_filter($mascota->galeria_fotos, function ($item) {
                    return is_string($item) && !empty($item);
                }));
            }
        } else {
            $mascota->galeria_fotos = [];
        }

        return $mascota;
    }

    public function createMascota(array $data, $files = null)
    {
        $fundacion = $this->getFundacion();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascotaData = [
            'fundacion_id' => $fundacion->id,
            'nombre_mascota' => $data['nombre_mascota'],
            'especie' => $data['especie'],
            'edad_aprox' => $data['edad_aprox'],
            'genero' => $data['genero'],
            'estado' => $data['estado'],
            'lugar_rescate' => $data['lugar_rescate'] ?? null,
            'descripcion' => $data['descripcion'],
            'condiciones_especiales' => $data['condiciones_especiales'] ?? null,
            'necesita_hogar_temporal' => $data['necesita_hogar_temporal'] ?? false,
            'apto_con_ninos' => $data['apto_con_ninos'] ?? true,
            'apto_con_otros_animales' => $data['apto_con_otros_animales'] ?? true,
            'fecha_ingreso' => $data['fecha_ingreso'] ?? now(),
        ];

        // Nuevos campos
        if (isset($data['peso_aprox'])) {
            $mascotaData['peso_aprox'] = $data['peso_aprox'];
        }
        if (isset($data['tamano'])) {
            $mascotaData['tamano'] = $data['tamano'];
        }
        if (isset($data['color'])) {
            $mascotaData['color'] = $data['color'];
        }
        if (isset($data['salud_general'])) {
            $mascotaData['salud_general'] = $data['salud_general'];
        }
        if (isset($data['esterilizado'])) {
            $mascotaData['esterilizado'] = $data['esterilizado'];
        }
        if (isset($data['desparasitado'])) {
            $mascotaData['desparasitado'] = $data['desparasitado'];
        }
        if (isset($data['vacunado'])) {
            $mascotaData['vacunado'] = $data['vacunado'];
        }
        if (isset($data['video_url'])) {
            $mascotaData['video_url'] = $data['video_url'];
        }

        $mascota = new Mascota();
        $mascota->fill($mascotaData);

        // Guardar foto principal
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        $mascota->save();

        // Guardar galería de fotos
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

        // Procesar arrays JSON
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

        // Sincronizar razas
        if (!empty($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        // Sincronizar vacunas
        if (!empty($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        return $mascota->load(['razas', 'vacunas']);
    }

    private function normalizeImageUrl(?string $url): ?string
    {
        if (!$url) return null;

        // Si es una URL completa de Cloudinary, extraer la parte relevante
        if (strpos($url, 'cloudinary.com') !== false) {
            // Extraer todo después de /upload/v{numero}/
            if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\?|$)/', $url, $matches)) {
                // Eliminar la extensión del archivo
                $normalized = preg_replace('/\.[^.]+$/', '', $matches[1]);
                Log::info('Normalizando URL:', ['original' => $url, 'normalized' => $normalized]);
                return $normalized;
            }
        }

        // Si ya es un path limpio (como 'mascotas/galeria/abc123')
        if (strpos($url, 'mascotas/') === 0) {
            // Eliminar extensión si existe
            $normalized = preg_replace('/\.[^.]+$/', '', $url);
            return $normalized;
        }

        return $url;
    }

    /**
     * Extrae el public_id de Cloudinary desde una URL
     */
    private function extractPublicIdFromUrl(string $url): ?string
    {
        // Si ya es un public_id limpio
        if (strpos($url, 'cloudinary.com') === false && strpos($url, '/') === false) {
            return $url;
        }

        // Extraer de URL completa de Cloudinary
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-zA-Z]+)?$/', $url, $matches)) {
            // Eliminar la extensión del archivo
            $publicId = preg_replace('/\.[^.]+$/', '', $matches[1]);
            return $publicId;
        }

        // Si es un path como 'mascotas/galeria/abc123'
        if (strpos($url, 'mascotas/') === 0) {
            return preg_replace('/\.[^.]+$/', '', $url);
        }

        return null;
    }

    public function updateMascota(int $id, array $data, $files = null)
    {
        Log::info('=== UPDATE MASCOTA ===');
        Log::info('ID: ' . $id);
        Log::info('FILES recibidos:', $files ?? []);

        $mascota = $this->findMascota($id);

        // ============================================
        // 1. ACTUALIZAR FOTO PRINCIPAL
        // ============================================
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            // Eliminar foto anterior si existe
            if ($mascota->foto_principal) {
                $this->deleteImage($mascota->foto_principal);
            }
            $data['foto_principal'] = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        // ============================================
        // 2. OBTENER GALERÍA ACTUAL
        // ============================================
        $galeriaActual = [];
        if ($mascota->galeria_fotos) {
            if (is_string($mascota->galeria_fotos)) {
                $galeriaActual = json_decode($mascota->galeria_fotos, true) ?: [];
            } elseif (is_array($mascota->galeria_fotos)) {
                $galeriaActual = $mascota->galeria_fotos;
            }
        }

        // Filtrar solo strings válidos
        $galeriaActual = array_values(array_filter($galeriaActual, function ($item) {
            return is_string($item) && !empty($item);
        }));

        Log::info('Galería actual:', $galeriaActual);

        // ============================================
        // 3. ELIMINAR FOTOS MARCADAS
        // ============================================
        if (isset($data['fotos_eliminar']) && is_array($data['fotos_eliminar'])) {
            Log::info('Fotos a eliminar:', $data['fotos_eliminar']);

            foreach ($data['fotos_eliminar'] as $fotoPath) {
                if (!$fotoPath || !is_string($fotoPath)) continue;

                // Encontrar la URL completa que coincide
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
                    // Eliminar de Cloudinary
                    $publicId = $this->extractPublicIdFromUrl($fotoAEliminar);
                    if ($publicId) {
                        Log::info('🗑️ Eliminando de Cloudinary:', ['public_id' => $publicId]);
                        $this->deleteImage($publicId);
                    }

                    // Eliminar del array
                    unset($galeriaActual[$indexToRemove]);
                    Log::info('✅ Foto eliminada:', ['url' => $fotoAEliminar]);
                } else {
                    Log::warning('⚠️ Foto no encontrada para eliminar:', ['path' => $fotoPath]);
                }
            }

            // Reindexar el array después de eliminaciones
            $galeriaActual = array_values($galeriaActual);
            Log::info('Galería después de eliminar:', $galeriaActual);
        }

        // ============================================
        // 4. AGREGAR NUEVAS FOTOS (UN SOLO BLOQUE)
        // ============================================
        if (!empty($files['galeria_fotos']) && is_array($files['galeria_fotos'])) {
            Log::info('Nuevas fotos a subir:', ['count' => count($files['galeria_fotos'])]);

            foreach ($files['galeria_fotos'] as $index => $foto) {
                if ($foto && $foto->isValid()) {
                    $url = $this->uploadImage($foto, 'mascotas/galeria');
                    $galeriaActual[] = $url;
                    Log::info("Foto nueva {$index} subida: " . $url);
                }
            }
        }

        // ============================================
        // 5. GUARDAR GALERÍA
        // ============================================
        $data['galeria_fotos'] = json_encode(array_values($galeriaActual));
        Log::info('Galería final guardada:', $galeriaActual);

        // ============================================
        // 6. ACTUALIZAR EL RESTO DE DATOS
        // ============================================
        $mascota->update($data);

        // ============================================
        // 7. PROCESAR ARRAYS JSON
        // ============================================
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

        // ============================================
        // 8. SINCRONIZAR RELACIONES
        // ============================================
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

    public function deleteMascota(int $id)
    {
        $mascota = $this->findMascota($id);

        // Eliminar foto principal
        if ($mascota->foto_principal && is_string($mascota->foto_principal)) {
            $this->deleteImage($mascota->foto_principal);
        }

        // Eliminar imágenes de galería
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

    public function toggleDestacada(int $id): Mascota
    {
        $mascota = $this->findMascota($id);
        $mascota->destacada = !$mascota->destacada;
        $mascota->save();
        return $mascota;
    }
}
