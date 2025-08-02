<?php


namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * Paths that don't require authentication
     */
    private array $publicPaths = [
        '/login',
        '/register',
    ];

    /**
     * Process the middleware
     */
    public function process(Request $request, Handler $handler): Response
    {
        $path = $request->getUri()->getPath();

        // Skip authentication for public routes
        foreach($this->publicPaths as $skip){
            if(strpos($path, $skip) !== false){
                return $handler->handle($request);
            }
        }

        // Extract and validate JWT
        $token = $this->getTokenFromRequest($request);
        if(!$token){
            return $this->unauthorizedResponse($response, 'Token not provided');
        }

        try{
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $request = $request->withAttribute('token', $token);
            $request = $request->withAttribute('user', $decoded);
        } catch(\Firebase\JWT\ExpiredException $e){
            return $this->unauthorizedResponse($response, 'Token has expired');
        } catch(\Firebase\JWT\SignatureInvalidException $e){
            return $this->unauthorizedResponse($response, 'Invalid token signature');
        } catch(\Exception $e){
            return $this->unauthorizedResponse($response, 'Invalid token: ' . $e->getMessage());
        }

        return $handler->handle($request);
    }

    /**
     * Protect a route that requires admin role
     * Call this in your controller or route
     *
     * @param Request $request
     * @return Response|null Returns Response if unauthorized, null if authorized
     */
    public function requireAdmin(Request $request): ?Response
    {
        $user = $request->getAttribute('user');
        if(!$user){
            return $this->unauthorizedResponse(new class implements Response{
                public function withStatus($code, $reasonPhrase = ''): Response
                {
                    return $this;
                }

                public function getHeaders(): array
                {
                    return ['Content-Type' => ['application/json']];
                }

                public function getBody()
                {
                    $stream = fopen('php://temp', 'r+');
                    fwrite($stream, json_encode(['error' => 'User not authenticated']));
                    rewind($stream);
                    return $stream;
                }

                public function withHeader($name, $value): Response
                {
                    return $this;
                }

                public function withoutHeader($name): Response
                {
                    return $this;
                }

                public function getStatusCode(): int
                {
                    return 401;
                }

                public function getReasonPhrase(): string
                {
                    return 'Unauthorized';
                }

                public function getProtocolVersion(): string
                {
                    return '1.1';
                }

                public function withProtocolVersion($version): Response
                {
                    return $this;
                }

                public function withAddedHeader($name, $value): Response
                {
                    return $this;
                }

                public function getHeader($name): array
                {
                    return [];
                }

                public function getHeaderLine($name): string
                {
                    return '';
                }

                public function hasHeader($name): bool
                {
                    return false;
                }
            }, 'User not authenticated');
        }

        if($user->role !== 'admin'){
            return $this->forbiddenResponse($response, 'Admin access required');
        }

        return null; // Authorized
    }

    /**
     * Extract JWT from Authorization header
     */
    private function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if(preg_match('/Bearer\s+(.+)$/i', $header, $matches)){
            return $matches[1];
        }
        return null;
    }

    /**
     * Return a 401 Unauthorized response
     */
    private function unauthorizedResponse(Response $response, string $message): Response
    {
        $payload = json_encode(['error' => $message]);
        $response = $response->withStatus(401);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Return a 403 Forbidden response
     */
    private function forbiddenResponse(Response $response, string $message): Response
    {
        $payload = json_encode(['error' => $message]);
        $response = $response->withStatus(403);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}

/*namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthMiddleware implements MiddlewareInterface
{
    private array $skipPaths = ['/login', '/register'];

    public function process(Request $request, Handler $handler): Response
    {
        $path = $request->getUri()->getPath();
        foreach($this->skipPaths as $skip){
            if(strpos($path, $skip) !== false) return $handler->handle($request);
        }

        $token = $this->getTokenFromRequest($request);
        if(!$token) return $this->unauthorized($response, 'Token not provided');

        try{
            JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $request = $request->withAttribute('token', $token);
        } catch(\Exception $e){
            return $this->unauthorized($response, 'Invalid or expired token');
        }

        return $handler->handle($request);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->getHeaderLine('Authorization');
        if(preg_match('/Bearer\s(\S+)/', $header, $matches)) return $matches[1];
        return null;
    }

    private function unauthorized(Response $response, string $message): Response
    {
        $payload = json_encode(['error' => $message]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}*/

/*
namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Message\ResponseInterface as Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthMiddleware implements MiddlewareInterface
{
    private $skipPaths = [
        '/login',
        '/register',
    ];

    public function process(Request $request, Handler $handler): Response
    {
        $path = $request->getUri()->getPath();

        // Skip auth for public routes
        foreach ($this->skipPaths as $skip) {
            if (strpos($path, $skip) !== false) {
                return $handler->handle($request);
            }
        }

        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return $this->unauthorizedResponse($response, 'Token not provided');
        }

        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
            $request = $request->withAttribute('token', $token);
            $request = $request->withAttribute('user', $decoded);
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($response, 'Invalid or expired token: ' . $e->getMessage());
        }

        return $handler->handle($request);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function unauthorizedResponse(Response $response, string $message): Response
    {
        $payload = json_encode(['error' => $message]);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}*/