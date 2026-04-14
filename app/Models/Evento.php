<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = [
        'nombre_evento',
        'lugar_evento',
        'descripcion',
        'fecha_evento',
        'imagen_url',
        'fundacion_id',
        'tipo',
        'likes'
    ];

    protected $casts = [
        'fecha_evento' => 'datetime',
        'likes' => 'integer'
    ];

    // Relación con fundación
    public function fundacion()
    {
        return $this->belongsTo(Fundacion::class, 'fundacion_id');
    }
}
