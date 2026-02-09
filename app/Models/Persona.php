<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';

    protected $fillable = [
        'nombre','apellido_p','apellido_m','celular','activo'
    ];

    public $timestamps = false;
}
