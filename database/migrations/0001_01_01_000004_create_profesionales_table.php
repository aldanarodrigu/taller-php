<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('modalidad_atencion', ['presencial', 'virtual', 'ambas']);
            $table->string('descripcion');
            $table->float('puntuacion_promedio')->default(0);
            $table->timestamps();
        });
    }

};