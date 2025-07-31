<?php
// Admin-only routes
use Slim\Routing\RouteCollectorProxy;

$app->group('/admin', function (RouteCollectorProxy $group) {
    // Future admin endpoints could go here
    $group->get('/users', function ($request, $response, $args) {
        return $response->withJson(['message' => 'Admin users endpoint']);
    });
})->add(function ($request, $handler) use ($container) {
    // Admin authentication middleware
    $adminMiddleware = App\Middleware\AuthMiddleware::requireAdmin($container->get('authService'));
    return $adminMiddleware($request, $handler);
});
