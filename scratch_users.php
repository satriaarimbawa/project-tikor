<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$database = app(\Kreait\Firebase\Contract\Database::class);

$users = $database->getReference('users')->getValue();
print_r(array_slice($users, 0, 1));
