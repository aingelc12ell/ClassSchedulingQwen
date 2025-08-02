<?php
// Public routes
use App\Helpers\ResponseHelper;
use App\Controllers\AuthController;
# use Illuminate\Support\Facades\DB;
use Illuminate\Database\Capsule\Manager as Capsule;

$app->post('/auth/register', [AuthController::class, 'register']);
$app->post('/auth/login', [AuthController::class, 'login']);

$app->get('/health/db', function ($request, $response) use ($capsule) {
    try {
        $capsule->getConnection()->getPdo();
        return ResponseHelper::json($response,['status' => 'OK', 'database' => 'Connected']);
    } catch (\Exception $e) {
        return ResponseHelper::json($response,['status' => 'Error', 'database' => $e->getMessage()], 500);
    }
});

$app->get('/test', function ($request, $response) {
    return ResponseHelper::json($response, ['status' => 'OK', 'timestamp' => date('Y-m-d H:i:s')]);
});