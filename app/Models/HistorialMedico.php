<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class HistorialMedico extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'historial_medico';

    protected $allowSelect = ['id','mascota_id','veterinaria_id','veterinario_id','registrado_por','fecha_consulta','diagnostico','tratamiento','observaciones','documento_url','created_at','updated_at'];
    protected $allowIncluded = ['mascota','veterinaria','veterinario','registradoPor'];
    protected $allowFilter = ['id','diagnostico','veterinaria_id','mascota_id'];
    protected $allowSort = ['id','fecha_consulta','created_at'];

    protected $fillable = [
        'mascota_id',
        'veterinaria_id',
        'veterinario_id',
        'registrado_por',
        'fecha_consulta',
        'diagnostico',
        'tratamiento',
        'observaciones',
        'documento_url',
    ];

    protected $casts = [
        'fecha_consulta' => 'date',
    ];

    // Relaciones
    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'mascota_id');
    }

    public function veterinaria()
    {
        return $this->belongsTo(Veterinaria::class, 'veterinaria_id');
    }

    public function veterinario()
    {
        return $this->belongsTo(User::class, 'veterinario_id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
