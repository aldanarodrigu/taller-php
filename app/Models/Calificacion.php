<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = [
        'reserva_id',
        'cliente_id',
        'puntuacion',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'puntuacion' => 'integer',
        ];
    }

    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}