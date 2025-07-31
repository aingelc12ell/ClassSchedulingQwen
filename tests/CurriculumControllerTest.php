<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use App\Controllers\CurriculumController;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class CurriculumControllerTest extends TestCase
{
    private $controller;
    private $requestFactory;
    private $responseFactory;

    protected function setUp(): void
    {
        $this->controller = new CurriculumController();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    public function testCreateCurriculumSuccess()
    {
        $data = [
            'id' => 'CURR001',
            'name' => 'Computer Science Fall 2024',
            'term' => 'Fall2024',
            'subjectIds' => ['CS101', 'CS102', 'MATH201']
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/curriculums')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(201, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('curriculum', $body);
        $this->assertEquals('CURR001', $body['curriculum']['id']);
        $this->assertEquals('Computer Science Fall 2024', $body['curriculum']['name']);
        $this->assertEquals('Fall2024', $body['curriculum']['term']);
    }

    public function testCreateCurriculumMissingFields()
    {
        $data = [
            'id' => 'CURR001',
            'name' => 'Computer Science Fall 2024'
            // Missing 'term' and 'subjectIds'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/curriculums')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('Missing fields', $body['error']);
        $this->assertArrayHasKey('fields', $body);
        $this->assertContains('term', $body['fields']);
        $this->assertContains('subjectIds', $body['fields']);
    }

    public function testCreateCurriculumInvalidSubjectIds()
    {
        $data = [
            'id' => 'CURR001',
            'name' => 'Computer Science Fall 2024',
            'term' => 'Fall2024',
            'subjectIds' => 'not-an-array'
        ];

        $request = $this->requestFactory->createServerRequest('POST', '/curriculums')
            ->withParsedBody($data);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->create($request, $response);

        $this->assertEquals(400, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('error', $body);
        $this->assertEquals('subjectIds must be an array', $body['error']);
    }

    public function testListCurriculums()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/curriculums');
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('curriculums', $body);
        $this->assertIsArray($body['curriculums']);
    }

    public function testListCurriculumsByTerm()
    {
        $request = $this->requestFactory->createServerRequest('GET', '/curriculums?term=Fall2024')
            ->withQueryParams(['term' => 'Fall2024']);
        $response = $this->responseFactory->createResponse();

        $result = $this->controller->list($request, $response);

        $this->assertEquals(200, $result->getStatusCode());
        
        $body = json_decode((string)$result->getBody(), true);
        $this->assertArrayHasKey('curriculums', $body);
        $this->assertIsArray($body['curriculums']);
    }
}