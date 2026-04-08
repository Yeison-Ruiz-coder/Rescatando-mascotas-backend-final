<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;
use App\Traits\Translatable;

class Mascota extends Model
{
    use HasFactory, Translatable, HasScopes;

    protected $table = 'mascotas';

    protected $fillable = [
        'nombre_mascota',
        'especie',
        'edad_aprox',
        'genero',
        'estado',
        'lugar_rescate',
        'descripcion',
        'condiciones_especiales',
        'foto_principal',
        'galeria_fotos',
        'necesita_hogar_temporal',
        'apto_con_ninos',
        'apto_con_otros_animales',
        'fecha_ingreso',
        'fecha_salida',
        'fundacion_id',
    ];

    protected $casts = [
        'galeria_fotos' => 'array',
        'necesita_hogar_temporal' => 'boolean',
        'apto_con_ninos' => 'boolean',
        'apto_con_otros_animales' => 'boolean',
        'fecha_ingreso' => 'date',
        'fecha_salida' => 'date',
        'edad_aprox' => 'decimal:1',
    ];

    protected $allowIncluded = [
        'fundacion',
        'razas',
        'vacunas',
        'solicitudes',
        'adopciones'
    ];

    protected $allowFilter = [
        'id',
        'nombre_mascota',
        'especie',
        'estado',
        'genero'
    ];

    protected $allowSort = [
        'id',
        'nombre_mascota',
        'edad_aprox',
        'created_at'
    ];

    // Relaciones
    public function fundacion()
    {
        return $this->belongsTo(Fundacion::class, 'fundacion_id');
    }

    public function razas()
    {
        return $this->belongsToMany(Raza::class, 'mascota_raza', 'mascota_id', 'raza_id')
            ->withTimestamps();
    }

    public function vacunas()
    {
        return $this->belongsToMany(TipoVacuna::class, 'mascota_vacuna', 'mascota_id', 'tipos_vacunas_id')
            ->withPivot('fecha_aplicacion')
            ->withTimestamps();
    }

    public function solicitudes()
    {
        return $this->morphMany(Solicitud::class, 'solicitable');
    }

    public function adopciones()
    {
        return $this->hasMany(Adopcion::class, 'mascota_id');
    }

    public function rescates()
    {
        return $this->hasMany(Rescate::class, 'mascota_id');
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'mascota_id');
    }

    public function historialMedico()
    {
        return $this->hasMany(HistorialMedico::class, 'mascota_id');
    }

    // Scopes
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'En adopcion');
    }

    public function scopePorEspecie($query, $especie)
    {
        return $query->where('especie', $especie);
    }

    // Helpers
    public function isDisponible(): bool
    {
        return $this->estado === 'En adopcion';
    }

    public function solicitudesAdopcion()
    {
        return $this->hasMany(Adopcion::class, 'mascota_id');
    }

    public function solicitudesPendientes()
    {
        return $this->solicitudesAdopcion()->where('estado', 'Pendiente');
    }

    public function tieneSolicitudesActivas(): bool
    {
        return $this->solicitudesAdopcion()
            ->whereIn('estado', ['Pendiente', 'En revisión'])
            ->exists();
    }
}
