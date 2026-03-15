<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'logo_url',
        'descripcion',
        'horario',
        'tipo',
        'user_id',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function vendedor(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
