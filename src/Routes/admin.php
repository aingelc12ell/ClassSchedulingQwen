<?php


use App\Helpers\ResponseHelper;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Middleware\JwtAuthMiddleware;

// Admin-only middleware
$requireAdmin = function($request, $handler){
    $auth = new JwtAuthMiddleware();
    $error = $auth->requireAdmin($request);
    if($error) return $error;
    return $handler->handle($request);
};

// Admin Routes
$app->get('/admin/users', function(Request $request, Response $response){
    $users = User::all();
    return ResponseHelper::json($response, ['users' => $users]);
})->add($requireAdmin);

$app->get('/admin/stats', function(Request $request, Response $response){
    $stats = [
        'total_students' => Student::count(),
        'total_classes' => ClassModel::count(),
    ];
    return ResponseHelper::json($response, $stats);
})->add($requireAdmin);

$app->delete('/admin/classes/{id}', function(Request $request, Response $response, $args){
    $class = ClassModel::find($args['id']);
    if(!$class){
        return ResponseHelper::json($response, ['error' => 'Class not found'], 404);
    }
    $class->delete();
    return ResponseHelper::json($response, ['message' => 'Class deleted']);
})->add($requireAdmin);

/*// Admin-only routes
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
});*/
