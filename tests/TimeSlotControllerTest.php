<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\TimeSlotController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class TimeSlotControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new TimeSlotController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateTimeSlotSuccess()
    {
        $data = [
            'label' => 'Morning Slot 1',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'is_active' => true
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('timeSlot', $body);
        $this->assertEquals('Morning Slot 1', $body['timeSlot']['label']);
        $this->assertEquals('08:00', $body['timeSlot']['start_time']);
        $this->assertEquals('09:30', $body['timeSlot']['end_time']);
        $this->assertTrue($body['timeSlot']['is_active']);
    }

    public function testCreateTimeSlotWithDefaultActive()
    {
        $data = [
            'label' => 'Afternoon Slot',
            'start_time' => '14:00',
            'end_time' => '15:30'
            // is_active not provided, should default to true
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('timeSlot', $body);
        $this->assertEquals('Afternoon Slot', $body['timeSlot']['label']);
        $this->assertEquals('14:00', $body['timeSlot']['start_time']);
        $this->assertEquals('15:30', $body['timeSlot']['end_time']);
        $this->assertTrue($body['timeSlot']['is_active']);
    }

    public function testCreateTimeSlotInactive()
    {
        $data = [
            'label' => 'Evening Slot',
            'start_time' => '18:00',
            'end_time' => '19:30',
            'is_active' => false
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('timeSlot', $body);
        $this->assertEquals('Evening Slot', $body['timeSlot']['label']);
        $this->assertFalse($body['timeSlot']['is_active']);
    }

    public function testCreateTimeSlotMissingFields()
    {
        $data = [
            'label' => 'Incomplete Slot',
            'start_time' => '10:00'
            // Missing required field: end_time
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('end_time', $body['errors']);
    }

    public function testCreateTimeSlotMissingLabel()
    {
        $data = [
            'start_time' => '10:00',
            'end_time' => '11:30'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('label', $body['errors']);
    }

    public function testCreateTimeSlotInvalidTimeFormat()
    {
        $data = [
            'label' => 'Invalid Time Slot',
            'start_time' => '8:00', // Invalid format, should be HH:MM
            'end_time' => '09:30'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('start_time', $body['errors']);
    }

    public function testCreateTimeSlotInvalidEndTimeFormat()
    {
        $data = [
            'label' => 'Invalid End Time Slot',
            'start_time' => '08:00',
            'end_time' => '9:30' // Invalid format, should be HH:MM
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('end_time', $body['errors']);
    }

    public function testCreateTimeSlotInvalidTimeValues()
    {
        $data = [
            'label' => 'Invalid Time Values',
            'start_time' => '25:00', // Invalid hour
            'end_time' => '09:60' // Invalid minutes
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        // Should have errors for both start_time and end_time
        $this->assertTrue(isset($body['errors']['start_time']) || isset($body['errors']['end_time']));
    }

    public function testCreateTimeSlotLongLabel()
    {
        $data = [
            'label' => str_repeat('A', 51), // Too long (more than 50 characters)
            'start_time' => '08:00',
            'end_time' => '09:30'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('label', $body['errors']);
    }

    public function testCreateTimeSlotInvalidBooleanActive()
    {
        $data = [
            'label' => 'Test Slot',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'is_active' => 'invalid' // Should be boolean
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('errors', $body);
        $this->assertArrayHasKey('is_active', $body['errors']);
    }

    public function testCreateTimeSlotBooleanAsString()
    {
        $data = [
            'label' => 'String Boolean Slot',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'is_active' => '1' // Should be accepted as boolean
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('timeSlot', $body);
        $this->assertEquals('String Boolean Slot', $body['timeSlot']['label']);
    }

    public function testCreateTimeSlotEdgeTimeValues()
    {
        $data = [
            'label' => 'Edge Time Slot',
            'start_time' => '00:00', // Midnight
            'end_time' => '23:59' // End of day
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/timeslots')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('timeSlot', $body);
        $this->assertEquals('Edge Time Slot', $body['timeSlot']['label']);
        $this->assertEquals('00:00', $body['timeSlot']['start_time']);
        $this->assertEquals('23:59', $body['timeSlot']['end_time']);
    }

    public function testListTimeSlots()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/timeslots');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('timeSlots', $body);
        $this->assertIsArray($body['timeSlots']);
    }

    public function testListActiveTimeSlots()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/timeslots?active=true');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('timeSlots', $body);
        $this->assertIsArray($body['timeSlots']);
    }
}