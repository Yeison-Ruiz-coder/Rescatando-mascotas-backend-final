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
        'comentable_id',
        'created_at',
    ];

    protected $fillable = [
        'contenido',
        'fecha',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
