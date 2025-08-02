<?php
// Public routes
use Illuminate\Support\Facades\DB;

$app->post('/auth/register', [App\Controllers\AuthController::class, 'register']);
$app->post('/auth/login', [App\Controllers\AuthController::class, 'login']);

$app->get('/health/db', function ($request, $response) {
    try {
        DB::connection()->getPdo();
        return $response->withJson(['status' => 'OK', 'database' => 'Connected']);
    } catch (\Exception $e) {
        return $response->withJson(['status' => 'Error', 'database' => $e->getMessage()], 500);
    }
});