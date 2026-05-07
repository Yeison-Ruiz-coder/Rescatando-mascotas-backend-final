<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Valoracion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'valoraciones';

    protected $fillable = [
        'calificable_id',
        'calificable_type',
        'user_id',
        'puntuacion',
        'comentario',
        'puntuacion_atencion',
        'puntuacion_profesionalismo',
        'puntuacion_instalaciones',
        'puntuacion_precio',
        'respuesta',
        'fecha_respuesta',
        'aprobada',
        'fecha_aprobacion',
        'aprobada_por',
        'fotos',
        'anonima',
        'ip_creacion',
        'user_agent',
    ];

    protected $casts = [
        'fotos' => 'array',
        'puntuacion' => 'integer',
        'puntuacion_atencion' => 'integer',
        'puntuacion_profesionalismo' => 'integer',
        'puntuacion_instalaciones' => 'integer',
        'puntuacion_precio' => 'integer',
        'aprobada' => 'boolean',
        'anonima' => 'boolean',
        'fecha_respuesta' => 'datetime',
        'fecha_aprobacion' => 'datetime',
    ];

    // Relaciones
    public function calificable()
    {
        return $this->morphTo();
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aprobadaPor()
    {
        return $this->belongsTo(User::class, 'aprobada_por');
    }

    // Scopes
    public function scopeAprobadas(Builder $query)
    {
        return $query->where('aprobada', true);
    }

    public function scopePorPuntuacion(Builder $query, int $puntuacion)
    {
        return $query->where('puntuacion', $puntuacion);
    }
}
