<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('disponibilidades', function (Blueprint $table) {
            $table->id();
            $table->string('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->time('hora_inicio_pausa')->nullable();
            $table->time('hora_fin_pausa')->nullable();
            $table->integer('buffer')->default(0);
            $table->foreignId('profesional_id')->constrained('profesionales');
            $table->timestamps();
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('disponibilidades');
    }
};
