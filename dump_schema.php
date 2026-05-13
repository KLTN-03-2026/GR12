<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = DB::select('SHOW TABLES');
foreach($tables as $t) {
    $table = array_values((array)$t)[0];
    echo "\nTable: $table\n";
    $cols = DB::select("SHOW COLUMNS FROM $table");
    foreach($cols as $c) {
        echo $c->Field . ' | ' . $c->Type . ' | ' . ($c->Null == 'YES' ? 'NULL' : 'NOT NULL') . "\n";
    }
}
