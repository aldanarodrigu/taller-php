<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->tinyInteger('puntuacion'); // 1 a 5
            $table->text('comentario')->nullable();
            $table->boolean('visible')->default(true);
            $table->timestamps();

            // Un cliente solo puede calificar una reserva una vez
            $table->unique('reserva_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};