<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaRolesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Eliminando usuarios de prueba anteriores (correos numéricos)...');
        // Eliminar los usuarios creados anteriormente con el formato correoN@correo.com o rolN@correo.com
        User::where('email', 'like', 'correo%@correo.com')->delete();
        User::where('email', 'like', 'rol%@correo.com')->delete();

        $this->command->info('Creando usuarios de prueba para cada ROL...');

        $roles = Role::all();
        $password = Hash::make('12345678');
        $contador = 1;

        foreach ($roles as $rol) {
            $email = "rol{$contador}@correo.com";

            $user = User::create([
                'name' => "Prueba ({$rol->name})",
                'email' => $email,
                'password' => $password,
                'email_verified_at' => now(),
            ]);

            // Asignar el rol al usuario
            $user->assignRole($rol);

            $this->command->line("Usuario creado: {$email} | Rol asignado: {$rol->name}");
            $contador++;
        }

        $this->command->info('');
        $this->command->info("✅ Se eliminaron los usuarios anteriores y se crearon {$roles->count()} usuarios por rol.");
        $this->command->info("Todos usan la contraseña:  12345678");
    }
}
