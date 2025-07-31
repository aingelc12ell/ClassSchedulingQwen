<?php


// Protected routes group
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\{SubjectController, RoomController, TeacherController, CurriculumController, StudentController, ScheduleController, ClassController, AuthController};

$app->group('', function (RouteCollectorProxy $group) {
    // Authentication required endpoints
    $group->get('/auth/me', [App\Controllers\AuthController::class, 'me']);
    $group->get('/profile', [\App\Controllers\AuthController::class, 'profile']);

    // Resource endpoints
    $group->get('/subjects', [App\Controllers\SubjectController::class, 'listAll']);
    $group->post('/subjects', [App\Controllers\SubjectController::class, 'create']);

    $group->get('/rooms', [App\Controllers\RoomController::class, 'listAll']);
    $group->post('/rooms', [App\Controllers\RoomController::class, 'create']);

    $group->get('/teachers', [App\Controllers\TeacherController::class, 'listAll']);
    $group->post('/teachers', [App\Controllers\TeacherController::class, 'create']);

    $group->get('/curriculums', [App\Controllers\CurriculumController::class, 'listAll']);
    $group->post('/curriculums', [App\Controllers\CurriculumController::class, 'create']);

    $group->get('/students', [App\Controllers\StudentController::class, 'listAll']);
    $group->post('/students', [App\Controllers\StudentController::class, 'create']);

    $group->get('/classes', [App\Controllers\ClassController::class, 'listAll']);
    $group->post('/schedule', [App\Controllers\ScheduleController::class, 'generate']);

})->add(function ($request, $handler) use ($container) {
    // Authentication middleware
    $authMiddleware = new App\Middleware\JwtAuthMiddleware($container->get('authService'));
    return $authMiddleware->process($request, $handler);
});