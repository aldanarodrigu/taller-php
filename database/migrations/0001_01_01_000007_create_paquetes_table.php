<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquetes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('cantidad_sesiones');
            $table->decimal('precio', 10, 2);
            $table->integer('vigencia_dias')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('paquete_servicio', function (Blueprint $table) {
            $table->foreignId('paquete_id')->constrained('paquetes')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->primary(['paquete_id', 'servicio_id']);
        });

        Schema::create('paquete_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paquete_id')->constrained('paquetes')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->integer('sesiones_disponibles');
            $table->integer('sesiones_usadas')->default(0);
            $table->enum('estado', ['activo', 'agotado', 'vencido'])->default('activo');
            $table->timestamp('fecha_compra')->useCurrent();
            $table->timestamp('fecha_vencimiento')->nullable();
            $table->timestamps();
        });

       /* Schema::create('paquete_pago', function (Blueprint $table) {
            $table->foreignId('paquete_id')->constrained('paquetes')->onDelete('cascade');
            $table->foreignId('pago_id')->constrained('pagos')->onDelete('cascade');
            $table->primary(['paquete_id', 'pago_id']);
        }); */
    }

    public function down(): void
    {
        //Schema::dropIfExists('paquete_pago');
        Schema::dropIfExists('paquete_cliente');
        Schema::dropIfExists('paquete_servicio');
        Schema::dropIfExists('paquetes');
    }
};