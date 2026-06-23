<?php

namespace App\Services;

use App\Models\Solicitud;
use App\Models\Adopcion;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class SolicitudService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Solicitud::with(['usuario', 'revisor', 'solicitable'])
            ->orderBy('fecha_solicitud', 'desc');

        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (!empty($filters['tipo_solicitud'])) {
            $query->where('tipo_solicitud', $filters['tipo_solicitud']);
        }

        if (!empty($filters['buscar'])) {
            $buscar = $filters['buscar'];
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('email_solicitante', 'like', "%{$buscar}%")
                  ->orWhere('telefono_solicitante', 'like', "%{$buscar}%")
                  ->orWhereHas('usuario', function($userQuery) use ($buscar) {
                      $userQuery->where('nombre', 'like', "%{$buscar}%")
                               ->orWhere('apellidos', 'like', "%{$buscar}%")
                               ->orWhere('email', 'like', "%{$buscar}%");
                  });
            });
        }

        return $query->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'pendientes' => Solicitud::where('estado', 'pendiente')->count(),
            'en_revision' => Solicitud::where('estado', 'en_revision')->count(),
            'aprobadas' => Solicitud::where('estado', 'aprobada')->count(),
            'rechazadas' => Solicitud::where('estado', 'rechazada')->count(),
            'total' => Solicitud::count(),
        ];
    }

    public function findById(int $id): Solicitud
    {
        return Solicitud::with(['usuario', 'revisor', 'solicitable'])->findOrFail($id);
    }

    public function create(array $data): Solicitud
    {
        $data['fecha_solicitud'] = now();
        $data['estado'] = 'pendiente';

        $solicitud = Solicitud::create($data);

        if ($data['tipo_solicitud'] === 'adopcion' && isset($data['datos_adopcion'])) {
            $solicitud->setDatosAdopcion($data['datos_adopcion'])->save();
        }

        return $solicitud;
    }

    public function update(int $id, array $data): Solicitud
    {
        $solicitud = Solicitud::findOrFail($id);
        $solicitud->update($data);
        return $solicitud->fresh(['usuario', 'revisor', 'solicitable']);
    }

    public function delete(int $id): void
    {
        $solicitud = Solicitud::findOrFail($id);
        $solicitud->delete();
    }

    public function cambiarEstado(int $id, string $estado, ?string $razonRechazo = null): Solicitud
    {
        $solicitud = Solicitud::with('solicitable')->findOrFail($id);

        $solicitud->estado = $estado;
        $solicitud->revisado_por = auth()->id();
        $solicitud->fecha_revision = now();

        if ($estado === 'rechazada') {
            $solicitud->razon_rechazo = $razonRechazo;
        }

        $solicitud->save();

        if ($estado === 'aprobada' &&
            $solicitud->tipo_solicitud === 'adopcion' &&
            $solicitud->solicitable_type === Mascota::class) {

            Adopcion::create([
                'solicitud_id' => $solicitud->id,
                'user_id' => $solicitud->user_id,
                'mascota_id' => $solicitud->solicitable_id,
                'fundacion_id' => $solicitud->solicitable->fundacion_id ?? null,
                'administrador_id' => auth()->id(),
                'estado' => 'en_proceso',
                'fecha_adopcion' => now(),
            ]);
        }

        return $solicitud;
    }

    public function getEstadisticasAvanzadas(): array
    {
        $porEstado = [
            'pendiente' => Solicitud::where('estado', 'pendiente')->count(),
            'en_revision' => Solicitud::where('estado', 'en_revision')->count(),
            'aprobada' => Solicitud::where('estado', 'aprobada')->count(),
            'rechazada' => Solicitud::where('estado', 'rechazada')->count(),
            'completada' => Solicitud::where('estado', 'completada')->count(),
        ];

        $porTipo = [
            'adopcion' => Solicitud::where('tipo_solicitud', 'adopcion')->count(),
            'rescate' => Solicitud::where('tipo_solicitud', 'rescate')->count(),
            'apadrinamiento' => Solicitud::where('tipo_solicitud', 'apadrinamiento')->count(),
            'donacion' => Solicitud::where('tipo_solicitud', 'donacion')->count(),
            'otro' => Solicitud::where('tipo_solicitud', 'otro')->count(),
        ];

        $ultimas30Dias = Solicitud::where('fecha_solicitud', '>=', now()->subDays(30))->count();

        $tiempoPromedio = $this->calcularTiempoPromedioResolucion();

        return [
            'por_estado' => $porEstado,
            'por_tipo' => $porTipo,
            'ultimas_30_dias' => $ultimas30Dias,
            'tiempo_promedio_resolucion' => $tiempoPromedio,
        ];
    }

    private function calcularTiempoPromedioResolucion(): float
    {
        $solicitudesResueltas = Solicitud::whereNotNull('fecha_revision')
            ->whereIn('estado', ['aprobada', 'rechazada', 'completada'])
            ->get();

        if ($solicitudesResueltas->isEmpty()) {
            return 0;
        }

        $totalDias = $solicitudesResueltas->sum(function($solicitud) {
            return $solicitud->fecha_solicitud->diffInDays($solicitud->fecha_revision);
        });

        return round($totalDias / $solicitudesResueltas->count(), 1);
    }
}
