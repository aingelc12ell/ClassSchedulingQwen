<?php
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
}