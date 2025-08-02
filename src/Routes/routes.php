<?php
/*use Slim\App;

return function (App $app) {
    // Students
    $app->post('/students', [\App\Controllers\StudentController::class, 'create']);
    $app->get('/students', [\App\Controllers\StudentController::class, 'list']);

    // Teachers
    $app->post('/teachers', [\App\Controllers\TeacherController::class, 'create']);
    $app->get('/teachers', [\App\Controllers\TeacherController::class, 'list']);

    // Rooms
    $app->post('/rooms', [\App\Controllers\RoomController::class, 'create']);
    $app->get('/rooms', [\App\Controllers\RoomController::class, 'list']);

    // Subjects
    $app->post('/subjects', [\App\Controllers\SubjectController::class, 'create']);
    $app->get('/subjects', [\App\Controllers\SubjectController::class, 'list']);

    // Curriculums
    $app->post('/curriculums', [\App\Controllers\CurriculumController::class, 'create']);
    $app->get('/curriculums', [\App\Controllers\CurriculumController::class, 'list']);

    // TimeSlots
    $app->post('/time-slots', [\App\Controllers\TimeSlotController::class, 'create']);
    $app->get('/time-slots', [\App\Controllers\TimeSlotController::class, 'list']);
    $app->put('/time-slots/{id}', [\App\Controllers\TimeSlotController::class, 'update']);

    // Classes
    $app->post('/classes/generate', [\App\Controllers\ClassController::class, 'generateSchedule']);
    $app->get('/classes', [\App\Controllers\ClassController::class, 'list']);
    $app->put('/classes/{id}', [\App\Controllers\ClassController::class, 'update']);
    $app->delete('/classes/{id}', [\App\Controllers\ClassController::class, 'delete']);

    // Exemptions
    $app->get('/exemptions', [\App\Controllers\ExemptionController::class, 'list']);
    $app->post('/exemptions', [\App\Controllers\ExemptionController::class, 'create']);
};*/


include __DIR__.'/public.php';
include __DIR__.'/protected.php';
include __DIR__.'/admin.php';