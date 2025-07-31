<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\ConflictExemption;

class ExemptionController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $exemption = ConflictExemption::create($data);
        $payload = json_encode(['exemption' => $exemption]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public function list(Request $request, Response $response): Response
    {
        $exemptions = ConflictExemption::where('expires_at', '>', now())
            ->orWhereNull('expires_at')
            ->get();
        $payload = json_encode(['exemptions' => $exemptions]);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}