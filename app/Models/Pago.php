<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'suscripcion_id',
        'monto',
        'moneda',
        'metodo_pago',
        'estado',
        'transaccion_id',
        'comprobante_url',
        'fecha_pago',
        'es_demo',
        'metadata',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'es_demo' => 'boolean',
        'metadata' => 'array',
    ];

    // Relaciones
    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class);
    }

    // Scopes
    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeDemo($query)
    {
        return $query->where('es_demo', true);
    }

    public function scopeReales($query)
    {
        return $query->where('es_demo', false);
    }
}
