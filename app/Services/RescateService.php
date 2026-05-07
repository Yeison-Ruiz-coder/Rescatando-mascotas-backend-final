<?php

namespace App\Services;

use App\Models\Rescate;
use App\Models\Fundacion;
use App\Models\Veterinaria;
use App\Traits\ImageUploadTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RescateService
{
    use ImageUploadTrait;

    public function getAll(array $filters = [], int $perPage = 20)
    {
        $query = Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota', 'reporte']);

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['tipo_emergencia'])) {
            $query->where('tipo_emergencia', $filters['tipo_emergencia']);
        }

        if (!empty($filters['prioridad'])) {
            $query->where('prioridad', $filters['prioridad']);
        }

        $query->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')");
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    public function findById(int $id): Rescate
    {
        return Rescate::with(['usuarioReporto', 'entidadResponsable', 'mascota', 'reporte', 'gestionadoPor'])
            ->findOrFail($id);
    }

    public function getEstadisticas(): array
    {
        return [
            'pendientes' => Rescate::where('estado', 'pendiente')->count(),
            'en_proceso' => Rescate::where('estado', 'en_proceso')->count(),
            'completados' => Rescate::where('estado', 'completado')->count(),
            'seguimiento' => Rescate::where('estado', 'seguimiento')->count(),
            'por_tipo' => [
                'herido' => Rescate::where('tipo_emergencia', 'herido')->count(),
                'abandonado' => Rescate::where('tipo_emergencia', 'abandonado')->count(),
                'urgente' => Rescate::where('tipo_emergencia', 'urgente')->count(),
                'otro' => Rescate::where('tipo_emergencia', 'otro')->count(),
            ],
            'por_prioridad' => [
                'alta' => Rescate::where('prioridad', 'alta')->count(),
                'media' => Rescate::where('prioridad', 'media')->count(),
                'baja' => Rescate::where('prioridad', 'baja')->count(),
            ]
        ];
    }

    public function create(array $data, $fotoPrincipal = null, array $galeriaFotos = []): Rescate
    {
        if ($fotoPrincipal) {
            $data['foto_principal'] = $this->uploadImage($fotoPrincipal, 'rescates');
        }

        if (!empty($galeriaFotos)) {
            $urls = [];
            foreach ($galeriaFotos as $foto) {
                if ($foto && $foto->isValid()) {
                    $urls[] = $this->uploadImage($foto, 'rescates/galeria');
                }
            }
            if (!empty($urls)) {
                $data['galeria_fotos'] = json_encode($urls);
            }
        }

        if (isset($data['galeria_fotos_public_ids']) && is_array($data['galeria_fotos_public_ids'])) {
            $data['galeria_fotos_public_ids'] = json_encode($data['galeria_fotos_public_ids']);
        }

        if (isset($data['fotos_metadata']) && is_array($data['fotos_metadata'])) {
            $data['fotos_metadata'] = json_encode($data['fotos_metadata'], JSON_UNESCAPED_UNICODE);
        }

        return Rescate::create($data);
    }

    public function update(int $id, array $data, $fotoPrincipal = null): Rescate
    {
        $rescate = Rescate::findOrFail($id);

        if ($fotoPrincipal) {
            $data['foto_principal'] = $this->uploadImage($fotoPrincipal, 'rescates', $rescate->foto_principal);
        }

        if (isset($data['galeria_fotos']) && is_array($data['galeria_fotos'])) {
            $data['galeria_fotos'] = json_encode($data['galeria_fotos']);
        }

        if (isset($data['fotos_metadata']) && is_array($data['fotos_metadata'])) {
            $data['fotos_metadata'] = json_encode($data['fotos_metadata'], JSON_UNESCAPED_UNICODE);
        }

        $rescate->update($data);
        return $rescate;
    }

    public function delete(int $id): void
    {
        $rescate = Rescate::findOrFail($id);

        if ($rescate->foto_principal) {
            $this->deleteImage($rescate->foto_principal);
        }

        if ($rescate->galeria_fotos) {
            $galeria = is_string($rescate->galeria_fotos) ? json_decode($rescate->galeria_fotos, true) : $rescate->galeria_fotos;
            if (is_array($galeria)) {
                foreach ($galeria as $foto) {
                    $this->deleteImage($foto);
                }
            }
        }

        $rescate->delete();
    }

    public function asignar(int $id, string $entidadTipo, int $entidadId): Rescate
    {
        $rescate = Rescate::findOrFail($id);

        $entidad = null;
        if ($entidadTipo === 'fundacion') {
            $entidad = Fundacion::findOrFail($entidadId);
            $rescate->entidad_responsable_type = Fundacion::class;
        } else {
            $entidad = Veterinaria::findOrFail($entidadId);
            $rescate->entidad_responsable_type = Veterinaria::class;
        }

        $rescate->entidad_responsable_id = $entidad->id;
        $rescate->estado = 'en_proceso';
        $rescate->save();

        return $rescate;
    }

    public function updateEstado(int $id, string $estado): Rescate
    {
        $rescate = Rescate::findOrFail($id);
        $rescate->estado = $estado;
        $rescate->save();
        return $rescate;
    }

    public function agregarFotos(int $id, array $nuevasFotos): Rescate
    {
        $rescate = Rescate::findOrFail($id);

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
}
