<?php
namespace App\Controllers;

use App\Helpers\ResponseHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\ClassModel;
use App\Services\SchedulingEngine;
use App\Services\ValidationService;

class ClassController
{
    private array $storage;

    public function __construct($storagePath = __DIR__ . '/../../data/storage.json')
    {
        $this->storage = json_decode(file_get_contents($storagePath), true) ?: [];
    }

    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        $rules = [
            #'class_id' => 'required|integer',
            'subject_id' => 'required|integer|exists_in:App\Models\Subject,id',
            'teacher_id' => 'required|integer|exists_in:App\Models\Teacher,id',
            'room_id' => 'required|integer|exists_in:App\Models\Room,id',
            'time_slot_id' => 'required|integer|exists_in:App\Models\TimeSlot,id',
            'day' => 'required|string|min:3|max:3|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'term' => 'required|string|max:20',
            'is_override' => 'boolean|override_allowed'
        ];

        if (!$validator->validate($data, $rules)) {
            return ResponseHelper::json($response,[
                'error' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Previously deleted manual validation code:
        // $required = ['class_id', 'subject_id', 'teacher_id', 'room_id', 'time_slot_id', 'day', 'term'];
        // $missing = array_filter($required, fn($key) => !isset($data[$key]));
        // if (!empty($missing)) {
        //     return ResponseHelper::json($response,['error' => 'Missing fields', 'fields' => $missing], 400);
        // }
        //
        // // Validate day format (3 characters)
        // if (strlen($data['day']) !== 3) {
        //     return ResponseHelper::json($response,['error' => 'Day must be 3 characters (e.g., Mon, Tue)'], 400);
        // }
        //
        // // Validate time_slot_id is numeric
        // if (!is_numeric($data['time_slot_id'])) {
        //     return ResponseHelper::json($response,['error' => 'time_slot_id must be numeric'], 400);
        // }

        try {
            $class = ClassModel::create([
                #'class_id' => $data['class_id'],
                'subject_id' => $data['subject_id'],
                'teacher_id' => $data['teacher_id'],
                'room_id' => $data['room_id'],
                'time_slot_id' => (int)$data['time_slot_id'],
                'day' => $data['day'],
                'term' => $data['term'],
                'is_override' => $data['is_override'] ?? false,
            ]);

            return ResponseHelper::json($response,['class' => $class], 201);
        } catch (\Exception $e) {
            return ResponseHelper::json($response,['error' => 'Failed to create class'], 500);
        }
    }
    public function generateSchedule(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $term = $data['term'] ?? null;

        $engine = new SchedulingEngine();
        $schedule = $engine->generateSchedule(['term' => $term]);

        $payload = json_encode(['classes' => $schedule], JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        /*$data = $request->getParsedBody();

        // Extract input
        $studentsData = $data['students'] ?? $this->storage['students'] ?? [];
        $teachersData = $data['teachers'] ?? $this->storage['teachers'] ?? [];
        $roomsData = $data['rooms'] ?? $this->storage['rooms'] ?? [];
        $subjectsData = $data['subjects'] ?? $this->storage['subjects'] ?? [];
        $curriculumsData = $data['curriculums'] ?? $this->storage['curriculums'] ?? [];

        // Validate and instantiate models
        $students = array_map(fn($s) => new Student($s), $studentsData);
        $teachers = array_map(fn($t) => new Teacher($t), $teachersData);
        $rooms = array_map(fn($r) => new Room($r), $roomsData);
        $subjects = array_map(fn($s) => new Subject($s), $subjectsData);
        $curriculums = array_map(fn($c) => new Curriculum($c), $curriculumsData);

        // Run scheduling engine
        $engine = new SchedulingEngine($students, $teachers, $rooms, $subjects, $curriculums);
        $classes = $engine->generateSchedule();

        $result = array_map(function($cls) {
            return (array)$cls;
        }, $classes);

        $payload = json_encode(['classes' => $result], JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);*/
    }

    public function list(Request $request, Response $response): Response
    {
        $term = $request->getQueryParam('term');
        $query = ClassModel::query();
        if ($term) $query->where('term', $term);
        $classes = $query->get();

        $payload = json_encode(['classes' => $classes], JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $class = ClassModel::find($args['id']);
        if (!$class) {
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();
        $class->fill($data);
        $class->is_override = true; // Mark as manual
        $class->save();

        $payload = json_encode(['class' => $class]);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $class = ClassModel::find($args['id']);
        if ($class && !$class->is_override) {
            return ResponseHelper::json($response,['error' => 'Cannot delete auto-generated class'], 400);
        }
        $class?->delete();

        return $response->withStatus(204);
    }
}