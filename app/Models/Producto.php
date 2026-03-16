<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Producto extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'productos';

    protected $allowIncluded = ['vendedor', 'categorias', 'tienda']; // Añadido 'categorias'
    protected $allowFilter = ['id', 'nombre', 'estado', 'precio'];
    protected $allowSort = ['id', 'nombre', 'precio', 'stock'];

    protected $fillable = [ // AÑADIDO
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'imagen_url',
        'estado',
        'tienda_id',
        'user_id',
    ];

    // Relaciones
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }

    // NUEVA - Relación con categorías
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categoria_producto')
                    ->withTimestamps();
    }

    // NUEVA - Relación con pedidos
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_producto')
                    ->withPivot('cantidad', 'precio_unitario', 'subtotal')
                    ->withTimestamps();
    }

    // Scope para productos disponibles
    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'disponible')->where('stock', '>', 0);
    }

    // Verificar disponibilidad
    public function isDisponible(): bool
    {
        return $this->estado === 'disponible' && $this->stock > 0;
    }
}
