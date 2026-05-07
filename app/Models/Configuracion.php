<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
        'tipo',
        'grupo',
        'subgrupo',
        'descripcion',
        'publica',
        'editable',
        'opciones',
        'orden',
    ];

    protected $casts = [
        'valor' => 'json',
        'opciones' => 'array',
        'publica' => 'boolean',
        'editable' => 'boolean',
        'orden' => 'integer',
    ];

    // Obtener valor con tipo correcto
    public function getValorFormateadoAttribute()
    {
        return match ($this->tipo) {
            'integer' => (int) $this->valor,
            'boolean' => (bool) $this->valor,
            'json', 'array' => json_decode($this->valor, true),
            default => $this->valor,
        };
    }

    // Helper estático para obtener configuraciones fácilmente
    public static function get(string $clave, $default = null)
    {
        $config = static::where('clave', $clave)->first();
        return $config ? $config->valor_formateado : $default;
    }

    // Scope por grupo
    public function scopePorGrupo(Builder $query,int $grupo)
    {
        return $query->where('grupo', $grupo);
    }

    // Scope configuraciones públicas
    public function scopePublicas(Builder $query)
    {
        return $query->where('publica', true);
    }
}
