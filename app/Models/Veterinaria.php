<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

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
        'user_id',
        // ===== NUEVOS CAMPOS =====
        'lat',
        'lng',
        'radio_atencion',
        'descripcion',
        'horario_atencion',
        'anios_experiencia',
        'servicios_detallados',
        'equipo_medico',
        'logo',
        'logo_public_id',
        'galeria_fotos',
        'redes_sociales',
        'whatsapp',
        'sitio_web',
        'verificado',
        'documentos_verificacion',
        'precio_consulta',
        'acepta_seguros',
        'valoracion_promedio',
        'total_valoraciones',
        'cobertura_zona',
        'ciudad',
        'departamento',
    ];


    protected $casts = [
    'servicios' => 'array',
    'convenios' => 'array',
    'servicios_detallados' => 'array', // NUEVO
    'equipo_medico' => 'array', // NUEVO
    'galeria_fotos' => 'array', // NUEVO
    'redes_sociales' => 'array', // NUEVO
    'documentos_verificacion' => 'array', //  NUEVO
    'cobertura_zona' => 'array', //  NUEVO
    'urgencias_24h' => 'boolean',
    'verificado' => 'boolean', //  NUEVO
    'acepta_seguros' => 'boolean', // NUEVO
    'lat' => 'decimal:8', // NUEVO
    'lng' => 'decimal:8', // NUEVO
    'valoracion_promedio' => 'decimal:2', // NUEVO
    'precio_consulta' => 'decimal:2', // NUEVO
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
    public function scopeUrgencias24h(Builder $query)
    {
        return $query->where('urgencias_24h', true);
    }
}
