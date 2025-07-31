<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\ClassController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class ClassControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new ClassController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateClassSuccess()
    {
        $data = [
            'class_id' => 'CLASS001',
            'subject_id' => 'CS101',
            'teacher_id' => 'T001',
            'room_id' => 'R101',
            'time_slot_id' => 1,
            'day' => 'Mon',
            'term' => 'Fall2024',
            'is_override' => false
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/classes')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('class', $body);
        $this->assertEquals('CLASS001', $body['class']['class_id']);
        $this->assertEquals('CS101', $body['class']['subject_id']);
        $this->assertEquals('T001', $body['class']['teacher_id']);
        $this->assertEquals('R101', $body['class']['room_id']);
        $this->assertEquals(1, $body['class']['time_slot_id']);
        $this->assertEquals('Mon', $body['class']['day']);
        $this->assertEquals('Fall2024', $body['class']['term']);
        $this->assertEquals(false, $body['class']['is_override']);
    }

    public function testCreateClassMissingFields()
    {
        $data = [
            'class_id' => 'CLASS001',
            'subject_id' => 'CS101'
            // Missing required fields: teacher_id, room_id, time_slot_id, day, term
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/classes')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing fields', $body['error']);
        $this->assertArrayHasKey('fields', $body);
        $this->assertContains('teacher_id', $body['fields']);
        $this->assertContains('room_id', $body['fields']);
        $this->assertContains('time_slot_id', $body['fields']);
        $this->assertContains('day', $body['fields']);
        $this->assertContains('term', $body['fields']);
    }

    public function testCreateClassInvalidDayFormat()
    {
        $data = [
            'class_id' => 'CLASS001',
            'subject_id' => 'CS101',
            'teacher_id' => 'T001',
            'room_id' => 'R101',
            'time_slot_id' => 1,
            'day' => 'Monday', // Invalid: should be 3 characters
            'term' => 'Fall2024'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/classes')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Day must be 3 characters (e.g., Mon, Tue)', $body['error']);
    }

    public function testCreateClassInvalidTimeSlotId()
    {
        $data = [
            'class_id' => 'CLASS001',
            'subject_id' => 'CS101',
            'teacher_id' => 'T001',
            'room_id' => 'R101',
            'time_slot_id' => 'not-numeric', // Invalid: should be numeric
            'day' => 'Mon',
            'term' => 'Fall2024'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/classes')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('time_slot_id must be numeric', $body['error']);
    }

    public function testCreateClassWithDefaultOverride()
    {
        $data = [
            'class_id' => 'CLASS002',
            'subject_id' => 'CS102',
            'teacher_id' => 'T002',
            'room_id' => 'R102',
            'time_slot_id' => 2,
            'day' => 'Tue',
            'term' => 'Fall2024'
            // is_override not provided, should default to false
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/classes')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('class', $body);
        $this->assertEquals(false, $body['class']['is_override']);
    }

    public function testListClasses()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/classes');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('classes', $body);
        $this->assertIsArray($body['classes']);
    }

    public function testListClassesByTerm()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/classes?term=Fall2024')
            ->withQueryParams(['term' => 'Fall2024']);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('classes', $body);
        $this->assertIsArray($body['classes']);
    }
}