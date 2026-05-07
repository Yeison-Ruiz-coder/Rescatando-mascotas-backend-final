<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'user_id',
        'mascota_id',
        'monto_mensual',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
        'mensaje_apoyo',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto_mensual' => 'decimal:2',
    ];

    // 👤 relación usuario (MEJOR NOMBRE)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🐶 relación mascota
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    // 🔥 scope
    public function scopeActivas(Builder $query)
    {
        return $query->where('estado', 'activo');
    }
}
