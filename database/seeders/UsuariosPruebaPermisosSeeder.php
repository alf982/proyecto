<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creando usuarios de prueba para cada permiso...');

        $permisos = Permission::all();
        $password = Hash::make('12345678');
        $contador = 1;

        foreach ($permisos as $permiso) {
            $email = "correo{$contador}@correo.com";

            // Eliminar si ya existe para evitar errores de unicidad si se corre dos veces
            User::where('email', $email)->delete();

            $user = User::create([
                'name' => "Prueba ({$permiso->name})",
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
            ]);

            // Asignar el permiso directamente al usuario
            $user->givePermissionTo($permiso);

            $this->command->line("Usuario creado: {$email} | Permiso: {$permiso->name}");
            $contador++;
        }

        $this->command->info('');
        $this->command->info("✅ Se crearon {$permisos->count()} usuarios de prueba.");
        $this->command->info("Todos usan la contraseña:  12345678");
    }
}
