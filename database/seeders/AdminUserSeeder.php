<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void{
    User::firstOrCreate(
        ['email' => 'admin@sistema.com'],
        [
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'password' => Hash::make('Admin12345!'),
            'role' => 'admin',
        ]
    );
}

}
