<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();

            // notificación "para" un departamento
            $table->unsignedBigInteger('depa_id');

            // message | multa | asamblea | pago_atrasado
            $table->string('type', 30);

            $table->string('title', 120);
            $table->text('body')->nullable();

            // JSON con ids: multa_id, asamblea_id, pago_id, etc.
            $table->json('data')->nullable();

            // leído/no leído (por depa)
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['depa_id', 'read_at', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
