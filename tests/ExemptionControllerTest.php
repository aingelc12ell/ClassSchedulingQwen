<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\ExemptionController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class ExemptionControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new ExemptionController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateExemptionSuccess()
    {
        $data = [
            'type' => 'student',
            'entity_id' => 'S001',
            'conflict_type' => 'schedule',
            'reason' => 'Student has medical appointment during this time slot',
            'expires_at' => '2024-12-31 23:59:59'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('exemption', $body);
        $this->assertEquals('student', $body['exemption']['type']);
        $this->assertEquals('S001', $body['exemption']['entity_id']);
        $this->assertEquals('schedule', $body['exemption']['conflict_type']);
        $this->assertEquals('Student has medical appointment during this time slot', $body['exemption']['reason']);
    }

    public function testCreateExemptionWithoutExpiresAt()
    {
        $data = [
            'type' => 'teacher',
            'entity_id' => 'T001',
            'conflict_type' => 'capacity',
            'reason' => 'Teacher has permanent scheduling conflict'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('exemption', $body);
        $this->assertEquals('teacher', $body['exemption']['type']);
        $this->assertEquals('T001', $body['exemption']['entity_id']);
        $this->assertEquals('capacity', $body['exemption']['conflict_type']);
        $this->assertEquals('Teacher has permanent scheduling conflict', $body['exemption']['reason']);
    }

    public function testCreateExemptionMissingFields()
    {
        $data = [
            'type' => 'student',
            'entity_id' => 'S001'
            // Missing required fields: conflict_type, reason
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing fields', $body['error']);
        $this->assertArrayHasKey('fields', $body);
        $this->assertContains('conflict_type', $body['fields']);
        $this->assertContains('reason', $body['fields']);
    }

    public function testCreateExemptionInvalidType()
    {
        $data = [
            'type' => 'invalid_type',
            'entity_id' => 'S001',
            'conflict_type' => 'schedule',
            'reason' => 'Some reason'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Type must be one of: student, teacher, room', $body['error']);
    }

    public function testCreateExemptionInvalidConflictType()
    {
        $data = [
            'type' => 'student',
            'entity_id' => 'S001',
            'conflict_type' => 'invalid_conflict',
            'reason' => 'Some reason'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Conflict type must be one of: schedule, capacity', $body['error']);
    }

    public function testCreateExemptionInvalidExpiresAt()
    {
        $data = [
            'type' => 'room',
            'entity_id' => 'R001',
            'conflict_type' => 'capacity',
            'reason' => 'Room under maintenance',
            'expires_at' => 'invalid-date-format'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('expires_at must be a valid datetime', $body['error']);
    }

    public function testCreateExemptionAllValidTypes()
    {
        $validTypes = ['student', 'teacher', 'room'];
        
        foreach ($validTypes as $type) {
            $data = [
                'type' => $type,
                'entity_id' => strtoupper($type[0]) . '001',
                'conflict_type' => 'schedule',
                'reason' => "Test exemption for {$type}"
            ];

            $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
                ->withParsedBody($data);
            $response = $this->responseFactory->createResponse();

            $result = $this->controller->create($request, $response);

            $this->assertEquals(201, $result->getStatusCode());
            
            $body = json_decode((string)$result->getBody(), true);
            $this->assertArrayHasKey('exemption', $body);
            $this->assertEquals($type, $body['exemption']['type']);
        }
    }

    public function testCreateExemptionAllValidConflictTypes()
    {
        $validConflictTypes = ['schedule', 'capacity'];
        
        foreach ($validConflictTypes as $conflictType) {
            $data = [
                'type' => 'student',
                'entity_id' => 'S001',
                'conflict_type' => $conflictType,
                'reason' => "Test exemption for {$conflictType} conflict"
            ];

            $request = $this->requestFactory->createServerRequest('POST', '/exemptions')
                ->withParsedBody($data);
            $response = $this->responseFactory->createResponse();

            $result = $this->controller->create($request, $response);

            $this->assertEquals(201, $result->getStatusCode());
            
            $body = json_decode((string)$result->getBody(), true);
            $this->assertArrayHasKey('exemption', $body);
            $this->assertEquals($conflictType, $body['exemption']['conflict_type']);
        }
    }

    public function testListExemptions()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/exemptions');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('exemptions', $body);
        $this->assertIsArray($body['exemptions']);
    }
}