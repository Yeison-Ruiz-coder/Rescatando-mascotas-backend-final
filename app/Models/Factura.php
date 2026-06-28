<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $fillable = [
        'suscripcion_id',
        'numero_factura',
        'monto',
        'moneda',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
        'pdf_url',
        'cliente_nombre',
        'cliente_email',
        'cliente_documento',
        'es_demo',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'es_demo' => 'boolean',
    ];

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class);
    }
}
