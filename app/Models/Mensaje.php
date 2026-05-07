<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Mensaje extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mensajes';

    protected $fillable = [
        'conversacion_id',
        'user_id',
        'mensaje',
        'leido',
        'leido_at',
        'entregado_at',
        'adjuntos',
        'reacciones',
        'tipo',
        'editado',
        'editado_at',
        'eliminado_para_mi',
        'eliminado_para_todos',
        'respondido_a',
    ];

    protected $casts = [
        'adjuntos' => 'array',
        'reacciones' => 'array',
        'leido' => 'boolean',
        'editado' => 'boolean',
        'eliminado_para_mi' => 'boolean',
        'eliminado_para_todos' => 'boolean',
        'leido_at' => 'datetime',
        'entregado_at' => 'datetime',
        'editado_at' => 'datetime',
    ];

    // Relaciones
    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class, 'conversacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function respondidoA()
    {
        return $this->belongsTo(Mensaje::class, 'respondido_a');
    }

    // Scope para mensajes no leídos
    public function scopeNoLeidos(Builder $query)
    {
        return $query->where('leido', false);
    }

    // Marcar como leído
    public function marcarComoLeido()
    {
        if (!$this->leido) {
            $this->leido = true;
            $this->leido_at = now();
            $this->save();
        }
    }
}
