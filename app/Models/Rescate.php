<?php
// app/Models/Rescate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasScopes;

class Rescate extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'rescates';

    protected $allowSelect = [
        'id',
        'fecha_rescate',
        'lugar_rescate',
        'descripcion_rescate',
        'estado',
        'tipo_emergencia',
        'prioridad',
        'lat',
        'lng',
        'nombre_reportante',
        'email_reportante',
        'telefono_reportante',
        'mascota_id',
        'reporte_id',
        'usuario_reporto_id',
        'entidad_responsable_id',
        'entidad_responsable_type',
        'gestionado_por',
        'created_at',
    ];

    // App/Models/Rescate.php

    protected $fillable = [
        'fecha_rescate',
        'lugar_rescate',
        'descripcion_rescate',
        'foto_principal',
        'foto_principal_public_id',
        'galeria_fotos',
        'galeria_fotos_public_ids',
        'fotos_metadata',
        'estado',
        'tipo_emergencia',
        'prioridad',
        'lat',
        'lng',
        'nombre_reportante',
        'email_reportante',
        'telefono_reportante',
        'mascota_id',
        'reporte_id',
        'usuario_reporto_id',
        'entidad_responsable_type',
        'entidad_responsable_id',
        'gestionado_por',
    ];

    protected $casts = [
        'fecha_rescate' => 'date',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
    ];

    // Relaciones
    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    public function reporte()
    {
        return $this->belongsTo(Reporte::class);
    }

    public function usuarioReporto()
    {
        return $this->belongsTo(User::class, 'usuario_reporto_id');
    }

    // Relación polimórfica
    public function entidadResponsable()
    {
        return $this->morphTo('entidad_responsable');
    }

    public function gestionadoPor()
    {
        return $this->belongsTo(User::class, 'gestionado_por');
    }

    // Aliado para mantener compatibilidad con código existente
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_reporto_id');
    }

    // Scopes útiles
    public function scopePendientes(Builder $query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnProceso(Builder $query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeCompletados(Builder $query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopePorPrioridad(Builder $query, string $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }
}
