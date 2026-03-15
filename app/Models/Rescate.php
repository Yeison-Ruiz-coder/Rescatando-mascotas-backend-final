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
        'entidad_responsable_id',    //  Para veterinaria o fundación
        'entidad_responsable_type',  //  'App\Models\Veterinaria' o 'App\Models\Fundacion'
        'gestionado_por',            //  Usuario admin que gestiona
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

    //  NUEVA - Relación polimórfica
    public function entidadResponsable()
    {
        return $this->morphTo();
    }

    //  NUEVA - Usuario que gestiona
    public function gestionadoPor()
    {
        return $this->belongsTo(User::class, 'gestionado_por');
    }
}
