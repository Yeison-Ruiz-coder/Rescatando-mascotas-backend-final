<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasScopes;

class Fundacion extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'fundaciones';

    protected $allowIncluded = ['mascotas', 'adopciones', 'donaciones', 'usuarios']; // Añadido 'usuarios'
    protected $allowFilter = ['id', 'Nombre_1', 'Email', 'Telefono'];
    protected $allowSort = ['id', 'Nombre_1', 'created_at'];

    protected $fillable = [
        'Nombre_1',
        'Direccion',
        'Telefono',
        'Email',
        'registro_sanitario',
        'capacidad_maxima',
        'necesidades_actuales',
        'horario_atencion',
        'recibe_voluntarios',
        'user_id',
        // ===== NUEVOS CAMPOS =====
        'lat',
        'lng',
        'radio_atencion',
        'imagen_portada',
        'imagen_portada_public_id',
        'verificado',
        'ciudad',
        'fecha_fundacion',
    ];

    protected $casts = [
        'necesidades_actuales' => 'array',
        'recibe_voluntarios' => 'boolean',
        'capacidad_maxima' => 'integer',
        'verificado' => 'boolean', //  NUEVO
        'lat' => 'decimal:8', //  NUEVO
        'lng' => 'decimal:8', //  NUEVO
        'fecha_fundacion' => 'date', //  NUEVO
    ];

    // Relaciones
    public function usuarios() // NUEVA - Relación con users
    {
        return $this->hasMany(User::class, 'fundacion_id');
    }

    public function usuarioPrincipal() // NUEVA - Usuario principal/administrador de la fundación
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mascotas()
    {
        return $this->hasMany(Mascota::class, 'fundacion_id');
    }

    public function adopciones()
    {
        return $this->hasMany(Adopcion::class, 'fundacion_id');
    }

    public function donaciones()
    {
        return $this->hasMany(Donacion::class, 'fundacion_id');
    }

    public function rescates()
    {
        return $this->morphMany(Rescate::class, 'entidad_responsable'); // CORREGIDO: 'entidad_responsable' (debe coincidir con el morph en Rescate)
    }

    // Scope para fundaciones que reciben voluntarios
    public function scopeRecibenVoluntarios(Builder $query)
    {
        return $query->where('recibe_voluntarios', true);
    }
}
