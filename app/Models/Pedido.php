<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';

    protected $fillable = [
        'codigo',
        'comprador_id',
        'vendedor_id',
        'subtotal',
        'total',
        'estado',
        'direccion_envio',
        'telefono_contacto',
        'notas',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot method para generar código automático
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            if (empty($pedido->codigo)) {
                $pedido->codigo = 'PED-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Comprador del pedido
     */
    public function comprador()
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    /**
     * Vendedor del pedido
     */
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    /**
     * Productos del pedido (para relación muchos a muchos)
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
                    ->withPivot('cantidad', 'precio_unitario', 'subtotal')
                    ->withTimestamps();
    }

    /**
     * Scope para pedidos por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para pedidos de un comprador
     */
    public function scopeDeComprador($query, $userId)
    {
        return $query->where('comprador_id', $userId);
    }

    /**
     * Scope para pedidos de un vendedor
     */
    public function scopeDeVendedor($query, $userId)
    {
        return $query->where('vendedor_id', $userId);
    }

    /**
     * Scope para pedidos completados (pagados, enviados o entregados)
     */
    public function scopeCompletados($query)
    {
        return $query->whereIn('estado', ['pagado', 'enviado', 'entregado']);
    }

    /**
     * Verificar si el pedido puede ser cancelado
     */
    public function puedeCancelar()
    {
        return in_array($this->estado, ['pendiente', 'pagado']);
    }

    /**
     * Calcular total a partir de items
     */
    public static function calcularTotal($items)
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item['precio'] * $item['cantidad'];
        }

        return [
            'subtotal' => $subtotal,
            'total' => $subtotal, // Aquí podrías agregar impuestos, envío, etc.
        ];
    }

    /**
     * Formatear items para guardar en JSON
     */
    public static function formatearItems($items)
    {
        $formatted = [];

        foreach ($items as $item) {
            $formatted[] = [
                'producto_id' => $item['producto_id'],
                'nombre' => $item['nombre'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio'],
                'subtotal' => $item['precio'] * $item['cantidad'],
            ];
        }

        return $formatted;
    }
}
