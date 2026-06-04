<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasScopes;

class Donacion extends Model
{
    use HasFactory, HasScopes;

    protected $table = 'donaciones';

    protected $allowSelect = [
        'id',
        'valor_donacion',
        'fecha_donacion',
        'publica',
        'user_id',
        'fundacion_id',
        'created_at',
        'updated_at',
    ];

    protected $allowIncluded = ['usuario', 'fundacion'];
    protected $allowFilter = ['id', 'valor_donacion', 'publica'];
    protected $allowSort = ['id', 'valor_donacion', 'fecha_donacion', 'created_at'];

    protected $fillable = [
        'valor_donacion',
        'fecha_donacion',
        'publica',
        'user_id',
        'fundacion_id',
    ];

    protected $casts = [
        'fecha_donacion' => 'datetime',
        'publica' => 'boolean',
        'valor_donacion' => 'decimal:2',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fundacion()
    {
        return $this->belongsTo(Fundacion::class, 'fundacion_id');
    }
}
