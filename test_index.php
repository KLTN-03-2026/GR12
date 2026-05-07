<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fake authentication for shipper
Auth::loginUsingId(7); // Shipper user_id is 7

$sc = new App\Http\Controllers\ShipperController();
$response = $sc->index();
echo json_encode($response->getData(), JSON_PRETTY_PRINT);
