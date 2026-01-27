<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre');
            $table->string('apellido_p');
            $table->string('apellido_m');
            $table->string('celular', 20);
            $table->boolean('activo')->default(true);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('rol');
        });

        Schema::create('departamentos', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('depa');
            $table->boolean('moroso')->default(false);
            $table->string('codigo', 5);
        });

        Schema::create('tipos_pago', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('tipo');
        });

        Schema::create('motivos', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('motivo');
        });

        Schema::create('eventos', function (Blueprint $table): void {
            $table->increments('id');
            $table->dateTime('fecha');
            $table->string('descripcion');
        });

        Schema::create('usuarios', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_persona');
            $table->string('pass');
            $table->boolean('admin')->default(false);

            $table->foreign('id_persona')
                ->references('id')
                ->on('personas')
                ->cascadeOnDelete();
        });

        Schema::create('per_dep', function (Blueprint $table): void {
            $table->unsignedInteger('id_persona');
            $table->unsignedInteger('id_depa');
            $table->unsignedInteger('id_rol');
            $table->boolean('residente')->default(false);
            $table->string('codigo');

            $table->primary(['id_persona', 'id_depa', 'id_rol']);
            $table->foreign('id_persona')
                ->references('id')
                ->on('personas')
                ->cascadeOnDelete();
            $table->foreign('id_depa')
                ->references('id')
                ->on('departamentos')
                ->cascadeOnDelete();
            $table->foreign('id_rol')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();
        });

        Schema::create('controles', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('codigo');
            $table->unsignedInteger('id_depa');

            $table->foreign('id_depa')
                ->references('id')
                ->on('departamentos')
                ->cascadeOnDelete();
        });

        Schema::create('carros', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_depa');
            $table->string('placa');
            $table->string('marca');
            $table->string('modelo');
            $table->string('color');

            $table->foreign('id_depa')
                ->references('id')
                ->on('departamentos')
                ->cascadeOnDelete();
        });

        Schema::create('asistencia', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_persona');
            $table->unsignedInteger('id_evento');
            $table->time('hora');

            $table->foreign('id_persona')
                ->references('id')
                ->on('personas')
                ->cascadeOnDelete();
            $table->foreign('id_evento')
                ->references('id')
                ->on('eventos')
                ->cascadeOnDelete();
        });

        Schema::create('preguntas', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('pregunta');
            $table->unsignedInteger('id_evento');

            $table->foreign('id_evento')
                ->references('id')
                ->on('eventos')
                ->cascadeOnDelete();
        });

        Schema::create('respuestas', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_pregunta');
            $table->unsignedInteger('id_asistente');
            $table->boolean('respuesta');

            $table->foreign('id_pregunta')
                ->references('id')
                ->on('preguntas')
                ->cascadeOnDelete();
            $table->foreign('id_asistente')
                ->references('id')
                ->on('asistencia')
                ->cascadeOnDelete();
        });

        Schema::create('reportes', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_usuario');
            $table->string('reporte');
            $table->timestamp('fecha')->useCurrent();

            $table->foreign('id_usuario')
                ->references('id')
                ->on('usuarios')
                ->cascadeOnDelete();
        });

        Schema::create('pagos', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('id_depa');
            $table->decimal('monto', 10, 2);
            $table->unsignedInteger('id_tipo');
            $table->date('fecha');
            $table->unsignedInteger('id_motivo');
            $table->string('descripcion');
            $table->string('comprobante')->nullable();
            $table->boolean('efectuado')->default(false);
            $table->unsignedInteger('id_reporte')->nullable();

            $table->foreign('id_depa')
                ->references('id')
                ->on('departamentos')
                ->cascadeOnDelete();
            $table->foreign('id_tipo')
                ->references('id')
                ->on('tipos_pago')
                ->cascadeOnDelete();
            $table->foreign('id_motivo')
                ->references('id')
                ->on('motivos')
                ->cascadeOnDelete();
            $table->foreign('id_reporte')
                ->references('id')
                ->on('reportes')
                ->nullOnDelete();
        });

        Schema::create('mantenimiento', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedTinyInteger('mes');
            $table->unsignedSmallInteger('ano');
            $table->unsignedInteger('id_depa');
            $table->boolean('completado')->default(false);
            $table->decimal('monto', 10, 2);
            $table->unsignedInteger('id_pago')->nullable();

            $table->foreign('id_depa')
                ->references('id')
                ->on('departamentos')
                ->cascadeOnDelete();
            $table->foreign('id_pago')
                ->references('id')
                ->on('pagos')
                ->nullOnDelete();
        });

        Schema::create('gastos', function (Blueprint $table): void {
            $table->increments('id');
            $table->decimal('monto', 10, 2);
            $table->string('descripcion');
            $table->date('fecha');
        });

        Schema::create('mensajes', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('remitente');
            $table->unsignedInteger('destinatario')->nullable();
            $table->unsignedInteger('id_depaA');
            $table->unsignedInteger('id_depaB')->nullable();
            $table->string('mensaje', 1000);
            $table->timestamp('fecha')->useCurrent();

            $table->foreign('remitente')
                ->references('id')
                ->on('usuarios')
                ->cascadeOnDelete();
            $table->foreign('destinatario')
                ->references('id')
                ->on('usuarios')
                ->nullOnDelete();
            $table->foreign('id_depaA')
                ->references('id')
                ->on('departamentos')
                ->cascadeOnDelete();
            $table->foreign('id_depaB')
                ->references('id')
                ->on('departamentos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('mantenimiento');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('reportes');
        Schema::dropIfExists('respuestas');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('asistencia');
        Schema::dropIfExists('carros');
        Schema::dropIfExists('controles');
        Schema::dropIfExists('per_dep');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('eventos');
        Schema::dropIfExists('motivos');
        Schema::dropIfExists('tipos_pago');
        Schema::dropIfExists('departamentos');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('personas');
    }
};
