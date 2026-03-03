<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            // Hacer celular nullable para soportar el nuevo flujo de registro
            // donde solo se piden: nombre, apellido, email, password
            $table->string('celular', 20)->nullable()->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('celular', 20)->nullable(false)->change();
        });
    }
};
