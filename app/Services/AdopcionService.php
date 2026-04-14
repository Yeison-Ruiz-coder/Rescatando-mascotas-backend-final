<?php

namespace App\Services;

use App\Models\Adopcion;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class AdopcionService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Adopcion::with(['mascota', 'fundacion', 'adoptante']);

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['fundacion_id'])) {
            $query->where('fundacion_id', $filters['fundacion_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): Adopcion
    {
        return Adopcion::with(['adoptante', 'mascota', 'fundacion', 'administrador', 'solicitud', 'entrevistas', 'seguimientos'])
            ->findOrFail($id);
    }

    public function create(array $data): Adopcion
    {
        if (empty($data['fecha_adopcion'])) {
            $data['fecha_adopcion'] = now();
        }

        $data['administrador_id'] = auth()->id();

        $adopcion = Adopcion::create($data);
        $this->actualizarEstadoMascota($adopcion);

        return $adopcion;
    }

    public function update(int $id, array $data): Adopcion
    {
        $adopcion = Adopcion::findOrFail($id);
        $estadoAnterior = $adopcion->estado;

        $adopcion->update($data);

        if ($estadoAnterior !== $adopcion->estado) {
            $this->actualizarEstadoMascota($adopcion);
        }

        return $adopcion->fresh(['adoptante', 'mascota']);
    }

    public function delete(int $id): void
    {
        $adopcion = Adopcion::findOrFail($id);

        if ($adopcion->seguimientos()->exists() || $adopcion->entrevistas()->exists()) {
            throw new \Exception('No se puede eliminar: tiene seguimientos o entrevistas');
        }

        if ($adopcion->estado === 'completada' && $adopcion->mascota) {
            $adopcion->mascota->update(['estado' => 'En adopcion']);
        }

        $adopcion->delete();
    }

    public function cambiarEstado(int $id, string $estado, ?string $razonRechazo = null): Adopcion
    {
        $adopcion = Adopcion::findOrFail($id);

        $adopcion->estado = $estado;

        if (in_array($estado, ['rechazada', 'cancelada'])) {
            $adopcion->razon_rechazo = $razonRechazo;
            $adopcion->fecha_cierre = now();
        }

        if ($estado === 'completada') {
            $adopcion->fecha_cierre = now();
        }

        $adopcion->save();
        $this->actualizarEstadoMascota($adopcion);

        return $adopcion;
    }

    private function actualizarEstadoMascota(Adopcion $adopcion): void
    {
        if (!$adopcion->mascota) {
            return;
        }

        switch ($adopcion->estado) {
            case 'completada':
                $adopcion->mascota->update(['estado' => 'Adoptado']);
                break;
            case 'en_proceso':
            case 'aprobada':
                $adopcion->mascota->update(['estado' => 'En proceso de adopción']);
                break;
            case 'rechazada':
            case 'cancelada':
                $adopcion->mascota->update(['estado' => 'En adopcion']);
                break;
        }
    }

    public function getSeguimientos(int $id)
    {
        $adopcion = Adopcion::findOrFail($id);
        return $adopcion->seguimientos()->with('realizadoPor')->latest()->get();
    }

    public function crearSeguimiento(int $id, array $data)
    {
        $adopcion = Adopcion::findOrFail($id);

        return $adopcion->seguimientos()->create([
            'descripcion' => $data['descripcion'],
            'fecha_seguimiento' => $data['fecha_seguimiento'] ?? now(),
            'realizado_por' => auth()->id(),
            'realizado_por_nombre' => auth()->user()->nombre,
        ]);
    }
}
