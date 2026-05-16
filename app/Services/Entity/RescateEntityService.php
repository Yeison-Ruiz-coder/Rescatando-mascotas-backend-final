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
     * ✅ NUEVO: Extrae el public_id de Cloudinary desde una URL
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

        // Si es un path como 'rescates/galeria/abc123'
        if (strpos($url, 'rescates/') === 0) {
            return preg_replace('/\.[^.]+$/', '', $url);
        }

        return null;
    }

    /**
     * ✅ NUEVO: Normaliza una URL de imagen para comparación
     */
    private function normalizeImageUrl(?string $url): ?string
    {
        if (!$url) return null;

        // Si es una URL completa de Cloudinary, extraer la parte relevante
        if (strpos($url, 'cloudinary.com') !== false) {
            if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\?|$)/', $url, $matches)) {
                $normalized = preg_replace('/\.[^.]+$/', '', $matches[1]);
                return $normalized;
            }
        }

        // Si ya es un path limpio (como 'rescates/galeria/abc123')
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
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $lat = $entidad->lat ?? null;
        $lng = $entidad->lng ?? null;
        $radio = $entidad->radio_atencion ?? 10;

        $userTipo = $user->tipo;

        $rescates = Rescate::where('estado', 'pendiente')
            ->where(function ($query) use ($userTipo) {
                if ($userTipo === 'veterinaria') {
                    $query->whereIn('tipo_emergencia', ['herido', 'urgente']);
                } else {
                    $query->whereIn('tipo_emergencia', ['abandonado', 'urgente']);
                }
            });

        if ($lat && $lng) {
            $rescates = $rescates->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
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
            ->with(['usuarioReporto', 'mascota'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

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
        ];

        // Agregar foto del rescate si existe
        if ($rescate->foto_principal) {
            $mascotaData['foto_principal'] = $rescate->foto_principal;
        }

        $mascota = Mascota::create($mascotaData);

        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
            $mascota->save();
        }

        // Procesar galería si viene
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

        $rescate->update([
            'mascota_id' => $mascota->id,
            'estado' => 'completado'
        ]);

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
     * ✅ NUEVO: Eliminar fotos específicas de la galería
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

        // Filtrar solo strings válidos
        $galeriaActual = array_values(array_filter($galeriaActual, function ($item) {
            return is_string($item) && !empty($item);
        }));

        Log::info('Eliminando fotos de rescate:', ['fotos_a_eliminar' => $fotosAEliminar]);

        foreach ($fotosAEliminar as $fotoPath) {
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
                    Log::info('🗑️ Eliminando foto de Cloudinary:', ['public_id' => $publicId]);
                    $this->deleteImage($publicId);
                }

                // Eliminar del array
                unset($galeriaActual[$indexToRemove]);
            } else {
                Log::warning('⚠️ Foto no encontrada para eliminar:', ['path' => $fotoPath]);
            }
        }

        // Reindexar
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
     * ✅ NUEVO: Actualizar rescate con manejo de eliminación de fotos
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

        // Actualizar foto principal
        if ($fotoPrincipal) {
            if ($rescate->foto_principal) {
                $this->deleteImage($rescate->foto_principal);
            }
            $data['foto_principal'] = $this->uploadImage($fotoPrincipal, 'rescates');
        }

        // Eliminar fotos de galería
        if (!empty($fotosAEliminar)) {
            $this->eliminarFotos($id, $fotosAEliminar);
        }

        // Actualizar otros campos
        $rescate->update($data);

        return $rescate;
    }
}
