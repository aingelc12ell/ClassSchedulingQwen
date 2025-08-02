<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Exception\HttpNotFoundException;

// Custom Not Found Handler
return function (Request $request, RequestHandler $handler): Response {
    try {
        return $handler->handle($request);
    } catch (HttpNotFoundException $exception) {
        $response = new \Slim\Psr7\Response();
        $payload = json_encode([
            'error' => 'Route not found',
            'path' => $request->getUri()->getPath(),
            'method' => $request->getMethod()
        ], JSON_PRETTY_PRINT);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
};
// Custom Not Found Handler (Clean & Official)
/*return function (Request $request): callable {
    return function (Request $request, Response $response) {
        $payload = json_encode([
            'error' => 'Route not found',
            'path' => $request->getUri()->getPath(),
            'method' => $request->getMethod()
        ], JSON_PRETTY_PRINT);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    };
};*/