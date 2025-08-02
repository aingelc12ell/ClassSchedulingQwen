<?php
use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$capsule = new Capsule();

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'] ?? 'localhost',
    'port'      => $_ENV['DB_PORT'] ?? 3306,
    'database'  => $_ENV['DB_DATABASE'] ?? 'scheduling_db',
    'username'  => $_ENV['DB_USERNAME'] ?? 'root',
    'password'  => $_ENV['DB_PASSWORD'] ?? '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
    'strict'    => true,
    'engine'    => 'InnoDB',
]);

// Make this Capsule instance available globally
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// Optional: Enable query logging (for debugging)
$capsule->getConnection()->enableQueryLog();

return $capsule;