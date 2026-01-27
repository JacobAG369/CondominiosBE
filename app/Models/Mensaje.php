<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    public $timestamps = false;

    protected $fillable = [
        'remitente',
        'destinatario',
        'id_depaA',
        'id_depaB',
        'mensaje',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];
}
