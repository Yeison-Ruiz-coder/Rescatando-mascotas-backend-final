<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class TipoVacuna extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'tipos_vacunas';

    protected $allowSelect = ['id','nombre_vacuna','frecuencia_dias','created_at','updated_at'];
    protected $allowIncluded = ['mascotas'];
    protected $allowFilter = ['id','nombre_vacuna'];
    protected $allowSort = ['id','nombre_vacuna','frecuencia_dias','created_at'];

    protected $fillable = [
        'nombre_vacuna',
        'frecuencia_dias',
    ];

    public function mascotas()
    {
        return $this->belongsToMany(Mascota::class, 'mascota_vacuna', 'tipos_vacunas_id', 'mascota_id')
                    ->withPivot('fecha_aplicacion')
                    ->withTimestamps();
    }
}
