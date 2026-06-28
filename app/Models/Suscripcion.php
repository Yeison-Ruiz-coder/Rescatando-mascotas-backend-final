<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Mascota;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suscripcion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suscripciones';

    protected $fillable = [
        'user_id',
        'mascota_id',
        'monto_mensual',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
        'mensaje_apoyo',
        'estado',
        'es_demo',
        'payment_method',
        'payment_reference',
        'stripe_subscription_id',
        'paypal_subscription_id',
        'mercadopago_subscription_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto_mensual' => 'decimal:2',
        'es_demo' => 'boolean',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class);
    }

    // ✅ NUEVA RELACIÓN: Pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    // ✅ NUEVA RELACIÓN: Facturas
    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    // ✅ NUEVA RELACIÓN: Métodos de pago (opcional)
    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ✅ NUEVOS SCOPES
    public function scopeDemo($query)
    {
        return $query->where('es_demo', true);
    }

    public function scopeReales($query)
    {
        return $query->where('es_demo', false);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }
}
