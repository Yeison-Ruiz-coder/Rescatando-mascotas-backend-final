<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class NotificacionService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Notificacion::with(['usuario', 'creadoPor']);

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['creado_por_id'])) {
            $query->where('creado_por_id', $filters['creado_por_id']);
        }

        if (!empty($filters['fecha_inicio'])) {
            $query->whereDate('fecha_envio', '>=', $filters['fecha_inicio']);
        }

        if (!empty($filters['fecha_fin'])) {
            $query->whereDate('fecha_envio', '<=', $filters['fecha_fin']);
        }

        if (!empty($filters['search'])) {
            $query->where('contenido', 'like', "%{$filters['search']}%");
        }

        return $query->orderBy('fecha_envio', 'desc')->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => Notificacion::count(),
            'hoy' => Notificacion::whereDate('fecha_envio', today())->count(),
            'esta_semana' => Notificacion::where('fecha_envio', '>=', now()->subDays(7))->count(),
            'usuarios_notificados' => Notificacion::distinct('user_id')->count('user_id'),
            'promedio_diario' => round(Notificacion::count() / max(1, Notificacion::distinct(DB::raw('DATE(fecha_envio)'))->count()), 1),
        ];
    }

    public function findById(int $id): Notificacion
    {
        return Notificacion::with(['usuario', 'creadoPor'])->findOrFail($id);
    }

    public function create(array $data): Notificacion
    {
        $data['creado_por_id'] = auth()->id();

        if (!isset($data['fecha_envio'])) {
            $data['fecha_envio'] = now();
        }

        return Notificacion::create($data);
    }

    public function update(int $id, array $data): Notificacion
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->update($data);
        return $notificacion->fresh(['usuario', 'creadoPor']);
    }

    public function delete(int $id): void
    {
        $notificacion = Notificacion::findOrFail($id);
        $notificacion->delete();
    }

    public function getPorUsuario(int $userId, array $filters = [], int $perPage = 15)
    {
        $user = User::findOrFail($userId);

        $query = Notificacion::with('creadoPor')->where('user_id', $userId);

        if (!empty($filters['no_leidas'])) {
            // $query->where('leida', false);
        }

        $notificaciones = $query->orderBy('fecha_envio', 'desc')->paginate($perPage);

        $noLeidas = 0;

        return [
            'data' => $notificaciones,
            'estadisticas' => [
                'total' => Notificacion::where('user_id', $userId)->count(),
                'no_leidas' => $noLeidas,
            ],
        ];
    }

    public function enviarMasivo(array $data): array
    {
        if (!empty($data['user_ids'])) {
            $destinatarios = User::whereIn('id', $data['user_ids'])
                ->where('estado', 'activo')
                ->get();
        } else {
            $query = User::where('estado', 'activo');

            switch ($data['tipo_destinatarios']) {
                case 'administradores':
                    $query->where('tipo', 'admin');
                    break;
                case 'fundaciones':
                    $query->where('tipo', 'fundacion');
                    break;
                case 'veterinarias':
                    $query->where('tipo', 'veterinaria');
                    break;
                case 'usuarios':
                    $query->where('tipo', 'user');
                    break;
            }

            $destinatarios = $query->get();
        }

        $fechaEnvio = $data['fecha_envio'] ?? now();
        $notificacionesCreadas = [];

        foreach ($destinatarios as $destinatario) {
            $notificacionesCreadas[] = Notificacion::create([
                'contenido' => $data['contenido'],
                'user_id' => $destinatario->id,
                'creado_por_id' => auth()->id(),
                'fecha_envio' => $fechaEnvio,
            ]);
        }

        return [
            'total_enviadas' => count($notificacionesCreadas),
            'tipo_destinatarios' => $data['tipo_destinatarios'],
            'fecha_envio' => $fechaEnvio,
        ];
    }

    public function getEstadisticasAvanzadas(): array
    {
        $ultimos7Dias = collect();
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $ultimos7Dias->push([
                'fecha' => $fecha->format('Y-m-d'),
                'total' => Notificacion::whereDate('fecha_envio', $fecha)->count(),
            ]);
        }

        $notificacionesPorAdmin = Notificacion::with('creadoPor')
            ->select('creado_por_id', DB::raw('count(*) as total'))
            ->whereNotNull('creado_por_id')
            ->groupBy('creado_por_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'admin' => $item->creadoPor?->nombre ?? 'Desconocido',
                    'total' => $item->total,
                ];
            });

        $notificacionesPorUsuario = Notificacion::with('usuario')
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'usuario' => $item->usuario?->nombre ?? 'Usuario eliminado',
                    'total' => $item->total,
                ];
            });

        return [
            'totales' => [
                'total' => Notificacion::count(),
                'hoy' => Notificacion::whereDate('fecha_envio', today())->count(),
                'esta_semana' => Notificacion::where('fecha_envio', '>=', now()->startOfWeek())->count(),
                'este_mes' => Notificacion::whereMonth('fecha_envio', now()->month)->count(),
            ],
            'ultimos_7_dias' => $ultimos7Dias,
            'top_administradores' => $notificacionesPorAdmin,
            'top_usuarios_notificados' => $notificacionesPorUsuario,
        ];
    }
}
