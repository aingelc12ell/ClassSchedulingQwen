<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\TeacherController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class TeacherControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new TeacherController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateTeacherSuccess()
    {
        $data = [
            'id' => 'T001',
            'name' => 'Dr. John Smith',
            'qualifiedSubjectIds' => ['MATH101', 'MATH102', 'STAT201']
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('teacher', $body);
        $this->assertEquals('T001', $body['teacher']['id']);
        $this->assertEquals('Dr. John Smith', $body['teacher']['name']);
        $this->assertNotEmpty($body['teacher']['qualified_subject_ids']);
    }

    public function testCreateTeacherWithMinimumName()
    {
        $data = [
            'id' => 'T002',
            'name' => 'Dr',
            'qualifiedSubjectIds' => ['PHYS101']
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('teacher', $body);
        $this->assertEquals('T002', $body['teacher']['id']);
        $this->assertEquals('Dr', $body['teacher']['name']);
    }

    public function testCreateTeacherMissingFields()
    {
        $data = [
            'id' => 'T001',
            'name' => 'Dr. John Smith'
            // Missing required field: qualifiedSubjectIds
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Validation failed', $body['error']);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('qualifiedSubjectIds', $body['errors']);
    }

    public function testCreateTeacherMissingId()
    {
        $data = [
            'name' => 'Dr. Jane Doe',
            'qualifiedSubjectIds' => ['CHEM101']
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Validation failed', $body['error']);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('id', $body['errors']);
    }

    public function testCreateTeacherShortName()
    {
        $data = [
            'id' => 'T003',
            'name' => 'A', // Too short (less than 2 characters)
            'qualifiedSubjectIds' => ['BIOL101']
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Validation failed', $body['error']);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('name', $body['errors']);
    }

    public function testCreateTeacherInvalidQualifiedSubjectIds()
    {
        $data = [
            'id' => 'T004',
            'name' => 'Dr. Bob Wilson',
            'qualifiedSubjectIds' => [] // Empty array should fail json_array validation
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Validation failed', $body['error']);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('qualifiedSubjectIds', $body['errors']);
    }

    public function testCreateTeacherNonArrayQualifiedSubjectIds()
    {
        $data = [
            'id' => 'T005',
            'name' => 'Dr. Alice Brown',
            'qualifiedSubjectIds' => 'MATH101' // Should be array, not string
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Validation failed', $body['error']);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('qualifiedSubjectIds', $body['errors']);
    }

    public function testCreateTeacherWithMultipleSubjects()
    {
        $data = [
            'id' => 'T006',
            'name' => 'Prof. Sarah Johnson',
            'qualifiedSubjectIds' => ['MATH101', 'MATH102', 'STAT201', 'STAT301', 'CALC101']
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/teachers')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('teacher', $body);
        $this->assertEquals('T006', $body['teacher']['id']);
        $this->assertEquals('Prof. Sarah Johnson', $body['teacher']['name']);
        
        $qualifiedSubjects = json_decode($body['teacher']['qualified_subject_ids'], true);
        $this->assertCount(5, $qualifiedSubjects);
        $this->assertContains('MATH101', $qualifiedSubjects);
        $this->assertContains('CALC101', $qualifiedSubjects);
    }

    public function testListTeachers()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/teachers');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('teachers', $body);
        $this->assertIsArray($body['teachers']);
    }
}