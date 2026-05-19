<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Profesional;

class Disponibilidad extends Model
{
    protected $table = 'disponibilidades';
    
    protected $fillable = [
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'buffer',
        'hora_inicio_pausa',
        'hora_fin_pausa',
        'profesional_id'
    ];
            
    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }
    
}
