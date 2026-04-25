<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Hash;

$u = App\Models\User::where('email', 'admin@sia.gov.ve')->first();
if ($u) {
    echo "ID:             " . $u->id . "\n";
    echo "Nombre:         " . $u->name . "\n";
    echo "Email:          " . $u->email . "\n";
    echo "Activo:         " . ($u->activo ? 'SI' : 'NO') . "\n";
    echo "Email verified: " . ($u->email_verified_at ?? 'NULL') . "\n";
    echo "Password hash:  " . substr($u->password, 0, 40) . "...\n";
    $check = Hash::check('Admin@SIA2024', $u->password);
    echo "Password OK:    " . ($check ? 'SI ✅' : 'NO ❌') . "\n";

    if (!$check) {
        // Resetear la contraseña
        $u->update(['password' => Hash::make('Admin@SIA2024')]);
        echo "\n🔑 Contraseña RESETEADA a: Admin@SIA2024\n";
        $verify = Hash::check('Admin@SIA2024', $u->fresh()->password);
        echo "Verificación post-reset: " . ($verify ? 'OK ✅' : 'FALLO ❌') . "\n";
    }

    // Asegurar email verificado y activo
    if (!$u->email_verified_at || !$u->activo) {
        $u->update([
            'email_verified_at' => now(),
            'activo' => true,
        ]);
        echo "✅ Email marcado como verificado y usuario activado.\n";
    }
} else {
    echo "❌ USUARIO NO ENCONTRADO en BD.\n";
    echo "\nUsuarios existentes:\n";
    App\Models\User::all(['id','name','email','activo'])->each(function($u) {
        echo "  [{$u->id}] {$u->email} — activo=" . ($u->activo ? '1' : '0') . "\n";
    });
}
