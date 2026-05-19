<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    protected $table = 'paquetes';

    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad_sesiones',
        'precio',
        'vigencia_dias',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'paquete_servicio');
    }

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'paquete_cliente')
            ->withPivot('sesiones_disponibles', 'sesiones_usadas', 'estado', 'fecha_compra', 'fecha_vencimiento')
            ->withTimestamps();
    }

    /*public function pagos()
    {
        return $this->belongsToMany(Pago::class, 'paquete_pago');
    }*/
}