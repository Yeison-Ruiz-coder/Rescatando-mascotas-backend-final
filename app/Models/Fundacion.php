<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Fundacion extends Model
{
    use HasFactory, HasScopes;

    protected $allowIncluded = ['mascotas', 'adopciones', 'donaciones'];
    protected $allowFilter = ['id', 'Nombre_1', 'Email', 'Telefono'];
    protected $allowSort = ['id', 'Nombre_1', 'created_at'];

    // Relaciones
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
        return $this->morphMany(Rescate::class, 'entidadResponsable');
    }

    public function usuariosFundacion()
    {
        return $this->hasMany(User::class, 'fundacion_id');
    }

    // Scope para fundaciones que reciben voluntarios
    public function scopeRecibenVoluntarios($query)
    {
        return $query->where('recibe_voluntarios', true);
    }
}
