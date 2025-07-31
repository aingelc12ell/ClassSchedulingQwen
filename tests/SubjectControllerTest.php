<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\SubjectController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class SubjectControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new SubjectController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateSubjectSuccess()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'Introduction to Computer Science',
            'units' => 3,
            'weeklyHours' => 4
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('subject', $body);
        $this->assertEquals('CS101', $body['subject']['id']);
        $this->assertEquals('Introduction to Computer Science', $body['subject']['title']);
        $this->assertEquals(3, $body['subject']['units']);
        $this->assertEquals(4, $body['subject']['weekly_hours']);
    }

    public function testCreateSubjectWithMinimumValues()
    {
        $data = [
            'id' => 'M',
            'title' => 'Ma', // Minimum 2 characters
            'units' => 1,
            'weeklyHours' => 1
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('subject', $body);
        $this->assertEquals('M', $body['subject']['id']);
        $this->assertEquals('Ma', $body['subject']['title']);
        $this->assertEquals(1, $body['subject']['units']);
        $this->assertEquals(1, $body['subject']['weekly_hours']);
    }

    public function testCreateSubjectWithMaximumTitle()
    {
        $longTitle = str_repeat('A', 255); // Maximum 255 characters
        $data = [
            'id' => 'LONG001',
            'title' => $longTitle,
            'units' => 4,
            'weeklyHours' => 6
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('subject', $body);
        $this->assertEquals('LONG001', $body['subject']['id']);
        $this->assertEquals($longTitle, $body['subject']['title']);
    }

    public function testCreateSubjectMissingFields()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'Introduction to Computer Science'
            // Missing required fields: units, weeklyHours
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectEmptyId()
    {
        $data = [
            'id' => '',
            'title' => 'Introduction to Computer Science',
            'units' => 3,
            'weeklyHours' => 4
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectShortTitle()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'A', // Too short (minimum 2 characters)
            'units' => 3,
            'weeklyHours' => 4
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectTooLongTitle()
    {
        $tooLongTitle = str_repeat('A', 256); // Too long (maximum 255 characters)
        $data = [
            'id' => 'CS101',
            'title' => $tooLongTitle,
            'units' => 3,
            'weeklyHours' => 4
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectInvalidUnits()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'Introduction to Computer Science',
            'units' => 0, // Invalid (minimum 1)
            'weeklyHours' => 4
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectInvalidWeeklyHours()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'Introduction to Computer Science',
            'units' => 3,
            'weeklyHours' => 0 // Invalid (minimum 1)
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectNonIntegerUnits()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'Introduction to Computer Science',
            'units' => 'three', // Invalid (must be integer)
            'weeklyHours' => 4
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectNonNumericWeeklyHours()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'Introduction to Computer Science',
            'units' => 3,
            'weeklyHours' => 'four' // Invalid (must be numeric)
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertIsArray($body['errors']);
    }

    public function testCreateSubjectDecimalWeeklyHours()
    {
        $data = [
            'id' => 'CS101',
            'title' => 'Introduction to Computer Science',
            'units' => 3,
            'weeklyHours' => 3.5 // Valid decimal
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('subject', $body);
        $this->assertEquals(3, $body['subject']['weekly_hours']); // Should be converted to integer
    }

    public function testCreateSubjectHighValues()
    {
        $data = [
            'id' => 'RESEARCH999',
            'title' => 'Advanced Research in Quantum Computing and Machine Learning',
            'units' => 12,
            'weeklyHours' => 20
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/subjects')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('subject', $body);
        $this->assertEquals('RESEARCH999', $body['subject']['id']);
        $this->assertEquals('Advanced Research in Quantum Computing and Machine Learning', $body['subject']['title']);
        $this->assertEquals(12, $body['subject']['units']);
        $this->assertEquals(20, $body['subject']['weekly_hours']);
    }

    public function testListSubjects()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/subjects');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('subjects', $body);
        $this->assertIsArray($body['subjects']);
    }
}