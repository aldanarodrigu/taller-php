<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->foreignId('paquete_cliente_id')
                  ->nullable()
                  ->constrained('paquete_cliente')
                  ->nullOnDelete()
                  ->after('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['paquete_cliente_id']);
            $table->dropColumn('paquete_cliente_id');
        });
    }
};
