<?php

namespace App\Services\Public;

use App\Models\Comentario;
use App\Models\Mascota;
use App\Models\Evento;
use App\Models\Fundacion;

class ComentarioPublicService
{
    private $modelMap = [
        'mascota' => Mascota::class,
        'evento' => Evento::class,
        'fundacion' => Fundacion::class,
    ];

    private function getModelClass(string $tipo): ?string
    {
        return $this->modelMap[$tipo] ?? null;
    }

    public function getComentarios(string $entidadTipo, int $entidadId): array
    {
        $modelClass = $this->getModelClass($entidadTipo);

        if (!$modelClass) {
            throw new \Exception('Tipo de entidad no válido');
        }

        $entidad = $modelClass::find($entidadId);
        if (!$entidad) {
            throw new \Exception('Entidad no encontrada');
        }

        return Comentario::query()
            ->selectFields()
            ->where('comentable_type', $modelClass)
            ->where('comentable_id', $entidadId)
            ->with('usuario:id,nombre,avatar')
            ->latest()
            ->get()
            ->toArray();
    }

    public function crearComentario(array $data, int $userId): Comentario
    {
        $modelClass = $this->getModelClass($data['entidad_tipo']);

        if (!$modelClass) {
            throw new \Exception('Tipo de entidad no válido');
        }

        $entidad = $modelClass::find($data['entidad_id']);
        if (!$entidad) {
            throw new \Exception('La entidad no existe');
        }

        return Comentario::create([
            'contenido' => $data['contenido'],
            'fecha' => now(),
            'user_id' => $userId,
            'comentable_type' => $modelClass,
            'comentable_id' => $data['entidad_id'],
        ]);
    }
}
