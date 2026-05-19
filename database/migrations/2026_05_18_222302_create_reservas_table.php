<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('estado', ['pendiente', 'confirmada', 'pagada', 'en_curso', 'finalizada' ,'cancelada', 'no_asistida'])->default('pendiente');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('pago_id')->nullable()->constrained('pagos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
