<?php
namespace App\Helpers;

use Psr\Http\Message\ResponseInterface;

class ResponseHelper
{
    public static function json(ResponseInterface $response, $data, int $status = 200): ResponseInterface
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $response = $response->withHeader('Content-Type', 'application/json');
        $response = $response->withStatus($status);
        $response->getBody()->write($payload);
        return $response;
    }
}