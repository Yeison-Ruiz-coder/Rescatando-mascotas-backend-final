<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Raza extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'razas';

    protected $allowSelect = [
        'id',
        'nombre_raza',
        'especie',
        'created_at',
        'updated_at',
    ];

    protected $allowIncluded = ['mascotas'];
    protected $allowFilter = ['id', 'nombre_raza', 'especie'];
    protected $allowSort = ['id', 'nombre_raza', 'especie', 'created_at'];

    protected $fillable = [
        'nombre_raza',
        'especie',
    ];

    public function mascotas()
    {
        return $this->belongsToMany(Mascota::class, 'mascota_raza', 'raza_id', 'mascota_id');
    }
}
