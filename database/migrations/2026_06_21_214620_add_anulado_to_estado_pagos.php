<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE pagos
             DROP CONSTRAINT IF EXISTS pagos_estado_check'
        );

        DB::statement(
            "ALTER TABLE pagos
             ADD CONSTRAINT pagos_estado_check
             CHECK (estado IN (
                'pendiente',
                'aprobado',
                'rechazado',
                'reembolsado',
                'anulado'
             ))"
        );
    }

    public function down(): void
    {
        DB::table('pagos')
            ->where('estado', 'anulado')
            ->update(['estado' => 'pendiente']);

        DB::statement(
            'ALTER TABLE pagos
             DROP CONSTRAINT IF EXISTS pagos_estado_check'
        );

        DB::statement(
            "ALTER TABLE pagos
             ADD CONSTRAINT pagos_estado_check
             CHECK (estado IN (
                'pendiente',
                'aprobado',
                'rechazado',
                'reembolsado'
             ))"
        );
    }
};