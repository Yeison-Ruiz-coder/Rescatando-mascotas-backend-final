<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasScopes;

class SeguimientoAdopcion extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'seguimientos_adopcion';

    protected $allowSelect = ['id','adopcion_id','tipo_seguimiento','fecha_seguimiento','proximo_seguimiento','observaciones','recomendaciones','estado_mascota','resultado','foto_url','fotos_adicionales','video_url','documento_url','condiciones_hogar','observaciones_hogar','convive_con_otros_animales','comportamiento_observado','realizado_por','realizado_por_nombre','requiere_nuevo_seguimiento','firma_adoptante','fecha_confirmacion','created_at','updated_at'];
    protected $allowIncluded = ['adopcion','realizadoPor'];
    protected $allowFilter = ['id','tipo_seguimiento','estado_mascota','realizado_por'];
    protected $allowSort = ['id','fecha_seguimiento','proximo_seguimiento','created_at'];

    protected $fillable = [
        'adopcion_id',
        'tipo_seguimiento',
        'fecha_seguimiento',
        'proximo_seguimiento',
        'observaciones',
        'recomendaciones',
        'estado_mascota',
        'resultado',
        'foto_url',
        'fotos_adicionales',
        'video_url',
        'documento_url',
        'condiciones_hogar',
        'observaciones_hogar',
        'convive_con_otros_animales',
        'comportamiento_observado',
        'realizado_por',
        'realizado_por_nombre',
        'requiere_nuevo_seguimiento',
        'firma_adoptante',
        'fecha_confirmacion',
    ];

    protected $casts = [
        'fotos_adicionales' => 'array',
        'fecha_seguimiento' => 'date',
        'proximo_seguimiento' => 'date',
        'fecha_confirmacion' => 'datetime',
        'requiere_nuevo_seguimiento' => 'boolean',
        'firma_adoptante' => 'boolean',
        'convive_con_otros_animales' => 'boolean',
    ];

    // Relaciones
    public function adopcion()
    {
        return $this->belongsTo(Adopcion::class, 'adopcion_id');
    }

    public function realizadoPor()
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }

    // Scopes
    public function scopePendientesProximoSeguimiento(Builder$query)
    {
        return $query->where('requiere_nuevo_seguimiento', true)
                     ->whereNotNull('proximo_seguimiento')
                     ->whereDate('proximo_seguimiento', '<=', now());
    }

    public function scopePorEstadoMascota(Builder $query, string $estado)
    {
        return $query->where('estado_mascota', $estado);
    }
}
