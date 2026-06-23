<?php

namespace App\Services\Entity;

use App\Models\Rescate;
use App\Models\Mascota;
use App\Models\Notificacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class RescateEntityService
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

    /**
     * Extrae el public_id de Cloudinary desde una URL
     */
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

    /**
     * Normaliza una URL de imagen para comparación
     */
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

    public function findById(int $id): Rescate
    {
        return Rescate::with(['usuarioReporto', 'mascota', 'entidadResponsable'])
            ->findOrFail($id);
    }

    public function completarRescate(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('estado', 'en_proceso')
            ->findOrFail($id);

        $rescate->update(['estado' => 'completado']);

        return $rescate;
    }

    public function getRescatesDisponibles(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            throw new \Exception('Usuario no autenticado');
        }

        $entidad = $this->getEntidad();

        if (!$entidad) {
            return collect([]);
        }

        $lat = $entidad->lat ?? null;
        $lng = $entidad->lng ?? null;
        $radio = $entidad->radio_atencion ?? 10;
        $userTipo = $user->tipo;

        $rescates = Rescate::where('estado', 'pendiente')
            ->where('tipo_emergencia', '!=', 'otro')
            ->where(function ($query) use ($userTipo) {
                if ($userTipo === 'veterinaria') {
                    $query->whereIn('tipo_emergencia', ['herido', 'urgente']);
                } elseif ($userTipo === 'fundacion') {
                    $query->whereIn('tipo_emergencia', ['abandonado', 'urgente']);
                }
            });

        if ($lat && $lng) {
            $rescates = $rescates->selectRaw("
                *,
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

        return $rescates->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(15);
    }

    public function aceptarRescate(int $id)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

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

    public function rechazarRescate(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('estado', 'pendiente')->findOrFail($id);

        $rescate->update([
            'estado' => 'pendiente'
        ]);

        return $rescate;
    }

    public function getMisRescates()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        return Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('tipo_emergencia', '!=', 'otro')
            ->with(['usuarioReporto', 'mascota'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    /**
     * ✅ REGISTRAR MASCOTA DESDE RESCATE - CORREGIDO
     * Ahora guarda razas, vacunas, enfermedades, medicamentos y requisitos
     */
    public function registrarMascotaDesdeRescate(int $id, array $data, $files = null)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('estado', 'en_proceso')
            ->findOrFail($id);

        // ============================================
        // 1. DATOS BÁSICOS DE LA MASCOTA
        // ============================================
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
            'estado' => ($data['necesita_hogar_temporal'] ?? false) ? 'En acogida' : 'En adopcion',
            'fundacion_id' => $user->tipo === 'fundacion' ? $entidad->id : null,
            'lugar_rescate' => $rescate->lugar_rescate,

            // ===== NUEVOS CAMPOS =====
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

        // Agregar foto del rescate si existe
        if ($rescate->foto_principal) {
            $mascotaData['foto_principal'] = $rescate->foto_principal;
        }

        $mascota = Mascota::create($mascotaData);

        // ============================================
        // 2. SUBIR FOTO PRINCIPAL (si se envió una nueva)
        // ============================================
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
            $mascota->save();
        }

        // ============================================
        // 3. PROCESAR GALERÍA DE FOTOS
        // ============================================
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

        // ============================================
        // 4. ✅ GUARDAR RAZAS
        // ============================================
        if (isset($data['razas']) && is_array($data['razas']) && !empty($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        // ============================================
        // 5. ✅ GUARDAR VACUNAS
        // ============================================
        if (isset($data['vacunas']) && is_array($data['vacunas']) && !empty($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        // ============================================
        // 6. ✅ GUARDAR ENFERMEDADES CRÓNICAS
        // ============================================
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas']) && !empty($data['enfermedades_cronicas'])) {
            $mascota->enfermedades_cronicas = json_encode(array_values($data['enfermedades_cronicas']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // ============================================
        // 7. ✅ GUARDAR MEDICAMENTOS
        // ============================================
        if (isset($data['medicamentos']) && is_array($data['medicamentos']) && !empty($data['medicamentos'])) {
            $mascota->medicamentos = json_encode(array_values($data['medicamentos']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // ============================================
        // 8. ✅ GUARDAR REQUISITOS DE ADOPCIÓN
        // ============================================
        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion']) && !empty($data['requisitos_adopcion'])) {
            $mascota->requisitos_adopcion = json_encode(array_values($data['requisitos_adopcion']), JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // ============================================
        // 9. ACTUALIZAR EL RESCATE
        // ============================================
        $rescate->update([
            'mascota_id' => $mascota->id,
            'estado' => 'completado'
        ]);

        // ============================================
        // 10. NOTIFICAR AL REPORTANTE
        // ============================================
        if ($rescate->usuario_reporto_id) {
            Notificacion::create([
                'user_id' => $rescate->usuario_reporto_id,
                'contenido' => "La mascota que reportaste ({$mascota->nombre_mascota}) ha sido registrada y está en proceso de adopción",
                'creado_por_id' => $user->id,
            ]);
        }

        return $mascota->load(['razas', 'vacunas']);
    }

    public function agregarFotos(int $id, array $nuevasFotos): Rescate
    {
        $entidad = $this->getEntidad();

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

        return $rescate;
    }

    /**
     * Eliminar fotos específicas de la galería
     */
    public function eliminarFotos(int $id, array $fotosAEliminar): Rescate
    {
        $entidad = $this->getEntidad();

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

        Log::info('Eliminando fotos de rescate:', ['fotos_a_eliminar' => $fotosAEliminar]);

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
                    Log::info('🗑️ Eliminando foto de Cloudinary:', ['public_id' => $publicId]);
                    $this->deleteImage($publicId);
                }
                unset($galeriaActual[$indexToRemove]);
            } else {
                Log::warning('⚠️ Foto no encontrada para eliminar:', ['path' => $fotoPath]);
            }
        }

        $galeriaActual = array_values($galeriaActual);

        $rescate->galeria_fotos = json_encode($galeriaActual);
        $rescate->save();

        Log::info('Galería después de eliminar:', $galeriaActual);

        return $rescate;
    }

    public function updateEstado(int $id, string $estado): Rescate
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->findOrFail($id);

        $rescate->estado = $estado;
        $rescate->save();

        return $rescate;
    }

    /**
     * Actualizar rescate con manejo de eliminación de fotos
     */
    public function update(int $id, array $data, $fotoPrincipal = null, array $fotosAEliminar = []): Rescate
    {
        $entidad = $this->getEntidad();

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

        return $rescate;
    }
}
