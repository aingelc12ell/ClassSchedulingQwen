<?php


require_once __DIR__ . '/../vendor/autoload.php';

// Load DB connection and Eloquent
require_once __DIR__ . '/../config/database.php';

use DI\Bridge\Slim\Bridge;
use App\Middleware\{JsonBodyParserMiddleware, JwtAuthMiddleware};

# $app = AppFactory::create();
$app = Bridge::create();
$app->addBodyParsingMiddleware();
$app->add(new JsonBodyParserMiddleware());
$app->add(new JwtAuthMiddleware());

#$container = $app->getContainer();
$builder = new \DI\ContainerBuilder();
$builder->enableCompilation(dirname(__DIR__) . '/tmp');
$builder->writeProxiesToFile(true, dirname(__DIR__) . '/tmp/proxies');
$container = $builder->build();

$app = Bridge::create($container);

$routes = require __DIR__ . '/../src/Routes/routes.php';
$routes($app);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000') // Allow your frontend
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

$app->run();

/*require_once __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use App\Middleware\{JsonBodyParserMiddleware, JwtAuthMiddleware};

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->add(new JsonBodyParserMiddleware());
$app->add(new JwtAuthMiddleware()); // Global auth middleware


// Temporary helper for JSON responses
$responseFactory = $app->getResponseFactory();
$container = $app->getContainer();
$container->get('response')->addBodyTransformer(function ($response, $data) {
    if (is_array($data) || is_object($data)) {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }
    return $response;
});

// Register routes
$routes = require __DIR__ . '/../src/Routes/routes.php';
$routes($app);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000') // Allow your frontend
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

$app->run();*/