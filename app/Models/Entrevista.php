<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Entrevista extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'entrevistas';

    protected $allowSelect = ['id','adopcion_id','fecha_entrevista','notas','resultado','administrador_id','created_at','updated_at'];
    protected $allowIncluded = ['adopcion','administrador'];
    protected $allowFilter = ['id','resultado','administrador_id'];
    protected $allowSort = ['id','fecha_entrevista','created_at'];

    protected $fillable = [
        'adopcion_id',
        'fecha_entrevista',
        'notas',
        'resultado',
        'administrador_id',
    ];

    protected $casts = [
        'fecha_entrevista' => 'date',
    ];

    public function adopcion()
    {
        return $this->belongsTo(Adopcion::class, 'adopcion_id');
    }

    public function administrador()
    {
        return $this->belongsTo(User::class, 'administrador_id');
    }
}
