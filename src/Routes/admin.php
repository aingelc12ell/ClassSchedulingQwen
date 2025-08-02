<?php
// Admin-only routes
use App\Helpers\ResponseHelper;
use App\Middleware\JwtAuthMiddleware;
use Slim\Routing\RouteCollectorProxy;

$app->group('/admin', function (RouteCollectorProxy $group) {
    // Future admin endpoints could go here
    $group->get('/users', function ($request, $response, $args) {
        return ResponseHelper::json($response,['message' => 'Admin users endpoint']);
    });
})->add(function ($request, $handler) use ($container) {
    // Admin authentication middleware
    $adminMiddleware = (new JwtAuthMiddleware())->requireAdmin($container->get('authService'));
    return $adminMiddleware($request, $handler);
});
