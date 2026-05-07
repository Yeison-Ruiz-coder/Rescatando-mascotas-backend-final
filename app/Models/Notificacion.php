<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'contenido',
        'fecha_envio',
        'user_id',
        'creado_por_id',
        // ===== NUEVOS CAMPOS =====
        'titulo',
        'tipo',
        'icono',
        'color',
        'url_accion',
        'texto_accion',
        'metadata',
        'prioridad',
        'leida',
        'leida_en',
        'expira_en',
        'enviada_email',
        'enviada_push',
    ];

    protected $casts = [
        'fecha_envio' => 'datetime',
        'metadata' => 'array', //  NUEVO
        'leida' => 'boolean', //  NUEVO
        'leida_en' => 'datetime', //  NUEVO
        'expira_en' => 'datetime', //  NUEVO
        'enviada_email' => 'boolean', //  NUEVO
        'enviada_push' => 'boolean', //  NUEVO
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }
}
