<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veterinaria extends Model
{
    use HasFactory;

    protected $table = 'veterinarias';

    protected $fillable = [
        'Nombre_vet',
        'Direccion',
        'Telefono',
        'Email',
        'servicios',
        'urgencias_24h',
        'convenios',
        'user_id', // AÑADIDO
    ];

    protected $casts = [
        'servicios' => 'array',
        'convenios' => 'array',
        'urgencias_24h' => 'boolean',
    ];

    // NUEVAS RELACIONES
    public function usuarios()
    {
        return $this->hasMany(User::class, 'veterinaria_id');
    }

    public function usuarioPrincipal()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rescates()
    {
        return $this->morphMany(Rescate::class, 'entidad_responsable'); // CORREGIDO
    }

    public function historialesMedicos()
    {
        return $this->hasMany(HistorialMedico::class, 'veterinaria_id');
    }

    // Scope para veterinarias con urgencias 24h
    public function scopeUrgencias24h($query)
    {
        return $query->where('urgencias_24h', true);
    }
}
