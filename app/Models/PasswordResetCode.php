<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $table = 'password_reset_codes';

    /**
     * Disable automatic timestamp management.
     * We only use `created_at` (set manually or via useCurrent()).
     */
    public $timestamps = false;

    protected $fillable = [
        'email',
        'code',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
