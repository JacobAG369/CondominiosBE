<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'id_persona',
        'email',
        'pass',
        'admin',
        'email_verified_at',
    ];

    protected $hidden = ['pass'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'admin' => 'boolean',
    ];

    public $timestamps = false;

    // Laravel espera "password". Tu columna se llama "pass".
    public function getAuthPassword(): string
    {
        return $this->pass;
    }

    // MustVerifyEmail: devuelve el email para el enlace de verificación.
    public function getEmailForVerification(): string
    {
        return $this->email ?? '';
    }

    // Notifiable: indica a qué dirección enviar la notificación de email.
    public function routeNotificationForMail(): string
    {
        return $this->email ?? '';
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'id_persona');
    }

    // Accessor para id_depa desde per_dep
    public function getIdDepaAttribute(): mixed
    {
        if (!$this->id_persona)
            return null;

        $perDep = \Illuminate\Support\Facades\DB::table('per_dep')
            ->where('id_persona', $this->id_persona)
            ->first();

        return $perDep->id_depa ?? null;
    }
}

