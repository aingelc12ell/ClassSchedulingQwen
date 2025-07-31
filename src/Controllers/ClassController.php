<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\ClassModel;
use App\Services\SchedulingEngine;

class ClassController
{
    private array $storage;

    public function __construct($storagePath = __DIR__ . '/../../data/storage.json')
    {
        $this->storage = json_decode(file_get_contents($storagePath), true) ?: [];
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
            return $response->withJson(['error' => 'Cannot delete auto-generated class'], 400);
        }
        $class?->delete();

        return $response->withStatus(204);
    }
}