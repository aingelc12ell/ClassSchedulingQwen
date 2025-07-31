<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Room as RoomModel;

class RoomController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $required = ['id', 'capacity'];
        $missing = array_filter($required, fn($key) => !isset($data[$key]));
        if (!empty($missing)) {
            return $response->withJson(['error' => 'Missing fields', 'fields' => $missing], 400);
        }

        if (!is_numeric($data['capacity']) || $data['capacity'] <= 0) {
            return $response->withJson(['error' => 'Capacity must be a positive number'], 400);
        }

        try {
            $room = RoomModel::create([
                'id' => $data['id'],
                'capacity' => (int)$data['capacity'],
            ]);

            return $response->withJson(['room' => $room], 201);
        } catch (\Exception $e) {
            return $response->withJson(['error' => 'Failed to create room'], 500);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $rooms = RoomModel::all();
        return $response->withJson(['rooms' => $rooms], 200);
    }
}