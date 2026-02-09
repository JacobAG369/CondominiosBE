<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerDep extends Model
{
    protected $table = 'per_dep';

    // Tabla pivote: NO tiene "id"
    public $incrementing = false;
    protected $primaryKey = null;

    // Si no tienes created_at / updated_at en esa tabla:
    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'id_depa',
        'id_rol',
        'residente',
        'codigo',
    ];
}

