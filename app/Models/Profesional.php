<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Profesional extends Model
{

    protected $table = 'profesionales';
    protected $fillable = [
        'user_id',
        'descripcion',
        'modalidad_atencion',
        'puntuacion_promedio',
        'profesion',
        'foto',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }

}