<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaqueteCliente extends Model
{
    protected $table = 'paquete_cliente';

    protected $fillable = [
        'paquete_id',
        'cliente_id',
        'sesiones_disponibles',
        'sesiones_usadas',
        'estado',
        'fecha_compra',
        'fecha_vencimiento',
    ];

    public function paquete()
    {
        return $this->belongsTo(Paquete::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'paquete_cliente_id');
    }
}
