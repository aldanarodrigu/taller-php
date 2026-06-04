<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';

    protected $fillable = [
        'profesional_id',
        'nombre',
        'descripcion',
        'tipo',
        'modalidad',
        'precio',
        'duracion_minutos',
        'activo',
        'videollamada',
        'cancelacion_horas_minimas',
        'direccion',
        'latitud',
        'longitud',
    ];

    protected function casts(): array
    {
        return [
            'precio'    => 'decimal:2',
            'latitud'   => 'decimal:7',
            'longitud'  => 'decimal:7',
            'activo'    => 'boolean',
            'videollamada' => 'boolean',
        ];
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class);
    }

    public function calificacion()
    {
        return $this->hasOne(Calificacion::class);
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    public function paquetes()
    {
        return $this->belongsToMany(Paquete::class, 'paquete_servicio');
    }

    public function scopeActivo($query)
    {
        return $query->whereRaw('"activo" IS TRUE');
    }
}