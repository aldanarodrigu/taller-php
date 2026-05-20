<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Profesional;

class Excepcion extends Model
{
    protected $table = 'excepciones';
    
    protected $fillable = [
        'profesional_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'tipo',
        'motivo'
    ];
    
    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }
    
}
