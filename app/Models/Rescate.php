<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rescate extends Model
{
    use HasFactory;

    protected $table = 'rescates';

    protected $fillable = [
        'fecha_rescate',
        'lugar_rescate',
        'descripcion_rescate',
        'estado',
        'mascota_id',
        'reporte_id',
        'usuario_reporto_id',
        'entidad_responsable_id',    // IMPORTANTE: debe coincidir con morph
        'entidad_responsable_type',  // IMPORTANTE: debe coincidir con morph
        'gestionado_por',
    ];

    protected $casts = [
        'fecha_rescate' => 'date',
    ];

    // Relaciones
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function reporte()
    {
        return $this->belongsTo(Reporte::class, 'reporte_id');
    }

    public function usuarioReporto()
    {
        return $this->belongsTo(User::class, 'usuario_reporto_id');
    }

    // CORREGIDO: nombre del método debe coincidir con los campos en la BD
    public function entidadResponsable()
    {
        return $this->morphTo('entidad_responsable'); // Especificamos el nombre del morph
    }

    public function gestionadoPor()
    {
        return $this->belongsTo(User::class, 'gestionado_por');
    }

    // Scopes útiles
    public function scopeEnProceso($query)
    {
        return $query->where('estado', 'en_proceso');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }
}
