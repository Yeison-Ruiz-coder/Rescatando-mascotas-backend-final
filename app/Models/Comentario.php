<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Comentario extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'comentarios';

    protected $allowSelect = [
        'id',
        'contenido',
        'fecha',
        'user_id',
        'comentable_type',
        'comentable_id',  // ✅ EXISTE EN LA TABLA
        'created_at',
    ];

    protected $allowIncluded = [
        'usuario',
        'comentable'  // ✅ AGREGAR
    ];

    protected $fillable = [
        'contenido',
        'fecha',
        'user_id',
        'comentable_id',   // ✅ AGREGAR
        'comentable_type', // ✅ AGREGAR
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // ✅ Relación con el usuario que comentó
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ✅ RELACIÓN POLIMÓRFICA - AGREGAR
    public function comentable()
    {
        return $this->morphTo();
    }
}
