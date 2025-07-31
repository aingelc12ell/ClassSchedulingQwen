<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Room;
use App\Models\TimeSlot;
use App\Models\Student;
use App\Models\Curriculum;
use App\Models\ConflictExemption;

echo "Testing database schemas for all models:\n\n";

$models = [
    'User' => User::class,
    'ClassModel' => ClassModel::class,
    'Teacher' => Teacher::class,
    'Subject' => Subject::class,
    'Room' => Room::class,
    'TimeSlot' => TimeSlot::class,
    'Student' => Student::class,
    'Curriculum' => Curriculum::class,
    'ConflictExemption' => ConflictExemption::class,
];

foreach ($models as $name => $class) {
    echo "=== $name Schema ===\n";
    echo $class::$schema . "\n\n";
}

echo "All schemas loaded successfully!\n";
