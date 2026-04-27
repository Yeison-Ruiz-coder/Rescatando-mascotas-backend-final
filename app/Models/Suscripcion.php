<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Mascota;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'user_id',
        'mascota_id',
        'monto_mensual',
        'frecuencia',
        'fecha_inicio',
        'fecha_fin',
        'mensaje_apoyo',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'monto_mensual' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Constantes para los estados de suscripción
     */
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_PAUSADO = 'pausado';
    const ESTADO_CANCELADO = 'cancelado';
    const ESTADO_FINALIZADO = 'finalizado';

    /**
     * Constantes para las frecuencias
     */
    const FRECUENCIA_UNICA = 'unica';
    const FRECUENCIA_MENSUAL = 'mensual';
    const FRECUENCIA_TRIMESTRAL = 'trimestral';
    const FRECUENCIA_ANUAL = 'anual';

    /**
     * 👤 Relación con el usuario (apadrinador/donante)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 🐶 Relación con la mascota
     */
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    /**
     * 🔥 Scopes para filtrar
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    public function scopePausadas($query)
    {
        return $query->where('estado', self::ESTADO_PAUSADO);
    }

    public function scopeCanceladas($query)
    {
        return $query->where('estado', self::ESTADO_CANCELADO);
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('estado', self::ESTADO_FINALIZADO);
    }

    public function scopePorUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorMascota($query, int $mascotaId)
    {
        return $query->where('mascota_id', $mascotaId);
    }

    /**
     * 🧮 Atributos calculados
     */
    
    /**
     * Verifica si la suscripción está activa
     */
    public function isActiva(): bool
    {
        return $this->estado === self::ESTADO_ACTIVO;
    }

    /**
     * Verifica si la suscripción está pausada
     */
    public function isPausada(): bool
    {
        return $this->estado === self::ESTADO_PAUSADO;
    }

    /**
     * Verifica si la suscripción está cancelada
     */
    public function isCancelada(): bool
    {
        return $this->estado === self::ESTADO_CANCELADO;
    }

    /**
     * Calcula el total donado hasta la fecha
     */
    public function getTotalDonadoAttribute(): float
    {
        // Simplificado - idealmente vendría de una tabla de pagos
        return $this->monto_mensual * $this->mesesActiva();
    }

    /**
     * Calcula cantidad de meses que lleva activa
     */
    public function getMesesActivaAttribute(): int
    {
        if (!$this->isActiva()) {
            return 0;
        }
        
        $inicio = $this->fecha_inicio;
        $ahora = now();
        
        return $inicio->diffInMonths($ahora);
    }

    /**
     * Calcula el próximo pago basado en la frecuencia
     */
    public function getProximoPagoAttribute(): ?string
    {
        if (!$this->isActiva()) {
            return null;
        }
        
        $ultimoPago = $this->fecha_inicio;
        
        switch ($this->frecuencia) {
            case self::FRECUENCIA_MENSUAL:
                $proximo = $ultimoPago->copy()->addMonths($this->mesesActiva + 1);
                break;
            case self::FRECUENCIA_TRIMESTRAL:
                $proximo = $ultimoPago->copy()->addMonths(($this->mesesActiva + 1) * 3);
                break;
            case self::FRECUENCIA_ANUAL:
                $proximo = $ultimoPago->copy()->addYears(floor($this->mesesActiva / 12) + 1);
                break;
            default:
                return null;
        }
        
        return $proximo->format('Y-m-d');
    }

    /**
     * Obtener el estado en español
     */
    public function getEstadoTextoAttribute(): string
    {
        $estados = [
            self::ESTADO_ACTIVO => 'Activo',
            self::ESTADO_PAUSADO => 'Pausado',
            self::ESTADO_CANCELADO => 'Cancelado',
            self::ESTADO_FINALIZADO => 'Finalizado',
        ];
        
        return $estados[$this->estado] ?? 'Desconocido';
    }

    /**
     * Obtener la frecuencia en español
     */
    public function getFrecuenciaTextoAttribute(): string
    {
        $frecuencias = [
            self::FRECUENCIA_UNICA => 'Única',
            self::FRECUENCIA_MENSUAL => 'Mensual',
            self::FRECUENCIA_TRIMESTRAL => 'Trimestral',
            self::FRECUENCIA_ANUAL => 'Anual',
        ];
        
        return $frecuencias[$this->frecuencia] ?? 'Desconocida';
    }

    /**
     * Obtener el color del estado para badges CSS
     */
    public function getEstadoColorAttribute(): string
    {
        $colores = [
            self::ESTADO_ACTIVO => 'success',
            self::ESTADO_PAUSADO => 'warning',
            self::ESTADO_CANCELADO => 'danger',
            self::ESTADO_FINALIZADO => 'secondary',
        ];
        
        return $colores[$this->estado] ?? 'secondary';
    }
}