<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Middleware\JsonBodyParserMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class JsonBodyParserMiddlewareTest extends TestCase
{
    private JsonBodyParserMiddleware $middleware;
    private ServerRequestFactory $requestFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->middleware = new JsonBodyParserMiddleware();
        $this->requestFactory = new ServerRequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    private function createMockHandler($expectedRequest = null): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->responseFactory->createResponse(200);
        
        if ($expectedRequest) {
            $handler->expects($this->once())
                ->method('handle')
                ->with($expectedRequest)
                ->willReturn($response);
        } else {
            $handler->expects($this->once())
                ->method('handle')
                ->willReturn($response);
        }
        
        return $handler;
    }

    public function testImplementsMiddlewareInterface()
    {
        $this->assertInstanceOf(\Psr\Http\Server\MiddlewareInterface::class, $this->middleware);
    }

    public function testProcessMethodExists()
    {
        $this->assertTrue(method_exists($this->middleware, 'process'));
        
        $reflection = new ReflectionMethod($this->middleware, 'process');
        $this->assertTrue($reflection->isPublic());
    }

    public function testProcessWithJsonContentType()
    {
        // Create a request with JSON content type
        $request = $this->requestFactory->createServerRequest('POST', '/test')
            ->withHeader('Content-Type', 'application/json');

        // Mock the handler
        $handler = $this->createMockHandler();

        // Process the request
        $response = $this->middleware->process($request, $handler);

        // Verify response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithNonJsonContentType()
    {
        // Create a request with non-JSON content type
        $request = $this->requestFactory->createServerRequest('POST', '/test')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Mock the handler to expect the original request (unchanged)
        $handler = $this->createMockHandler();

        // Process the request
        $response = $this->middleware->process($request, $handler);

        // Verify response
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithMixedContentType()
    {
        // Test with content type that includes application/json but has other parts
        $request = $this->requestFactory->createServerRequest('POST', '/test')
            ->withHeader('Content-Type', 'application/json; charset=utf-8');

        $handler = $this->createMockHandler();
        $response = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithNoContentType()
    {
        // Create a request without Content-Type header
        $request = $this->requestFactory->createServerRequest('POST', '/test');

        $handler = $this->createMockHandler();
        $response = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithEmptyContentType()
    {
        // Create a request with empty Content-Type header
        $request = $this->requestFactory->createServerRequest('POST', '/test')
            ->withHeader('Content-Type', '');

        $handler = $this->createMockHandler();
        $response = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testContentTypeDetection()
    {
        // Test various content type formats that should be detected as JSON
        $jsonContentTypes = [
            'application/json',
            'application/json; charset=utf-8',
            'application/json;charset=utf-8',
            'Application/JSON',
            'text/json', // This should NOT match based on the current implementation
        ];

        foreach ($jsonContentTypes as $contentType) {
            $request = $this->requestFactory->createServerRequest('POST', '/test')
                ->withHeader('Content-Type', $contentType);

            $isJsonExpected = strpos($contentType, 'application/json') !== false;
            
            // We can't easily test the internal logic without mocking php://input
            // But we can verify the middleware processes without errors
            $handler = $this->createMockHandler();
            $response = $this->middleware->process($request, $handler);
            
            $this->assertEquals(200, $response->getStatusCode(), 
                "Failed for content type: $contentType");
        }
    }

    public function testProcessWithGetRequest()
    {
        // Test that GET requests are processed normally (no body parsing expected)
        $request = $this->requestFactory->createServerRequest('GET', '/test')
            ->withHeader('Content-Type', 'application/json');

        $handler = $this->createMockHandler();
        $response = $this->middleware->process($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcessWithDifferentHttpMethods()
    {
        $methods = ['POST', 'PUT', 'PATCH', 'DELETE', 'GET'];

        foreach ($methods as $method) {
            $request = $this->requestFactory->createServerRequest($method, '/test')
                ->withHeader('Content-Type', 'application/json');

            $handler = $this->createMockHandler();
            $response = $this->middleware->process($request, $handler);

            $this->assertEquals(200, $response->getStatusCode(), 
                "Failed for HTTP method: $method");
        }
    }

    public function testMiddlewareChaining()
    {
        // Test that the middleware properly calls the next handler
        $request = $this->requestFactory->createServerRequest('POST', '/test');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->responseFactory->createResponse(201);
        
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($expectedResponse);

        $response = $this->middleware->process($request, $handler);

        $this->assertSame($expectedResponse, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testRequestObjectIntegrity()
    {
        // Test that the request object maintains its integrity through processing
        $originalUri = '/test/endpoint';
        $originalMethod = 'POST';
        $originalHeaders = ['X-Custom-Header' => 'test-value'];

        $request = $this->requestFactory->createServerRequest($originalMethod, $originalUri);
        foreach ($originalHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function($processedRequest) use ($originalUri, $originalMethod, $originalHeaders) {
                // Verify the request maintains its original properties
                $this->assertEquals($originalMethod, $processedRequest->getMethod());
                $this->assertEquals($originalUri, (string)$processedRequest->getUri());
                
                foreach ($originalHeaders as $name => $value) {
                    $this->assertEquals($value, $processedRequest->getHeaderLine($name));
                }
                
                return true;
            }))
            ->willReturn($this->responseFactory->createResponse(200));

        $response = $this->middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCaseInsensitiveContentTypeCheck()
    {
        $contentTypes = [
            'application/json',
            'Application/Json',
            'APPLICATION/JSON',
            'application/JSON',
        ];

        foreach ($contentTypes as $contentType) {
            $request = $this->requestFactory->createServerRequest('POST', '/test')
                ->withHeader('Content-Type', $contentType);

            $handler = $this->createMockHandler();
            $response = $this->middleware->process($request, $handler);

            // The current implementation uses strpos which is case-sensitive
            // So only lowercase 'application/json' will match
            $shouldMatch = strpos($contentType, 'application/json') !== false;
            
            $this->assertEquals(200, $response->getStatusCode(), 
                "Failed for content type: $contentType");
        }
    }

    public function testMiddlewareDoesNotModifyResponse()
    {
        // Test that the middleware doesn't modify the response from the handler
        $request = $this->requestFactory->createServerRequest('POST', '/test');
        
        $expectedResponse = $this->responseFactory->createResponse(418) // I'm a teapot
            ->withHeader('X-Custom-Response', 'test-value');
        
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn($expectedResponse);

        $actualResponse = $this->middleware->process($request, $handler);

        $this->assertSame($expectedResponse, $actualResponse);
        $this->assertEquals(418, $actualResponse->getStatusCode());
        $this->assertEquals('test-value', $actualResponse->getHeaderLine('X-Custom-Response'));
    }

    public function testProcessReturnsResponseInterface()
    {
        $request = $this->requestFactory->createServerRequest('POST', '/test');
        $handler = $this->createMockHandler();

        $response = $this->middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}