<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasScopes;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasScopes;

    protected $allowIncluded = [
        'solicitudes',
        'adopciones',
        'mascotas',
        'donaciones',
        'suscripciones',
        'comentarios',
        'notificaciones',
        'tienda', // NUEVA
        'fundacion', // NUEVA
        'veterinaria' // NUEVA
    ];

    protected $allowFilter = ['id', 'nombre', 'email', 'tipo', 'estado'];
    protected $allowSort = ['id', 'nombre', 'email', 'created_at'];

    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'password',
        'tipo',
        'estado',
        'fecha_nacimiento',
        'direccion',
        'telefono',
        'avatar',
        'tipo_documento',
        'numero_documento',
        'email_verified_at',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'fecha_nacimiento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones de usuario creador/editor
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Usuarios que este usuario creó
    public function usuariosCreados()
    {
        return $this->hasMany(User::class, 'created_by');
    }


    public function fundacion()
    {
        return $this->hasOne(Fundacion::class, 'user_id'); // IMPORTANTE: fundaciones necesita user_id
    }

    public function veterinaria()
    {
        return $this->hasOne(Veterinaria::class, 'user_id'); // IMPORTANTE: veterinarias necesita user_id
    }

    // Relaciones existentes (verifica que los foreign keys sean correctos)
    public function mascotas()
    {
        return $this->hasMany(Mascota::class, 'fundacion_id'); // OJO: Esto asume que el user es una fundación
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'user_id');
    }

    public function reportesResueltos()
    {
        return $this->hasMany(Reporte::class, 'resuelto_por');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'user_id');
    }

    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'user_id');
    }

    public function solicitudesRevisadas()
    {
        return $this->hasMany(Solicitud::class, 'revisado_por');
    }

    public function suscripciones()
    {
        return $this->hasMany(Suscripcion::class, 'user_id');
    }

    public function adopciones()
    {
        return $this->hasMany(Adopcion::class, 'user_id');
    }

    public function adopcionesAprobadas()
    {
        return $this->hasMany(Adopcion::class, 'administrador_id');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'user_id');
    }

    public function notificacionesEnviadas()
    {
        return $this->hasMany(Notificacion::class, 'creado_por_id');
    }

    public function donaciones()
    {
        return $this->hasMany(Donacion::class, 'user_id');
    }

    public function rescatesReportados()
    {
        return $this->hasMany(Rescate::class, 'usuario_reporto_id');
    }

    public function rescatesGestionados()
    {
        return $this->hasMany(Rescate::class, 'gestionado_por');
    }

    public function entrevistas()
    {
        return $this->hasMany(Entrevista::class, 'administrador_id');
    }

    public function seguimientosRealizados()
    {
        return $this->hasMany(SeguimientoAdopcion::class, 'realizado_por');
    }

    public function historialesMedicosVeterinario()
    {
        return $this->hasMany(HistorialMedico::class, 'veterinario_id');
    }

    public function historialesMedicosRegistrados()
    {
        return $this->hasMany(HistorialMedico::class, 'registrado_por');
    }

    // Helper para verificar roles
    public function isAdmin(): bool
    {
        return $this->tipo === 'admin';
    }

    public function isVeterinaria(): bool
    {
        return $this->tipo === 'veterinaria';
    }

    public function isFundacion(): bool
    {
        return $this->tipo === 'fundacion';
    }

    public function isUser(): bool
    {
        return $this->tipo === 'user';
    }

    // Scope para usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    // Nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente')
            ->whereIn('tipo', ['fundacion', 'veterinaria']);
    }
}
