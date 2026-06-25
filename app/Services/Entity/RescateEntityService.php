<?php

namespace App\Services\Entity;

use App\Models\Rescate;
use App\Models\Mascota;
use App\Models\Notificacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class RescateEntityService
{
    use ImageUploadTrait;

    // ============================================
    // 🔥 CONSTANTES DE CACHÉ
    // ============================================
    private const CACHE_TTL = 300; // 5 minutos

    // ============================================
    // 🔥 ENTIDAD CON CACHÉ
    // ============================================

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

    private function getEntidadCached()
    {
        $user = Auth::user();
        if (!$user) return null;

        $cacheKey = 'entidad_' . $user->id . '_' . $user->tipo;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->getEntidad();
        });
    }

    private function clearEntidadCache(): void
    {
        $user = Auth::user();
        if ($user) {
            Cache::forget('entidad_' . $user->id . '_' . $user->tipo);
            Log::info('🧹 Caché de entidad limpiado para usuario: ' . $user->id);
        }
    }

    // ============================================
    // ✅ UTILIDADES DE IMAGEN
    // ============================================

    private function extractPublicIdFromUrl(string $url): ?string
    {
        if (strpos($url, 'cloudinary.com') === false && strpos($url, '/') === false) {
            return $url;
        }

        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-zA-Z]+)?$/', $url, $matches)) {
            $publicId = preg_replace('/\.[^.]+$/', '', $matches[1]);
            return $publicId;
        }

        if (strpos($url, 'rescates/') === 0) {
            return preg_replace('/\.[^.]+$/', '', $url);
        }

        return null;
    }

    private function normalizeImageUrl(?string $url): ?string
    {
        if (!$url) return null;

        if (strpos($url, 'cloudinary.com') !== false) {
            if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\?|$)/', $url, $matches)) {
                $normalized = preg_replace('/\.[^.]+$/', '', $matches[1]);
                return $normalized;
            }
        }

        if (strpos($url, 'rescates/') === 0) {
            $normalized = preg_replace('/\.[^.]+$/', '', $url);
            return $normalized;
        }

        return $url;
    }

    // ============================================
    // ✅ OBTENER MIS RESCATES - OPTIMIZADO
    // ============================================

    public function getMisRescates()
    {
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        // 🔥 SOLO LOS CAMPOS NECESARIOS
        return Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('tipo_emergencia', '!=', 'otro')
            ->select([
                'id',
                'fecha_rescate',
                'lugar_rescate',
                'descripcion_rescate',
                'estado',
                'tipo_emergencia',
                'prioridad',
                'lat',
                'lng',
                'nombre_reportante',
                'email_reportante',
                'telefono_reportante',
                'mascota_id',
                'usuario_reporto_id',
                'created_at',
            ])
            ->with([
                'usuarioReporto:id,nombre,email',
                'mascota:id,nombre_mascota,foto_principal,estado,especie,genero'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    // ============================================
    // ✅ OBTENER RESCATES DISPONIBLES - OPTIMIZADO
    // ============================================

    public function getRescatesDisponibles(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            return collect([]);
        }

        $lat = $entidad->lat ?? null;
        $lng = $entidad->lng ?? null;
        $radio = $entidad->radio_atencion ?? 10;
        $userTipo = $user->tipo;

        // 🔥 SOLO LOS CAMPOS NECESARIOS
        $query = Rescate::select([
                'id',
                'fecha_rescate',
                'lugar_rescate',
                'descripcion_rescate',
                'estado',
                'tipo_emergencia',
                'prioridad',
                'lat',
                'lng',
                'nombre_reportante',
                'email_reportante',
                'telefono_reportante',
                'usuario_reporto_id',
                'created_at',
            ])
            ->where('estado', 'pendiente')
            ->where('tipo_emergencia', '!=', 'otro')
            ->where(function ($query) use ($userTipo) {
                if ($userTipo === 'veterinaria') {
                    $query->whereIn('tipo_emergencia', ['herido', 'urgente']);
                } elseif ($userTipo === 'fundacion') {
                    $query->whereIn('tipo_emergencia', ['abandonado', 'urgente']);
                }
            })
            ->with([
                'usuarioReporto:id,nombre,email',
                'mascota:id,nombre_mascota,foto_principal'
            ]);

        // 🔥 CÁLCULO DE DISTANCIA
        if ($lat && $lng) {
            $query->selectRaw("
                (6371 * acos(
                    cos(radians(?)) *
                    cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(lat))
                )) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', $radio)
            ->orderBy('distance');
        }

        return $query->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(15);
    }

    // ============================================
    // ✅ ENCONTRAR POR ID
    // ============================================

    public function findById(int $id): Rescate
    {
        return Rescate::select([
                'id',
                'fecha_rescate',
                'lugar_rescate',
                'descripcion_rescate',
                'estado',
                'tipo_emergencia',
                'prioridad',
                'lat',
                'lng',
                'nombre_reportante',
                'email_reportante',
                'telefono_reportante',
                'mascota_id',
                'usuario_reporto_id',
                'entidad_responsable_type',
                'entidad_responsable_id',
                'created_at',
            ])
            ->with([
                'usuarioReporto:id,nombre,email',
                'mascota:id,nombre_mascota,foto_principal,estado,especie,genero',
                'entidadResponsable'
            ])
            ->findOrFail($id);
    }

    // ============================================
    // ✅ ACEPTAR RESCATE
    // ============================================

    public function aceptarRescate(int $id)
    {
        $user = Auth::user();
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('estado', 'pendiente')->findOrFail($id);

        if ($rescate->tipo_emergencia === 'otro') {
            throw new \Exception('Este tipo de rescate solo puede ser asignado por un administrador');
        }

        $puedeAceptar = false;
        if ($user->tipo === 'veterinaria' && in_array($rescate->tipo_emergencia, ['herido', 'urgente'])) {
            $puedeAceptar = true;
        }
        if ($user->tipo === 'fundacion' && in_array($rescate->tipo_emergencia, ['abandonado', 'urgente'])) {
            $puedeAceptar = true;
        }

        if (!$puedeAceptar) {
            throw new \Exception('No puedes aceptar este tipo de rescate');
        }

        $rescate->update([
            'estado' => 'en_proceso',
            'entidad_responsable_type' => get_class($entidad),
            'entidad_responsable_id' => $entidad->id,
        ]);

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        if ($rescate->usuario_reporto_id) {
            $nombreEntidad = $user->tipo === 'veterinaria'
                ? ($entidad->Nombre_vet ?? $entidad->nombre)
                : ($entidad->Nombre_1 ?? $entidad->nombre);

            Notificacion::create([
                'user_id' => $rescate->usuario_reporto_id,
                'contenido' => "Tu reporte de rescate fue aceptado por {$nombreEntidad}",
                'creado_por_id' => $user->id,
            ]);
        }

        return $rescate->load(['usuarioReporto', 'entidadResponsable']);
    }

    // ============================================
    // ✅ RECHAZAR RESCATE
    // ============================================

    public function rechazarRescate(int $id)
    {
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('estado', 'pendiente')->findOrFail($id);

        $rescate->update([
            'estado' => 'pendiente'
        ]);

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        return $rescate;
    }

    // ============================================
    // ✅ COMPLETAR RESCATE
    // ============================================

    public function completarRescate(int $id)
    {
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('estado', 'en_proceso')
            ->findOrFail($id);

        $rescate->update(['estado' => 'completado']);

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        return $rescate;
    }

    // ============================================
    // ✅ REGISTRAR MASCOTA DESDE RESCATE
    // ============================================

    public function registrarMascotaDesdeRescate(int $id, array $data, $files = null)
    {
        $user = Auth::user();
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('estado', 'en_proceso')
            ->findOrFail($id);

        // Determinar estado
        if (isset($data['estado']) && !empty($data['estado'])) {
            $estado = $data['estado'];
        } else {
            $estado = 'Rescatada';
        }

        if ($data['necesita_hogar_temporal'] ?? false) {
            $estado = 'En acogida';
        }

        // Crear mascota
        $mascotaData = [
            'nombre_mascota' => $data['nombre_mascota'],
            'especie' => $data['especie'],
            'edad_aprox' => $data['edad_aprox'] ?? null,
            'genero' => $data['genero'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'necesita_hogar_temporal' => $data['necesita_hogar_temporal'] ?? false,
            'apto_con_ninos' => $data['apto_con_ninos'] ?? true,
            'apto_con_otros_animales' => $data['apto_con_otros_animales'] ?? true,
            'condiciones_especiales' => $data['condiciones_especiales'] ?? null,
            'fecha_ingreso' => $data['fecha_ingreso'],
            'estado' => $estado,
            'fundacion_id' => $user->tipo === 'fundacion' ? $entidad->id : null,
            'lugar_rescate' => $rescate->lugar_rescate,
            'peso_aprox' => $data['peso_aprox'] ?? null,
            'tamano' => $data['tamano'] ?? null,
            'color' => $data['color'] ?? null,
            'salud_general' => $data['salud_general'] ?? null,
            'esterilizado' => $data['esterilizado'] ?? false,
            'desparasitado' => $data['desparasitado'] ?? false,
            'vacunado' => $data['vacunado'] ?? false,
            'video_url' => $data['video_url'] ?? null,
            'hogar_recomendado' => $data['hogar_recomendado'] ?? null,
        ];

        if ($rescate->foto_principal) {
            $mascotaData['foto_principal'] = $rescate->foto_principal;
        }

        $mascota = Mascota::create($mascotaData);

        // Subir foto principal
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
            $mascota->save();
        }

        // Galería
        if (!empty($files['galeria_fotos']) && is_array($files['galeria_fotos'])) {
            $galeriaPaths = [];
            foreach ($files['galeria_fotos'] as $foto) {
                if ($foto && $foto->isValid()) {
                    $galeriaPaths[] = $this->uploadImage($foto, 'mascotas/galeria');
                }
            }
            if (!empty($galeriaPaths)) {
                $mascota->galeria_fotos = json_encode($galeriaPaths);
                $mascota->save();
            }
        }

        // Razas
        if (isset($data['razas']) && is_array($data['razas']) && !empty($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        // Vacunas
        if (isset($data['vacunas']) && is_array($data['vacunas']) && !empty($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        // Enfermedades crónicas
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas']) && !empty($data['enfermedades_cronicas'])) {
            $mascota->enfermedades_cronicas = json_encode(array_values($data['enfermedades_cronicas']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // Medicamentos
        if (isset($data['medicamentos']) && is_array($data['medicamentos']) && !empty($data['medicamentos'])) {
            $mascota->medicamentos = json_encode(array_values($data['medicamentos']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // Requisitos de adopción
        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion']) && !empty($data['requisitos_adopcion'])) {
            $mascota->requisitos_adopcion = json_encode(array_values($data['requisitos_adopcion']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        $rescate->update([
            'mascota_id' => $mascota->id,
            'estado' => 'completado'
        ]);

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        if ($rescate->usuario_reporto_id) {
            Notificacion::create([
                'user_id' => $rescate->usuario_reporto_id,
                'contenido' => "La mascota que reportaste ({$mascota->nombre_mascota}) ha sido registrada",
                'creado_por_id' => $user->id,
            ]);
        }

        return $mascota->load(['razas', 'vacunas']);
    }

    // ============================================
    // ✅ AGREGAR FOTOS
    // ============================================

    public function agregarFotos(int $id, array $nuevasFotos): Rescate
    {
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->findOrFail($id);

        $galeriaActual = $rescate->galeria_fotos;
        if (is_string($galeriaActual)) {
            $galeriaActual = json_decode($galeriaActual, true) ?? [];
        } elseif (!is_array($galeriaActual)) {
            $galeriaActual = [];
        }

        foreach ($nuevasFotos as $foto) {
            if ($foto && $foto->isValid()) {
                $url = $this->uploadImage($foto, 'rescates/galeria');
                $galeriaActual[] = $url;
            }
        }

        $rescate->galeria_fotos = json_encode($galeriaActual);
        $rescate->save();

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        return $rescate;
    }

    // ============================================
    // ✅ ELIMINAR FOTOS
    // ============================================

    public function eliminarFotos(int $id, array $fotosAEliminar): Rescate
    {
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->findOrFail($id);

        $galeriaActual = $rescate->galeria_fotos;
        if (is_string($galeriaActual)) {
            $galeriaActual = json_decode($galeriaActual, true) ?? [];
        } elseif (!is_array($galeriaActual)) {
            $galeriaActual = [];
        }

        $galeriaActual = array_values(array_filter($galeriaActual, function ($item) {
            return is_string($item) && !empty($item);
        }));

        foreach ($fotosAEliminar as $fotoPath) {
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
        $rescate->galeria_fotos = json_encode($galeriaActual);
        $rescate->save();

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        return $rescate;
    }

    // ============================================
    // ✅ ACTUALIZAR ESTADO
    // ============================================

    public function updateEstado(int $id, string $estado): Rescate
    {
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->findOrFail($id);

        $rescate->estado = $estado;
        $rescate->save();

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        return $rescate;
    }

    // ============================================
    // ✅ ACTUALIZAR RESCATE
    // ============================================

    public function update(int $id, array $data, $fotoPrincipal = null, array $fotosAEliminar = []): Rescate
    {
        $entidad = $this->getEntidadCached();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->findOrFail($id);

        if ($fotoPrincipal) {
            if ($rescate->foto_principal) {
                $this->deleteImage($rescate->foto_principal);
            }
            $data['foto_principal'] = $this->uploadImage($fotoPrincipal, 'rescates');
        }

        if (!empty($fotosAEliminar)) {
            $this->eliminarFotos($id, $fotosAEliminar);
        }

        $rescate->update($data);

        // 🔥 LIMPIAR CACHÉ
        $this->clearEntidadCache();

        return $rescate;
    }
}
