<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('excepciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profesional_id')->constrained('profesionales');
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->enum('tipo', ['bloqueo','habilitacion_extra','pausa','feriado','licencia']);
            $table->text('motivo')->nullable();
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('excepciones');
    }
};
