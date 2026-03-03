<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            // Hacer apellido_m nullable para soportar el flujo de registro simplificado
            $table->string('apellido_m', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('apellido_m', 255)->nullable(false)->change();
        });
    }
};
