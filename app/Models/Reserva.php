<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reservas';
    
    protected $fillable = [
        'cliente_id',
        'servicio_id',
        'pago_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
        'livekit_room',
        'livekit_token',
    ];
    
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
    
    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
    
    public function pago()
    {
        return $this->belongsTo(Pago::class);
    }
    
}
