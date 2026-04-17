<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = [
        'nombre_evento',
        'lugar_evento',
        'descripcion',
        'fecha_evento',
        'imagen_url',
        'fundacion_id',
        'tipo',
        'likes'
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'likes' => 'integer'
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
                    ->withTimestamps();
    }

    // Verificar si un usuario específico ha confirmado asistencia
    public function usuarioConfirmoAsistencia($userId)
    {
        return $this->asistentes()->where('user_id', $userId)->wherePivot('estado', 'confirmado')->exists();
    }

    // Contar asistentes confirmados
    public function getTotalAsistentesAttribute()
    {
        return $this->asistentes()->wherePivot('estado', 'confirmado')->count();
    }
}
