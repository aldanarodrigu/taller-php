<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->boolean('requiere_reprogramacion')
                ->default(false)
                ->after('estado');

            $table->foreignId('excepcion_id')
                ->nullable()
                ->after('requiere_reprogramacion')
                ->constrained('excepciones')
                ->nullOnDelete();

            $table->string('motivo_reprogramacion')
                ->nullable()
                ->after('excepcion_id');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('excepcion_id');
            $table->dropColumn('motivo_reprogramacion');
            $table->dropColumn('requiere_reprogramacion');
        });
    }
};