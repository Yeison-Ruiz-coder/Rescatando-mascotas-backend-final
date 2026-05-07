<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Mensaje;
use Illuminate\Database\Eloquent\Builder;

class Conversacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'conversaciones';

    protected $fillable = [
        'participante1_id',
        'participante2_id',
        'ultimo_mensaje_at',
        'ultimo_mensaje',
        'activa',
        'fecha_cierre',
        'cerrada_por',
    ];

    protected $casts = [
        'ultimo_mensaje_at' => 'datetime',
        'fecha_cierre' => 'datetime',
        'activa' => 'boolean',
    ];

    // Relaciones
    public function participante1()
    {
        return $this->belongsTo(User::class, 'participante1_id');
    }

    public function participante2()
    {
        return $this->belongsTo(User::class, 'participante2_id');
    }

    public function cerradaPor()
    {
        return $this->belongsTo(User::class, 'cerrada_por');
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class, 'conversacion_id');
    }

    public function ultimoMensaje()
    {
        return $this->belongsTo(Mensaje::class, 'id', 'conversacion_id');
    }

    // Scope para conversaciones activas de un usuario
    public function scopeActivasPorUsuario(Builder $query, int $userId)
    {
        return $query->where('activa', true)
            ->where(function ($q) use ($userId) {
                $q->where('participante1_id', $userId)
                    ->orWhere('participante2_id', $userId);
            });
    }
}
