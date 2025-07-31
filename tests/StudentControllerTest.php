<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\StudentController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class StudentControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new StudentController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateStudentSuccess()
    {
        $data = [
            'id' => 'S001',
            'name' => 'John Doe',
            'curriculumId' => 'CURR001',
            'enrollmentCount' => 2
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('student', $body);
        $this->assertEquals('S001', $body['student']['id']);
        $this->assertEquals('John Doe', $body['student']['name']);
        $this->assertEquals('CURR001', $body['student']['curriculumId']);
        $this->assertEquals(2, $body['student']['enrollmentCount']);
    }

    public function testCreateStudentWithDefaultEnrollmentCount()
    {
        $data = [
            'id' => 'S002',
            'name' => 'Jane Smith',
            'curriculumId' => 'CURR002'
            // enrollmentCount not provided, should default to 1
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('student', $body);
        $this->assertEquals('S002', $body['student']['id']);
        $this->assertEquals('Jane Smith', $body['student']['name']);
        $this->assertEquals('CURR002', $body['student']['curriculumId']);
        $this->assertEquals(1, $body['student']['enrollmentCount']);
    }

    public function testCreateStudentMissingFields()
    {
        $data = [
            'id' => 'S001',
            'name' => 'John Doe'
            // Missing required field: curriculumId
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing fields', $body['error']);
        $this->assertArrayHasKey('fields', $body);
        $this->assertContains('curriculumId', $body['fields']);
    }

    public function testCreateStudentMissingMultipleFields()
    {
        $data = [
            'id' => 'S001'
            // Missing required fields: name, curriculumId
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing fields', $body['error']);
        $this->assertArrayHasKey('fields', $body);
        $this->assertContains('name', $body['fields']);
        $this->assertContains('curriculumId', $body['fields']);
    }

    public function testCreateStudentMissingId()
    {
        $data = [
            'name' => 'John Doe',
            'curriculumId' => 'CURR001'
            // Missing required field: id
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing fields', $body['error']);
        $this->assertArrayHasKey('fields', $body);
        $this->assertContains('id', $body['fields']);
    }

    public function testCreateStudentWithZeroEnrollmentCount()
    {
        $data = [
            'id' => 'S003',
            'name' => 'Bob Johnson',
            'curriculumId' => 'CURR003',
            'enrollmentCount' => 0
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('student', $body);
        $this->assertEquals(0, $body['student']['enrollmentCount']);
    }

    public function testCreateStudentWithHighEnrollmentCount()
    {
        $data = [
            'id' => 'S004',
            'name' => 'Alice Brown',
            'curriculumId' => 'CURR004',
            'enrollmentCount' => 5
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('student', $body);
        $this->assertEquals('S004', $body['student']['id']);
        $this->assertEquals('Alice Brown', $body['student']['name']);
        $this->assertEquals('CURR004', $body['student']['curriculumId']);
        $this->assertEquals(5, $body['student']['enrollmentCount']);
    }

    public function testCreateStudentWithLongName()
    {
        $data = [
            'id' => 'S005',
            'name' => 'Christopher Alexander Montgomery-Wellington III',
            'curriculumId' => 'CURR005'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/students')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('student', $body);
        $this->assertEquals('S005', $body['student']['id']);
        $this->assertEquals('Christopher Alexander Montgomery-Wellington III', $body['student']['name']);
        $this->assertEquals('CURR005', $body['student']['curriculumId']);
    }

    public function testListStudents()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/students');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('students', $body);
        $this->assertIsArray($body['students']);
    }

    public function testListStudentsByTerm()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/students?term=Fall2024')
            ->withQueryParams(['term' => 'Fall2024']);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('students', $body);
        $this->assertIsArray($body['students']);
    }
}