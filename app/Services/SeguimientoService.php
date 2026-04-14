<?php

namespace App\Services;

use App\Models\Adopcion;
use App\Models\SeguimientoAdopcion;
use App\Models\Notificacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SeguimientoService
{
    use ImageUploadTrait;

    public function getByAdopcion(int $adopcionId, int $perPage = 15)
    {
        $adopcion = Adopcion::findOrFail($adopcionId);

        return SeguimientoAdopcion::where('adopcion_id', $adopcionId)
            ->with(['realizadoPor'])
            ->orderBy('fecha_seguimiento', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): SeguimientoAdopcion
    {
        return SeguimientoAdopcion::with(['adopcion', 'realizadoPor'])->findOrFail($id);
    }

    public function create(int $adopcionId, array $data, $fotos = null): SeguimientoAdopcion
    {
        $adopcion = Adopcion::findOrFail($adopcionId);

        $data['adopcion_id'] = $adopcionId;
        $data['realizado_por'] = auth()->id();
        $data['realizado_por_nombre'] = auth()->user()->nombre;

        if (!empty($fotos['foto_url'])) {
            $data['foto_url'] = $this->uploadImage($fotos['foto_url'], 'seguimientos');
        }

        if (!empty($fotos['fotos_adicionales'])) {
            $paths = [];
            foreach ($fotos['fotos_adicionales'] as $foto) {
                $paths[] = $this->uploadImage($foto, 'seguimientos');
            }
            $data['fotos_adicionales'] = json_encode($paths);
        }

        $seguimiento = SeguimientoAdopcion::create($data);

        if ($data['requiere_nuevo_seguimiento'] ?? false) {
            $adopcion->update(['requiere_seguimiento' => true]);
        }

        if (($data['resultado'] ?? '') === 'reingreso') {
            $adopcion->mascota->update(['estado' => 'En adopcion']);
            $adopcion->update(['estado' => 'reingresada']);
        }

        $this->enviarNotificacion($adopcion, $data['resultado'] ?? 'satisfactorio');

        return $seguimiento;
    }

    public function update(int $id, array $data): SeguimientoAdopcion
    {
        $seguimiento = SeguimientoAdopcion::findOrFail($id);
        $seguimiento->update($data);
        return $seguimiento;
    }

    public function delete(int $id): void
    {
        $seguimiento = SeguimientoAdopcion::findOrFail($id);

        if ($seguimiento->foto_url) {
            $this->deleteImage($seguimiento->foto_url);
        }

        if ($seguimiento->fotos_adicionales) {
            foreach (json_decode($seguimiento->fotos_adicionales, true) ?? [] as $foto) {
                $this->deleteImage($foto);
            }
        }

        $seguimiento->delete();
    }

    public function getEstadisticas(int $adopcionId): array
    {
        $adopcion = Adopcion::findOrFail($adopcionId);

        return [
            'total' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->count(),
            'por_tipo' => [
                'virtual' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('tipo_seguimiento', 'virtual')->count(),
                'domiciliario' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('tipo_seguimiento', 'domiciliario')->count(),
                'telefonico' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('tipo_seguimiento', 'telefonico')->count(),
            ],
            'por_resultado' => [
                'satisfactorio' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'satisfactorio')->count(),
                'observaciones' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'observaciones')->count(),
                'incumplimiento' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'incumplimiento')->count(),
                'reingreso' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('resultado', 'reingreso')->count(),
            ],
            'estado_mascota' => [
                'excelente' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'excelente')->count(),
                'bueno' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'bueno')->count(),
                'regular' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'regular')->count(),
                'preocupante' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)->where('estado_mascota', 'preocupante')->count(),
            ],
            'ultimos' => SeguimientoAdopcion::where('adopcion_id', $adopcionId)
                ->with(['realizadoPor'])
                ->orderBy('fecha_seguimiento', 'desc')
                ->limit(5)
                ->get()
        ];
    }

    private function enviarNotificacion(Adopcion $adopcion, string $resultado): void
    {
        $textoResultado = match($resultado) {
            'satisfactorio' => 'Satisfactorio',
            'observaciones' => 'Requiere observaciones',
            'incumplimiento' => 'Incumplimiento detectado',
            'reingreso' => 'Mascota requiere reingreso',
            default => $resultado,
        };

        Notificacion::create([
            'user_id' => $adopcion->user_id,
            'contenido' => "Se ha registrado un seguimiento de tu adopción. Resultado: {$textoResultado}",
            'creado_por_id' => auth()->id(),
        ]);
    }
}
