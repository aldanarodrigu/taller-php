<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Profesional extends Model
{

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'descripcion',
        'modalidad_atencion',
        'puntuacion_promedio',
        'ubicacion_id',
        'user_id'
    ];

    public function user(){
        return this->belongsTo(User::class);
    }

}