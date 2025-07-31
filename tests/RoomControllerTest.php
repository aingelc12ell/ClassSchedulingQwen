<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\RoomController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class RoomControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new RoomController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateRoomSuccess()
    {
        $data = [
            'id' => 'R101',
            'capacity' => 30
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('room', $body);
        $this->assertEquals('R101', $body['room']['id']);
        $this->assertEquals(30, $body['room']['capacity']);
    }

    public function testCreateRoomMissingFields()
    {
        $data = [
            'id' => 'R101'
            // Missing required field: capacity
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing fields', $body['error']);
        $this->assertArrayHasKey('fields', $body);
        $this->assertContains('capacity', $body['fields']);
    }

    public function testCreateRoomMissingId()
    {
        $data = [
            'capacity' => 30
            // Missing required field: id
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
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

    public function testCreateRoomInvalidCapacityNonNumeric()
    {
        $data = [
            'id' => 'R101',
            'capacity' => 'not-a-number'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Capacity must be a positive number', $body['error']);
    }

    public function testCreateRoomInvalidCapacityZero()
    {
        $data = [
            'id' => 'R101',
            'capacity' => 0
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Capacity must be a positive number', $body['error']);
    }

    public function testCreateRoomInvalidCapacityNegative()
    {
        $data = [
            'id' => 'R101',
            'capacity' => -5
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Capacity must be a positive number', $body['error']);
    }

    public function testCreateRoomCapacityAsString()
    {
        $data = [
            'id' => 'R102',
            'capacity' => '25' // String representation of number
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('room', $body);
        $this->assertEquals('R102', $body['room']['id']);
        $this->assertEquals(25, $body['room']['capacity']);
        $this->assertIsInt($body['room']['capacity']); // Should be converted to integer
    }

    public function testCreateRoomLargeCapacity()
    {
        $data = [
            'id' => 'AUDITORIUM',
            'capacity' => 500
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/rooms')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('room', $body);
        $this->assertEquals('AUDITORIUM', $body['room']['id']);
        $this->assertEquals(500, $body['room']['capacity']);
    }

    public function testListRooms()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/rooms');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('rooms', $body);
        $this->assertIsArray($body['rooms']);
    }
}