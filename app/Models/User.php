<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Cliente;
use App\Models\Profesional;
use App\Models\Actividad;

    class User extends Authenticatable
    {
        /** @use HasFactory<UserFactory> */
        use HasApiTokens, HasFactory, Notifiable;

        /**
         * Get the attributes that should be cast.
         *
         * @return array<string, string>
         */

        protected $fillable = [
            'nombre',
            'apellido',
            'telefono',
            'role',
            'email',
            'password',
        ];

        protected $hidden = [
            'password',
            'remember_token',
        ];

        protected function casts(): array
        {
            return [
                'email_verified_at' => 'datetime',
                'password' => 'hashed',
            ];
        }

        public function cliente(){
            return $this->hasOne(Cliente::class);
        }

        public function profesional(){
            return $this->hasOne(Profesional::class);
        }

        public function esCliente(){
            return $this->role == 'cliente';
        }

        public function esProfesional(){
            return $this->role == 'profesional';
        }

        public function actividades(){
            return $this->hasMany(Actividad::class, 'usuario_id');
        }

        public function receivesBroadcastNotificationsOn(): string
        {
            return 'usuario.' . $this->id;
        }

    }
