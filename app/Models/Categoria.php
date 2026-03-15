<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Categoria extends Model
{
    use HasFactory, HasScopes;

    protected $allowIncluded = ['productos', 'categoriaPadre', 'categoriasHijas'];
    protected $allowFilter = ['id', 'nombre', 'activo'];
    protected $allowSort = ['id', 'nombre', 'created_at'];

    /**
     * Productos en esta categoría
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'categoria_producto')
                    ->withTimestamps();
    }

    /**
     * Categoría padre
     */
    public function categoriaPadre()
    {
        return $this->belongsTo(Categoria::class, 'categoria_padre_id');
    }

    /**
     * Subcategorías
     */
    public function categoriasHijas()
    {
        return $this->hasMany(Categoria::class, 'categoria_padre_id');
    }

    /**
     * Scope para categorías activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para categorías principales (sin padre)
     */
    public function scopePrincipales($query)
    {
        return $query->whereNull('categoria_padre_id');
    }

    /**
     * Obtener todas las subcategorías recursivamente
     */
    public function subcategoriasRecursivas()
    {
        return $this->categoriasHijas()->with('subcategoriasRecursivas');
    }
}
