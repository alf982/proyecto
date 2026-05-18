<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Schema::table('empleados', function (Blueprint $table) {
    if (Schema::hasColumn('empleados', 'nombres')) {
        $table->renameColumn('nombres', 'nombre');
    }
    if (Schema::hasColumn('empleados', 'primer_apellido')) {
        $table->renameColumn('primer_apellido', 'apellido');
    }
});
echo "Done.\n";
