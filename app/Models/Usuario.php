<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Usuario extends Model
{
    protected $table = 'usuarios';

    public $timestamps = false;

    protected $fillable = [
        'id_persona',
        'id_tipo_usuario',
        'email',
        'pass',
        'admin',
    ];

    protected $hidden = [
        'pass',
    ];

    public function getTable(): string
    {
        static $resolvedTable;

        if ($resolvedTable !== null) {
            return $resolvedTable;
        }

        if (Schema::hasTable('usuario')) {
            $resolvedTable = 'usuario';

            return $resolvedTable;
        }

        $resolvedTable = parent::getTable();

        return $resolvedTable;
    }
}
