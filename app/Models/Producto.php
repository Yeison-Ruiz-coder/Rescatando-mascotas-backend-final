<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Producto extends Model
{
    use HasFactory, HasScopes;

    protected $allowIncluded = ['vendedor', 'categorias'];
    protected $allowFilter = ['id', 'nombre', 'estado', 'precio'];
    protected $allowSort = ['id', 'nombre', 'precio', 'stock'];

    // Relación con el vendedor
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'user_id');
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
