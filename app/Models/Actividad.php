<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasScopes;

class Actividad extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'actividades';

    protected $allowSelect = ['id','user_id','accion','tabla','registro_id','descripcion','valores_viejos','valores_nuevos','cambios','ip','user_agent','created_at','updated_at'];
    protected $allowIncluded = ['usuario'];
    protected $allowFilter = ['id','accion','tabla','user_id'];
    protected $allowSort = ['id','created_at','accion','tabla'];

    protected $fillable = [
        'user_id',
        'accion',
        'tabla',
        'registro_id',
        'descripcion',
        'valores_viejos',
        'valores_nuevos',
        'cambios',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'valores_viejos' => 'array',
        'valores_nuevos' => 'array',
        'cambios' => 'array',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scope por usuario
    public function scopePorUsuario(Builder $query,int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope por acción
    public function scopePorAccion(Builder $query, string $accion)
    {
        return $query->where('accion', $accion);
    }

    // Scope por tabla
    public function scopePorTabla(Builder $query, string $tabla)
    {
        return $query->where('tabla', $tabla);
    }

    // Scope por fecha
    public function scopeUltimasHoras(Builder $query, int $horas = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($horas));
    }
}
