<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = ['id_persona', 'pass', 'admin'];

    protected $hidden = ['pass'];

    public $timestamps = false;

    // Laravel espera "password". Tu columna se llama "pass".
    public function getAuthPassword()
    {
        return $this->pass;
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    // Accessor para id_depa desde per_dep
    public function getIdDepaAttribute()
    {
        if (!$this->id_persona) return null;
        
        $perDep = \Illuminate\Support\Facades\DB::table('per_dep')
            ->where('id_persona', $this->id_persona)
            ->first();
        
        return $perDep->id_depa ?? null;
    }
}

