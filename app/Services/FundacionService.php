<?php

namespace App\Services;

use App\Models\Fundacion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FundacionService
{
    public function getAll(array $filters = [], int $perPage = 15)
    {
        $query = Fundacion::with('usuarioPrincipal');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('Nombre_1', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%")
                  ->orWhere('Telefono', 'like', "%{$search}%")
                  ->orWhere('Direccion', 'like', "%{$search}%")
                  ->orWhere('ciudad', 'like', "%{$search}%");
            });
        }

        if (isset($filters['recibe_voluntarios'])) {
            $query->where('recibe_voluntarios', $filters['recibe_voluntarios']);
        }

        if (isset($filters['verificado'])) {
            $query->where('verificado', $filters['verificado']);
        }

        if (!empty($filters['ciudad'])) {
            $query->where('ciudad', $filters['ciudad']);
        }

        return $query->orderBy('Nombre_1')->paginate($perPage);
    }

    public function getEstadisticas(): array
    {
        return [
            'total' => Fundacion::count(),
            'reciben_voluntarios' => Fundacion::where('recibe_voluntarios', true)->count(),
            'verificadas' => Fundacion::where('verificado', true)->count(),
            'total_mascotas' => Fundacion::withCount('mascotas')->get()->sum('mascotas_count'),
        ];
    }

    public function findById(int $id): Fundacion
    {
        return Fundacion::with([
            'usuarioPrincipal',
            'mascotas' => function($q) {
                $q->whereIn('estado', ['En adopcion', 'Rescatada', 'En acogida']);
            },
            'rescates',
            'donaciones',
            'adopciones'
        ])->findOrFail($id);
    }

    public function getDetalleEstadisticas(Fundacion $fundacion): array
    {
        return [
            'mascotas_activas' => $fundacion->mascotas->count(),
            'adopciones_realizadas' => $fundacion->adopciones()->count(),
            'rescates_gestionados' => $fundacion->rescates()->count(),
            'total_donaciones' => $fundacion->donaciones()->sum('valor_donacion'),
            'donaciones_publicas' => $fundacion->donaciones()->where('publica', true)->sum('valor_donacion'),
            'donaciones_privadas' => $fundacion->donaciones()->where('publica', false)->sum('valor_donacion'),
        ];
    }

    public function create(array $data): Fundacion
    {
        if (isset($data['necesidades_actuales']) && is_array($data['necesidades_actuales'])) {
            $data['necesidades_actuales'] = json_encode($data['necesidades_actuales'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        // Compatibilidad con latitud/longitud
        if (isset($data['latitud']) && !isset($data['lat'])) {
            $data['lat'] = $data['latitud'];
        }
        if (isset($data['longitud']) && !isset($data['lng'])) {
            $data['lng'] = $data['longitud'];
        }

        $data['verificado'] = $data['verificado'] ?? false;

        return Fundacion::create($data);
    }

    public function update(int $id, array $data): Fundacion
    {
        $fundacion = Fundacion::findOrFail($id);

        if (isset($data['necesidades_actuales']) && is_array($data['necesidades_actuales'])) {
            $data['necesidades_actuales'] = json_encode($data['necesidades_actuales'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
        }

        // Compatibilidad con latitud/longitud
        if (isset($data['latitud']) && !isset($data['lat'])) {
            $data['lat'] = $data['latitud'];
        }
        if (isset($data['longitud']) && !isset($data['lng'])) {
            $data['lng'] = $data['longitud'];
        }

        $fundacion->update($data);
        return $fundacion;
    }

    public function delete(int $id): void
    {
        $fundacion = Fundacion::findOrFail($id);

        if ($fundacion->mascotas()->exists()) {
            throw new \Exception('No se puede eliminar la fundación porque tiene mascotas asociadas');
        }

        if ($fundacion->adopciones()->exists()) {
            throw new \Exception('No se puede eliminar la fundación porque tiene adopciones asociadas');
        }

        if ($fundacion->rescates()->exists()) {
            throw new \Exception('No se puede eliminar la fundación porque tiene rescates asociados');
        }

        $fundacion->delete();
    }

    public function getNecesidades(int $id): array
    {
        $fundacion = Fundacion::findOrFail($id);
        $necesidades = $fundacion->necesidades_actuales;
        if (is_string($necesidades)) {
            return json_decode($necesidades, true) ?? [];
        }
        return $necesidades ?? [];
    }

    public function actualizarNecesidades(int $id, array $necesidades): Fundacion
    {
        $fundacion = Fundacion::findOrFail($id);
        $fundacion->update([
            'necesidades_actuales' => json_encode($necesidades, JSON_UNESCAPED_UNICODE)
        ]);
        return $fundacion;
    }

    public function getCercanas(float $lat, float $lng, int $radio = 10)
    {
        return Fundacion::selectRaw(
            "*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distancia",
            [$lat, $lng, $lat]
        )
        ->whereNotNull('lat')
        ->whereNotNull('lng')
        ->having('distancia', '<=', $radio)
        ->orderBy('distancia')
        ->get();
    }
}
