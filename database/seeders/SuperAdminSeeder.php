<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'              => 'Administrador del Sistema',
            'email'             => 'admin@cep.gob.ve',
            'password'          => Hash::make('Admin@CEP2026'),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super-admin');

        $this->command->info('✅ Usuario administrador creado:');
        $this->command->table(
            ['Campo', 'Valor'],
            [
                ['Nombre',     $user->name],
                ['Correo',     $user->email],
                ['Contraseña', 'Admin@CEP2026'],
                ['Rol',        'super-admin'],
            ]
        );
        $this->command->warn('⚠ Cambia la contraseña en el primer inicio de sesión.');
    }
}
