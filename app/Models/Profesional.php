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
        'puntuacion_promedio'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

}