<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php'; // Initializes Eloquent

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

$files = glob('migrations/*.php');
foreach ($files as $file) {
    require $file;
}

$batch = 1; // Simple batch system

foreach ($files as $file) {
    preg_match('/^(\d+)_(.*?)\.php$/', basename($file), $matches);
    if (!$matches) continue;

    $className = str_replace('.php', '', basename($file));
    $action = $matches[2];

    require $file;
    $migration = new $className;

    echo "Running {$action}...\n";
    $migration->up();
    echo "✅ {$action}\n";
}