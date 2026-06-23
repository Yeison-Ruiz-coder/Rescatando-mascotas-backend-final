<?php

namespace App\Services\Public;

use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\DB;

class RescatePublicService
{
    use ImageUploadTrait;

    public function getAll($perPage = 15)
    {
        return Rescate::with(['mascota', 'usuarioReporto'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id)
    {
        return Rescate::with(['mascota', 'usuarioReporto'])
            ->findOrFail($id);
    }

    /**
     * Reportar un rescate (PÚBLICO)
     * Asigna automáticamente la entidad responsable más cercana
     */
    public function reportar(array $data, $fotoPrincipal = null, array $galeriaFotos = [])
    {
        // ✅ Subir fotos si existen
        if ($fotoPrincipal) {
            $data['foto_principal'] = $this->uploadImage($fotoPrincipal, 'rescates');
        }

        if (!empty($galeriaFotos)) {
            $galeriaUrls = [];
            foreach ($galeriaFotos as $foto) {
                if ($foto) {
                    $galeriaUrls[] = $this->uploadImage($foto, 'rescates/galeria');
                }
            }
            $data['galeria_fotos'] = json_encode($galeriaUrls);
        }

        // ✅ ASIGNAR ENTIDAD RESPONSABLE MÁS CERCANA
        $entidad = $this->findNearestEntity($data['lat'] ?? null, $data['lng'] ?? null);

        $data['estado'] = 'pendiente';
        $data['tipo_emergencia'] = $data['tipo_emergencia'] ?? 'otro';
        $data['prioridad'] = $data['prioridad'] ?? 'baja';

        // ✅ Guardar el rescate con la entidad asignada (si existe)
        $rescate = Rescate::create([
            'fecha_rescate' => $data['fecha_rescate'],
            'lugar_rescate' => $data['lugar_rescate'],
            'descripcion_rescate' => $data['descripcion_rescate'],
            'foto_principal' => $data['foto_principal'] ?? null,
            'galeria_fotos' => $data['galeria_fotos'] ?? null,
            'estado' => $data['estado'],
            'tipo_emergencia' => $data['tipo_emergencia'],
            'prioridad' => $data['prioridad'],
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'nombre_reportante' => $data['nombre_reportante'] ?? null,
            'email_reportante' => $data['email_reportante'] ?? null,
            'telefono_reportante' => $data['telefono_reportante'] ?? null,
            'usuario_reporto_id' => auth()->id() ?? null,
            // ✅ ENTIDAD RESPONSABLE ASIGNADA
            'entidad_responsable_type' => $entidad ? get_class($entidad) : null,
            'entidad_responsable_id' => $entidad ? $entidad->id : null,
        ]);

        return $rescate;
    }

    /**
     * Encuentra la entidad (fundación o veterinaria) más cercana
     */
    private function findNearestEntity(?float $lat, ?float $lng, int $radius = 50)
    {
        if (!$lat || !$lng) {
            return null;
        }

        // Buscar en fundaciones
        $fundacion = Fundacion::select(
            'id',
            'user_id',
            'Nombre_1 as nombre',
            'lat',
            'lng',
            DB::raw("
                ( 6371 * acos(
                    cos( radians(?) ) *
                    cos( radians( lat ) ) *
                    cos( radians( lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( lat ) )
                ) ) AS distance
            ")
        )
        ->setBindings([$lat, $lng, $lat])
        ->having('distance', '<', $radius)
        ->orderBy('distance')
        ->first();

        if ($fundacion) {
            return $fundacion;
        }

        // Buscar en veterinarias
        $veterinaria = Veterinaria::select(
            'id',
            'user_id',
            'Nombre_vet as nombre',
            'lat',
            'lng',
            DB::raw("
                ( 6371 * acos(
                    cos( radians(?) ) *
                    cos( radians( lat ) ) *
                    cos( radians( lng ) - radians(?) ) +
                    sin( radians(?) ) *
                    sin( radians( lat ) )
                ) ) AS distance
            ")
        )
        ->setBindings([$lat, $lng, $lat])
        ->having('distance', '<', $radius)
        ->orderBy('distance')
        ->first();

        return $veterinaria;
    }

    /**
     * Marcar rescate como disponible para administradores
     */
    public function marcarDisponibleParaAdmin(int $rescateId)
    {
        $rescate = Rescate::findOrFail($rescateId);
        $rescate->update([
            'disponible_para_admin' => true
        ]);
        return $rescate;
    }
}
