<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evento extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'eventos';

    protected $fillable = [
        'nombre_evento',
        'lugar_evento',
        'descripcion',
        'fecha_evento',
        'imagen_url',
        'imagen_public_id', // ✅ AGREGAR ESTE
        'fundacion_id',
        'tipo',
        'likes',
        // Si tienes más campos como fecha_fin, capacidad, etc.
        'fecha_fin',
        'capacidad_maxima',
        'costo',
        'organizador',
        'telefono_contacto',
        'email_contacto',
        'categoria',
        'tags',
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'fecha_fin' => 'datetime',
        'likes' => 'integer',
        'tags' => 'array',
    ];

    // Relación con fundación
    public function fundacion()
    {
        return $this->belongsTo(Fundacion::class, 'fundacion_id');
    }

    // Relación con asistentes (usuarios que confirmaron asistencia)
    public function asistentes()
    {
        return $this->belongsToMany(User::class, 'evento_asistentes', 'evento_id', 'user_id')
                    ->withPivot('estado', 'created_at')
                    ->wherePivot('estado', 'confirmado');
    }

    // Verificar si un usuario específico ha confirmado asistencia
    public function usuarioConfirmoAsistencia(int $userId)
    {
        return $this->asistentes()->where('user_id', $userId)->exists();
    }

    // Contar asistentes confirmados
    public function getTotalAsistentesAttribute()
    {
        return $this->asistentes()->count();
    }
}
