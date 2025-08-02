<?php

use App\Controllers\ClassController;
use App\Controllers\CurriculumController;
use App\Controllers\ExemptionController;
use App\Controllers\RoomController;
use App\Controllers\StudentController;
use App\Controllers\SubjectController;
use App\Controllers\TeacherController;
use App\Controllers\TimeSlotController;
use Slim\App;

return function (App $app) {
    // Students
    $app->post('/students', [StudentController::class, 'create']);
    $app->get('/students', [StudentController::class, 'list']);

    // Teachers
    $app->post('/teachers', [TeacherController::class, 'create']);
    $app->get('/teachers', [TeacherController::class, 'list']);

    // Rooms
    $app->post('/rooms', [RoomController::class, 'create']);
    $app->get('/rooms', [RoomController::class, 'list']);

    // Subjects
    $app->post('/subjects', [SubjectController::class, 'create']);
    $app->get('/subjects', [SubjectController::class, 'list']);

    // Curriculums
    $app->post('/curriculums', [CurriculumController::class, 'create']);
    $app->get('/curriculums', [CurriculumController::class, 'list']);

    // TimeSlots
    $app->post('/time-slots', [TimeSlotController::class, 'create']);
    $app->get('/time-slots', [TimeSlotController::class, 'list']);
    $app->put('/time-slots/{id}', [TimeSlotController::class, 'update']);

    // Classes
    $app->post('/classes/generate', [ClassController::class, 'generateSchedule']);
    $app->get('/classes', [ClassController::class, 'list']);
    $app->put('/classes/{id}', [ClassController::class, 'update']);
    $app->delete('/classes/{id}', [ClassController::class, 'delete']);

    // Exemptions
    $app->get('/exemptions', [ExemptionController::class, 'list']);
    $app->post('/exemptions', [ExemptionController::class, 'create']);
};


/*include __DIR__.'/public.php';
include __DIR__.'/protected.php';
include __DIR__.'/admin.php';*/