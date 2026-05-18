<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$result = \Illuminate\Support\Facades\Auth::attempt(['email' => 'admin@cep.gob.ve', 'password' => '12345678']);
echo "Login attempt: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
if (!$result) {
    echo "Checking user...\n";
    $u = \App\Models\User::where('email', 'admin@cep.gob.ve')->first();
    if ($u) {
        echo "User exists.\n";
        echo "Password match: " . (\Illuminate\Support\Facades\Hash::check('12345678', $u->password) ? 'YES' : 'NO') . "\n";
        echo "Activo: " . $u->activo . "\n";
    } else {
        echo "User not found.\n";
    }
}
