<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Solicitud extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'solicitudes';

    protected $allowSelect = [
        'id',
        'tipo_solicitud',
        'contenido',
        'fecha_solicitud',
        'estado',
        'razon_rechazo',
        'notas_internas',
        'user_id',
        'nombre_solicitante',
        'email_solicitante',
        'telefono_solicitante',
        'solicitable_type',
        'solicitable_id',
        'revisado_por',
        'fecha_revision',
        'datos_adicionales',
        'created_at',
        'updated_at',
    ];

    protected $allowIncluded = [
        'usuario',
        'revisor',
        'solicitable',
        'adopcion'
    ];

    protected $allowFilter = [
        'id',
        'tipo_solicitud',
        'estado',
        'nombre_solicitante',
        'email_solicitante'
    ];

    protected $allowSort = [
        'id',
        'fecha_solicitud',
        'estado',
        'created_at'
    ];

    // ✅ CORREGIDO: Solo los campos que existen en la tabla
    protected $fillable = [
        'tipo_solicitud',
        'contenido',
        'fecha_solicitud',
        'estado',
        'razon_rechazo',
        'notas_internas',
        'user_id',
        'nombre_solicitante',
        'email_solicitante',
        'telefono_solicitante',
        'solicitable_type',
        'solicitable_id',
        'revisado_por',
        'fecha_revision',
        'datos_adicionales'
    ];

    protected $casts = [
        'datos_adicionales' => 'array',
        'fecha_solicitud' => 'datetime',
        'fecha_revision' => 'datetime',
    ];

    // Constantes para tipos de solicitud
    const TIPO_ADOPCION = 'adopcion';
    const TIPO_RESCATE = 'rescate';
    const TIPO_APADRINAMIENTO = 'apadrinamiento';
    const TIPO_DONACION = 'donacion';
    const TIPO_OTRO = 'otro';

    // Constantes para estados
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_REVISION = 'en_revision';
    const ESTADO_APROBADA = 'aprobada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_COMPLETADA = 'completada';

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    // Relación polimórfica - puede ser para mascota, ayuda económica, etc.
    public function solicitable()
    {
        return $this->morphTo();
    }

    public function adopcion()
    {
        return $this->hasOne(Adopcion::class, 'solicitud_id');
    }

    // Scopes útiles
    public function scopePendientes(Builder $query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeEnRevision(Builder $query)
    {
        return $query->where('estado', self::ESTADO_EN_REVISION);
    }

    public function scopeAprobadas(Builder $query)
    {
        return $query->where('estado', self::ESTADO_APROBADA);
    }

    public function scopeRechazadas(Builder $query)
    {
        return $query->where('estado', self::ESTADO_RECHAZADA);
    }

    public function scopePorTipo(Builder $query, string $tipo)
    {
        return $query->where('tipo_solicitud', $tipo);
    }

    public function scopePorMascota(Builder $query, int $mascotaId)
    {
        return $query->where('solicitable_type', Mascota::class)
            ->where('solicitable_id', $mascotaId);
    }

    // MÉTODOS ÚTILES PARA ADOPCIÓN

    /**
     * Guarda datos específicos de adopción en el campo JSON
     */
    public function setDatosAdopcion(array $datos): self
    {
        $datosAdopcion = array_merge($this->datos_adicionales ?? [], [
            'experiencia_mascotas' => $datos['experiencia_mascotas'] ?? null,
            'tipo_vivienda' => $datos['tipo_vivienda'] ?? null,
            'motivo_adopcion' => $datos['motivo_adopcion'] ?? null,
            'compromiso_cuidado' => $datos['compromiso_cuidado'] ?? false,
            'compromiso_esterilizacion' => $datos['compromiso_esterilizacion'] ?? false,
            'compromiso_seguimiento' => $datos['compromiso_seguimiento'] ?? false,
            'direccion' => $datos['direccion'] ?? null,
            'apellido_solicitante' => $datos['apellido_solicitante'] ?? null,
            'documento_identidad' => $datos['documento_identidad'] ?? null,
            'ciudad' => $datos['ciudad'] ?? null,
            'departamento' => $datos['departamento'] ?? null,
            'codigo_postal' => $datos['codigo_postal'] ?? null,
            'estado_civil' => $datos['estado_civil'] ?? null,
            'cantidad_hijos' => $datos['cantidad_hijos'] ?? null,
            'ocupacion' => $datos['ocupacion'] ?? null,
            'es_propietario' => $datos['es_propietario'] ?? null,
        ]);

        $this->datos_adicionales = $datosAdopcion;
        return $this;
    }

    /**
     * Obtiene datos específicos de adopción
     */
    public function getDatosAdopcion(): array
    {
        return $this->datos_adicionales ?? [];
    }

    /**
     * Obtiene un campo específico de adopción
     */
    public function getDatoAdopcion(string $key, $default = null)
    {
        return $this->datos_adicionales[$key] ?? $default;
    }

    /**
     * Verifica si la solicitud es de adopción
     */
    public function esAdopcion(): bool
    {
        return $this->tipo_solicitud === self::TIPO_ADOPCION;
    }

    /**
     * Verifica si todos los compromisos están aceptados (para adopción)
     */
    public function compromisosCompletos(): bool
    {
        if (!$this->esAdopcion()) {
            return false;
        }

        return $this->getDatoAdopcion('compromiso_cuidado') &&
            $this->getDatoAdopcion('compromiso_esterilizacion') &&
            $this->getDatoAdopcion('compromiso_seguimiento');
    }

    /**
     * Obtiene el nombre completo del solicitante
     */
    public function getNombreCompletoAttribute(): string
    {
        if ($this->esAdopcion() && $apellido = $this->getDatoAdopcion('apellido_solicitante')) {
            return trim($this->nombre_solicitante . ' ' . $apellido);
        }
        return $this->nombre_solicitante ?? 'No especificado';
    }

    // MÉTODOS DE ESTADO
    public function isPendiente(): bool
    {
        return $this->estado === self::ESTADO_PENDIENTE;
    }

    public function isEnRevision(): bool
    {
        return $this->estado === self::ESTADO_EN_REVISION;
    }

    public function isAprobada(): bool
    {
        return $this->estado === self::ESTADO_APROBADA;
    }

    public function isRechazada(): bool
    {
        return $this->estado === self::ESTADO_RECHAZADA;
    }

    public function isCompletada(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADA;
    }

    public function aprobar(int $revisorId): void
    {
        $this->estado = self::ESTADO_APROBADA;
        $this->revisado_por = $revisorId;
        $this->fecha_revision = now();
        $this->save();

        if ($this->esAdopcion() && $this->solicitable_type === Mascota::class) {
            $mascota = $this->solicitable;
            if ($mascota && $mascota->estado === 'En adopcion') {
                $mascota->estado = 'Adoptado';
                $mascota->save();
            }
        }
    }

    public function rechazar(int $revisorId, string $razon): void
    {
        $this->estado = self::ESTADO_RECHAZADA;
        $this->razon_rechazo = $razon;
        $this->revisado_por = $revisorId;
        $this->fecha_revision = now();
        $this->save();
    }

    public function ponerEnRevision(int $revisorId): void
    {
        $this->estado = self::ESTADO_EN_REVISION;
        $this->revisado_por = $revisorId;
        $this->fecha_revision = now();
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($solicitud) {
            if (
                $solicitud->tipo_solicitud === self::TIPO_ADOPCION &&
                $solicitud->solicitable_type === Mascota::class
            ) {

                $mascota = Mascota::find($solicitud->solicitable_id);
                if ($mascota && $mascota->estado !== 'En adopcion') {
                    throw new \Exception('La mascota no está disponible para adopción');
                }
            }
        });
    }
    public function getDuenoAttribute()
    {
        if ($this->solicitable_type === Mascota::class) {
            $mascota = $this->solicitable;
            if ($mascota && $mascota->fundacion_id) {
                return $mascota->fundacion?->usuario;
            }
            // Si la mascota tiene veterinaria asociada
            if ($mascota && $mascota->veterinaria_id) {
                return $mascota->veterinaria?->usuario;
            }
        }
        return null;
    }

    public function getEntidadDuenoAttribute()
    {
        if ($this->solicitable_type === Mascota::class) {
            $mascota = $this->solicitable;
            if ($mascota && $mascota->fundacion_id) {
                return $mascota->fundacion;
            }
            if ($mascota && $mascota->veterinaria_id) {
                return $mascota->veterinaria;
            }
        }
        return null;
    }

    public function esParaUsuario($userId): bool
    {
        if ($this->solicitable_type === Mascota::class) {
            $mascota = $this->solicitable;
            if ($mascota) {
                // Verificar si el usuario es dueño de la fundación
                if ($mascota->fundacion_id) {
                    return $mascota->fundacion?->user_id === $userId;
                }
                // Verificar si el usuario es dueño de la veterinaria
                if ($mascota->veterinaria_id) {
                    return $mascota->veterinaria?->user_id === $userId;
                }
            }
        }
        return false;
    }
    // Agregar relación con mascota (helper)
    public function mascota()
    {
        if ($this->solicitable_type === Mascota::class) {
            return $this->belongsTo(Mascota::class, 'solicitable_id');
        }
        return null;
    }
}
