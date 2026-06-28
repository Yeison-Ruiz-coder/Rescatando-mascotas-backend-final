<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $table = 'metodos_pago';

    protected $fillable = [
        'user_id',
        'tipo',
        'token',
        'ultimos_digitos',
        'marca',
        'expiracion_mes',
        'expiracion_anio',
        'paypal_email',
        'paypal_id',
        'es_principal',
        'es_demo',
    ];

    protected $casts = [
        'es_principal' => 'boolean',
        'es_demo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
