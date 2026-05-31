<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->nullOnDelete()->after('id');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete()->after('reserva_id');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reserva_id');
            $table->dropConstrainedForeignId('usuario_id');
        });
    }
};