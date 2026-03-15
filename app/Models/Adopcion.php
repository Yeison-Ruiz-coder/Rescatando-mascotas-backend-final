<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Adopcion extends Model
{
    use HasFactory, HasScopes;

    protected $allowIncluded = [
        'solicitud',
        'adoptante',
        'mascota',
        'fundacion',
        'administrador',
        'entrevistas',
        'seguimientos'
    ];

    protected $allowFilter = ['id', 'estado'];
    protected $allowSort = ['id', 'fecha_adopcion', 'created_at'];

    // Relaciones
    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'solicitud_id');
    }

    public function adoptante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function fundacion()
    {
        return $this->belongsTo(Fundacion::class, 'fundacion_id');
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }

    public function entrevistas()
    {
        return $this->hasMany(Entrevista::class, 'adopcion_id');
    }

    public function seguimientos()
    {
        return $this->hasMany(SeguimientoAdopcion::class, 'adopcion_id');
    }

    // Scopes
    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado', 'completada');
    }
}
