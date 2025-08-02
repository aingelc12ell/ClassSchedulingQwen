<?php
namespace App\Controllers;

use App\Helpers\ResponseHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Room as RoomModel;
use App\Services\ValidationService;

class RoomController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        $rules = [
            #'id' => 'required|integer|unique:App\Models\Room,id',
            'name' => 'required|string|max:50|unique:App\Models\Room,name',
            'capacity' => 'required|integer|min:1|max:1000'
        ];

        if (!$validator->validate($data, $rules)) {
            return ResponseHelper::json($response,[
                'error' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Previously deleted manual validation code:
        // $required = ['id', 'capacity'];
        // $missing = array_filter($required, fn($key) => !isset($data[$key]));
        // if (!empty($missing)) {
        //     return ResponseHelper::json($response,['error' => 'Missing fields', 'fields' => $missing], 400);
        // }
        //
        // if (!is_numeric($data['capacity']) || $data['capacity'] <= 0) {
        //     return ResponseHelper::json($response,['error' => 'Capacity must be a positive number'], 400);
        // }

        try {
            $room = RoomModel::create([
                'id' => $data['id'],
                'capacity' => (int)$data['capacity'],
            ]);

            return ResponseHelper::json($response,['room' => $room], 201);
        } catch (\Exception $e) {
            return ResponseHelper::json($response,['error' => 'Failed to create room'], 500);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $rooms = RoomModel::all();
        return ResponseHelper::json($response,['rooms' => $rooms], 200);
    }
}