<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasScopes;

class Reporte extends Model
{
    use HasFactory, SoftDeletes, HasScopes;

    protected $table = 'reportes';

    protected $allowSelect = ['id','tipo_reporte','titulo','descripcion','ubicacion','fecha_incidente','especie','raza','color','foto_url','galeria_fotos','estado','user_id','nombre_reportante','telefono_reportante','email_reportante','solucion','resuelto_por','fecha_resolucion','lat','lng','direccion_completa','fotos_detalle','fotos_public_ids','contacto_permiso','anonimo','urgencia','seguimiento_interno','asignado_a','fecha_asignacion','entidad_encargada','numero_caso','acciones_tomadas','created_at','updated_at'];
    protected $allowIncluded = ['usuario','resueltoPor','rescate'];
    protected $allowFilter = ['id','tipo_reporte','estado','titulo','user_id'];
    protected $allowSort = ['id','fecha_incidente','created_at'];

    protected $fillable = [
        'tipo_reporte',
        'titulo',
        'descripcion',
        'ubicacion',
        'fecha_incidente',
        'especie',
        'raza',
        'color',
        'foto_url',
        'galeria_fotos',
        'estado',
        'user_id',
        'nombre_reportante',
        'telefono_reportante',
        'email_reportante',
        'solucion',
        'resuelto_por',
        'fecha_resolucion',
        // ===== NUEVOS CAMPOS =====
        'lat',
        'lng',
        'direccion_completa',
        'fotos_detalle',
        'fotos_public_ids',
        'contacto_permiso',
        'anonimo',
        'urgencia',
        'seguimiento_interno',
        'asignado_a',
        'fecha_asignacion',
        'entidad_encargada',
        'numero_caso',
        'acciones_tomadas',
    ];

    protected $casts = [
        'galeria_fotos' => 'array',
        'fotos_detalle' => 'array', //  NUEVO
        'fotos_public_ids' => 'array', //  NUEVO
        'seguimiento_interno' => 'array', //  NUEVO
        'fecha_incidente' => 'date',
        'fecha_resolucion' => 'datetime',
        'fecha_asignacion' => 'datetime', //  NUEVO
        'contacto_permiso' => 'boolean', //  NUEVO
        'anonimo' => 'boolean', //  NUEVO
        'lat' => 'decimal:8', //  NUEVO
        'lng' => 'decimal:8', //  NUEVO
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function resueltoPor()
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }

    public function rescate()
    {
        return $this->hasOne(Rescate::class, 'reporte_id');
    }

    // Scopes
    public function scopeActivos(Builder $query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopePerdidos(Builder $query)
    {
        return $query->where('tipo_reporte', 'perdido');
    }

    public function scopeEncontrados(Builder $query)
    {
        return $query->where('tipo_reporte', 'encontrado');
    }
}
