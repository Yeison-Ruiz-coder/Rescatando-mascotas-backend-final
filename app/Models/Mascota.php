<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasScopes;
use Illuminate\Database\Eloquent\Builder;

class Mascota extends Model
{
    use HasFactory, SoftDeletes, HasScopes;

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
        'foto_principal_public_id',
        'galeria_fotos',
        'necesita_hogar_temporal',
        'apto_con_ninos',
        'apto_con_otros_animales',
        'fecha_ingreso',
        'fecha_salida',
        'fundacion_id',
        'peso_aprox',
        'tamano',
        'color',
        'salud_general',
        'esterilizado',
        'desparasitado',
        'vacunado',
        'enfermedades_cronicas',
        'medicamentos',
        'requisitos_adopcion',
        'hogar_recomendado',
        'video_url',
        'video_public_id',
        'destacada',
        'fecha_publicacion',
        'vistas',
        'interesados',
        'veterinaria_id',
        'padrinos',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'galeria_fotos' => 'array',
        'enfermedades_cronicas' => 'array',
        'medicamentos' => 'array',
        'requisitos_adopcion' => 'array',
        'padrinos' => 'array',
        'necesita_hogar_temporal' => 'boolean',
        'apto_con_ninos' => 'boolean',
        'apto_con_otros_animales' => 'boolean',
        'esterilizado' => 'boolean',
        'desparasitado' => 'boolean',
        'vacunado' => 'boolean',
        'destacada' => 'boolean',
        'fecha_ingreso' => 'date',
        'fecha_salida' => 'date',
        'fecha_publicacion' => 'datetime',
        'edad_aprox' => 'decimal:2',
        'peso_aprox' => 'decimal:2',
        'vistas' => 'integer',
        'interesados' => 'integer',
    ];

    // ✅ AGREGAR 'veterinaria' a allowIncluded
    protected $allowIncluded = [
        'fundacion',
        'veterinaria', // ✅ NUEVA
        'razas',
        'vacunas',
        'solicitudes',
        'adopciones'
    ];

    protected $allowSelect = [
        'id',
        'nombre_mascota',
        'especie',
        'edad_aprox',
        'genero',
        'estado',
        'descripcion',
        'lugar_rescate',
        'condiciones_especiales',
        'foto_principal',
        'galeria_fotos',
        'tamano',
        'destacada',
        'fundacion_id',
        'veterinaria_id',
        'peso_aprox',
        'color',
        'salud_general',
        'esterilizado',
        'desparasitado',
        'vacunado',
        'requisitos_adopcion',
        'video_url',
        'fecha_publicacion',
        'vistas',
        'interesados',
        'created_at',
        'apto_con_ninos',
        'apto_con_otros_animales',
        'necesita_hogar_temporal',
        'enfermedades_cronicas',
        'medicamentos',
        'fecha_ingreso',
        'hogar_recomendado',
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

    // ============================================
    // ✅ RELACIONES
    // ============================================

    public function fundacion()
    {
        return $this->belongsTo(Fundacion::class, 'fundacion_id');
    }

    /**
     * ✅ RELACIÓN CON VETERINARIA - AGREGAR
     */
    public function veterinaria()
    {
        return $this->belongsTo(Veterinaria::class, 'veterinaria_id', 'id');
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

    // ============================================
    // ✅ SCOPES
    // ============================================

    public function scopeDisponibles(Builder $query)
    {
        return $query->where('estado', 'En adopcion');
    }

    public function scopePorEspecie(Builder $query, string $especie)
    {
        return $query->where('especie', $especie);
    }

    // ============================================
    // ✅ HELPERS
    // ============================================

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

    public function getEdadAproxAttribute(mixed $value)
    {
        if (is_null($value)) {
            return null;
        }

        $edadNum = (float) $value;

        if ($edadNum == 0) {
            return null;
        }

        return (int) round($edadNum);
    }

    public function setEdadAproxAttribute(mixed $value)
    {
        if (is_null($value) || $value === '') {
            $this->attributes['edad_aprox'] = null;
            return;
        }

        $this->attributes['edad_aprox'] = (int) round((float) $value);
    }
}
