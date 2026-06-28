<?php

namespace App\Services\Entity;

use App\Models\SeguimientoAdopcion;
use App\Models\Adopcion;
use App\Models\Notificacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SeguimientoEntityService
{
    use ImageUploadTrait;

    /**
     * Obtener la entidad (fundación) del usuario autenticado
     */
    public function getEntidad()
    {
        $user = Auth::user();

        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        return null;
    }

    /**
     * ✅ OBTENER TODOS LOS SEGUIMIENTOS DE LA ENTIDAD
     */
    public function getMisSeguimientos(int $perPage = 15)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $adopcionesIds = Adopcion::where('fundacion_id', $entidad->id)->pluck('id');

        return SeguimientoAdopcion::whereIn('adopcion_id', $adopcionesIds)
            ->with(['adopcion.mascota', 'realizadoPor'])
            ->select([
                'id',
                'adopcion_id',
                'tipo_seguimiento',
                'fecha_seguimiento',
                'proximo_seguimiento',
                'observaciones',
                'recomendaciones',
                'estado_mascota',
                'resultado',
                'foto_url',
                'video_url',
                'documento_url',
                'condiciones_hogar',
                'observaciones_hogar',
                'convive_con_otros_animales',
                'comportamiento_observado',
                'realizado_por',
                'realizado_por_nombre',
                'requiere_nuevo_seguimiento',
                'firma_adoptante',
                'fecha_confirmacion',
                'created_at',
                'updated_at',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * ✅ OBTENER SEGUIMIENTOS POR ADOPCIÓN
     */
    public function getSeguimientosPorAdopcion(int $adopcionId, int $perPage = 15)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        // Verificar que la adopción pertenece a la entidad
        $adopcion = Adopcion::where('fundacion_id', $entidad->id)
            ->findOrFail($adopcionId);

        return SeguimientoAdopcion::where('adopcion_id', $adopcionId)
            ->with(['realizadoPor'])
            ->select([
                'id',
                'adopcion_id',
                'tipo_seguimiento',
                'fecha_seguimiento',
                'proximo_seguimiento',
                'observaciones',
                'recomendaciones',
                'estado_mascota',
                'resultado',
                'foto_url',
                'video_url',
                'documento_url',
                'condiciones_hogar',
                'observaciones_hogar',
                'convive_con_otros_animales',
                'comportamiento_observado',
                'realizado_por',
                'realizado_por_nombre',
                'requiere_nuevo_seguimiento',
                'firma_adoptante',
                'fecha_confirmacion',
                'created_at',
                'updated_at',
            ])
            ->orderBy('fecha_seguimiento', 'desc')
            ->paginate($perPage);
    }

    /**
     * ✅ CREAR SEGUIMIENTO
     */
    public function createSeguimiento(int $adopcionId, array $data, array $fotosAdicionales = [])
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        // Verificar que la adopción pertenece a la entidad
        $adopcion = Adopcion::where('fundacion_id', $entidad->id)
            ->findOrFail($adopcionId);

        $seguimientoData = [
            'adopcion_id' => $adopcionId,
            'tipo_seguimiento' => $data['tipo_seguimiento'],
            'fecha_seguimiento' => $data['fecha_seguimiento'],
            'observaciones' => $data['observaciones'],
            'estado_mascota' => $data['estado_mascota'],
            'proximo_seguimiento' => $data['proximo_seguimiento'] ?? null,
            'recomendaciones' => $data['recomendaciones'] ?? null,
            'resultado' => $data['resultado'] ?? 'satisfactorio',
            'condiciones_hogar' => $data['condiciones_hogar'] ?? null,
            'observaciones_hogar' => $data['observaciones_hogar'] ?? null,
            'convive_con_otros_animales' => $data['convive_con_otros_animales'] ?? null,
            'comportamiento_observado' => $data['comportamiento_observado'] ?? null,
            'requiere_nuevo_seguimiento' => $data['requiere_nuevo_seguimiento'] ?? false,
            'firma_adoptante' => $data['firma_adoptante'] ?? false,
            'realizado_por' => $user->id,
            'realizado_por_nombre' => $user->nombre,
        ];

        // Subir foto principal
        if (!empty($data['foto_url']) && $data['foto_url']->isValid()) {
            $seguimientoData['foto_url'] = $this->uploadImage($data['foto_url'], 'seguimientos');
        }

        // Subir documento
        if (!empty($data['documento_url']) && $data['documento_url']->isValid()) {
            $seguimientoData['documento_url'] = $this->uploadImage($data['documento_url'], 'seguimientos/documentos');
        }

        // Video URL (ya viene como string)
        if (!empty($data['video_url'])) {
            $seguimientoData['video_url'] = $data['video_url'];
        }

        // Crear el seguimiento
        $seguimiento = SeguimientoAdopcion::create($seguimientoData);

        // Subir fotos adicionales
        if (!empty($fotosAdicionales)) {
            $fotosUrls = [];
            foreach ($fotosAdicionales as $foto) {
                if ($foto && $foto->isValid()) {
                    $fotosUrls[] = $this->uploadImage($foto, 'seguimientos/fotos');
                }
            }
            if (!empty($fotosUrls)) {
                $seguimiento->fotos_adicionales = json_encode($fotosUrls);
                $seguimiento->save();
            }
        }

        // Notificar al adoptante
        if ($adopcion->user_id) {
            Notificacion::create([
                'user_id' => $adopcion->user_id,
                'contenido' => "Se ha registrado un nuevo seguimiento para tu adopción de {$adopcion->mascota->nombre_mascota}",
                'creado_por_id' => $user->id,
            ]);
        }

        return $seguimiento->load(['realizadoPor']);
    }

    /**
     * ✅ ACTUALIZAR SEGUIMIENTO
     */
    public function updateSeguimiento(int $id, array $data, array $fotosAdicionales = [])
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $seguimiento = $this->findSeguimiento($id);

        // Verificar que la adopción pertenece a la entidad
        $adopcion = Adopcion::where('fundacion_id', $entidad->id)
            ->where('id', $seguimiento->adopcion_id)
            ->first();

        if (!$adopcion) {
            throw new \Exception('No autorizado para modificar este seguimiento');
        }

        $seguimientoData = [
            'tipo_seguimiento' => $data['tipo_seguimiento'] ?? $seguimiento->tipo_seguimiento,
            'fecha_seguimiento' => $data['fecha_seguimiento'] ?? $seguimiento->fecha_seguimiento,
            'observaciones' => $data['observaciones'] ?? $seguimiento->observaciones,
            'estado_mascota' => $data['estado_mascota'] ?? $seguimiento->estado_mascota,
            'proximo_seguimiento' => $data['proximo_seguimiento'] ?? $seguimiento->proximo_seguimiento,
            'recomendaciones' => $data['recomendaciones'] ?? $seguimiento->recomendaciones,
            'resultado' => $data['resultado'] ?? $seguimiento->resultado,
            'condiciones_hogar' => $data['condiciones_hogar'] ?? $seguimiento->condiciones_hogar,
            'observaciones_hogar' => $data['observaciones_hogar'] ?? $seguimiento->observaciones_hogar,
            'convive_con_otros_animales' => $data['convive_con_otros_animales'] ?? $seguimiento->convive_con_otros_animales,
            'comportamiento_observado' => $data['comportamiento_observado'] ?? $seguimiento->comportamiento_observado,
            'requiere_nuevo_seguimiento' => $data['requiere_nuevo_seguimiento'] ?? $seguimiento->requiere_nuevo_seguimiento,
            'firma_adoptante' => $data['firma_adoptante'] ?? $seguimiento->firma_adoptante,
        ];

        // Subir nueva foto principal si se envía
        if (!empty($data['foto_url']) && $data['foto_url']->isValid()) {
            if ($seguimiento->foto_url) {
                $this->deleteImage($seguimiento->foto_url);
            }
            $seguimientoData['foto_url'] = $this->uploadImage($data['foto_url'], 'seguimientos');
        }

        // Subir nuevo documento si se envía
        if (!empty($data['documento_url']) && $data['documento_url']->isValid()) {
            if ($seguimiento->documento_url) {
                $this->deleteImage($seguimiento->documento_url);
            }
            $seguimientoData['documento_url'] = $this->uploadImage($data['documento_url'], 'seguimientos/documentos');
        }

        // Video URL
        if (isset($data['video_url'])) {
            $seguimientoData['video_url'] = $data['video_url'];
        }

        $seguimiento->update($seguimientoData);

        // Agregar nuevas fotos adicionales
        if (!empty($fotosAdicionales)) {
            $fotosActuales = $seguimiento->fotos_adicionales ?? [];
            if (is_string($fotosActuales)) {
                $fotosActuales = json_decode($fotosActuales, true) ?? [];
            }
            $fotosUrls = $fotosActuales;

            foreach ($fotosAdicionales as $foto) {
                if ($foto && $foto->isValid()) {
                    $fotosUrls[] = $this->uploadImage($foto, 'seguimientos/fotos');
                }
            }

            $seguimiento->fotos_adicionales = json_encode($fotosUrls);
            $seguimiento->save();
        }

        return $seguimiento->load(['realizadoPor']);
    }

    /**
     * ✅ ELIMINAR SEGUIMIENTO
     */
    public function deleteSeguimiento(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $seguimiento = $this->findSeguimiento($id);

        // Verificar que la adopción pertenece a la entidad
        $adopcion = Adopcion::where('fundacion_id', $entidad->id)
            ->where('id', $seguimiento->adopcion_id)
            ->first();

        if (!$adopcion) {
            throw new \Exception('No autorizado para eliminar este seguimiento');
        }

        // Eliminar imágenes asociadas
        if ($seguimiento->foto_url) {
            $this->deleteImage($seguimiento->foto_url);
        }

        if ($seguimiento->documento_url) {
            $this->deleteImage($seguimiento->documento_url);
        }

        if ($seguimiento->fotos_adicionales) {
            $fotos = is_string($seguimiento->fotos_adicionales)
                ? json_decode($seguimiento->fotos_adicionales, true)
                : $seguimiento->fotos_adicionales;

            if (is_array($fotos)) {
                foreach ($fotos as $foto) {
                    if ($foto) {
                        $this->deleteImage($foto);
                    }
                }
            }
        }

        $seguimiento->delete();
    }

    /**
     * ✅ ENCONTRAR SEGUIMIENTO POR ID
     */
    public function findSeguimiento(int $id)
    {
        $seguimiento = SeguimientoAdopcion::with(['realizadoPor'])
            ->find($id);

        if (!$seguimiento) {
            throw new ModelNotFoundException('Seguimiento no encontrado');
        }

        return $seguimiento;
    }

    /**
     * ✅ OBTENER SEGUIMIENTOS PENDIENTES
     */
    public function getSeguimientosPendientes(int $perPage = 15)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        // Obtener adopciones de la entidad
        $adopcionesIds = Adopcion::where('fundacion_id', $entidad->id)->pluck('id');

        return SeguimientoAdopcion::whereIn('adopcion_id', $adopcionesIds)
            ->where('requiere_nuevo_seguimiento', true)
            ->whereNotNull('proximo_seguimiento')
            ->whereDate('proximo_seguimiento', '<=', now())
            ->with(['adopcion.mascota', 'realizadoPor'])
            ->select([
                'id',
                'adopcion_id',
                'tipo_seguimiento',
                'fecha_seguimiento',
                'proximo_seguimiento',
                'observaciones',
                'estado_mascota',
                'resultado',
                'realizado_por',
                'created_at',
            ])
            ->orderBy('proximo_seguimiento', 'asc')
            ->paginate($perPage);
    }

    /**
     * ✅ COMPLETAR SEGUIMIENTO (marca como no pendiente)
     */
    public function completarSeguimiento(int $id)
    {
        $seguimiento = $this->findSeguimiento($id);
        $seguimiento->requiere_nuevo_seguimiento = false;
        $seguimiento->save();

        return $seguimiento;
    }

    /**
     * ✅ ESTADÍSTICAS DE SEGUIMIENTOS
     */
    public function getEstadisticas()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $adopcionesIds = Adopcion::where('fundacion_id', $entidad->id)->pluck('id');

        $total = SeguimientoAdopcion::whereIn('adopcion_id', $adopcionesIds)->count();
        $pendientes = SeguimientoAdopcion::whereIn('adopcion_id', $adopcionesIds)
            ->where('requiere_nuevo_seguimiento', true)
            ->whereNotNull('proximo_seguimiento')
            ->whereDate('proximo_seguimiento', '<=', now())
            ->count();

        $porEstado = SeguimientoAdopcion::whereIn('adopcion_id', $adopcionesIds)
            ->selectRaw('estado_mascota, count(*) as total')
            ->groupBy('estado_mascota')
            ->pluck('total', 'estado_mascota')
            ->toArray();

        $porResultado = SeguimientoAdopcion::whereIn('adopcion_id', $adopcionesIds)
            ->selectRaw('resultado, count(*) as total')
            ->groupBy('resultado')
            ->pluck('total', 'resultado')
            ->toArray();

        return [
            'total_seguimientos' => $total,
            'pendientes' => $pendientes,
            'por_estado_mascota' => $porEstado,
            'por_resultado' => $porResultado,
            'ultimos_seguimientos' => SeguimientoAdopcion::whereIn('adopcion_id', $adopcionesIds)
                ->with(['adopcion.mascota', 'realizadoPor'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'fecha' => $item->fecha_seguimiento,
                        'mascota' => $item->adopcion?->mascota?->nombre_mascota,
                        'estado' => $item->estado_mascota,
                        'realizado_por' => $item->realizadoPor?->nombre,
                    ];
                })
        ];
    }
}