<?php
namespace App\Controllers;

use App\Helpers\ResponseHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\ConflictExemption;
use App\Services\ValidationService;

class ExemptionController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        $rules = [
            'type' => 'required|string|in:student,teacher,room',
            'entity_id' => 'required|string|max:50',
            'conflict_type' => 'required|string|in:schedule,capacity',
            'reason' => 'required|string|min:5|max:500',
            'expires_at' => 'date_format:Y-m-d H:i:s'
        ];

        if (!$validator->validate($data, $rules)) {
            return ResponseHelper::json($response,[
                'error' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Previously deleted manual validation code:
        // $required = ['type', 'entity_id', 'conflict_type', 'reason'];
        // $missing = array_filter($required, fn($key) => !isset($data[$key]));
        // if (!empty($missing)) {
        //     return ResponseHelper::json($response,['error' => 'Missing fields', 'fields' => $missing], 400);
        // }
        //
        // // Validate type field
        // $validTypes = ['student', 'teacher', 'room'];
        // if (!in_array($data['type'], $validTypes)) {
        //     return ResponseHelper::json($response,['error' => 'Type must be one of: ' . implode(', ', $validTypes)], 400);
        // }
        //
        // // Validate conflict_type field
        // $validConflictTypes = ['schedule', 'capacity'];
        // if (!in_array($data['conflict_type'], $validConflictTypes)) {
        //     return ResponseHelper::json($response,['error' => 'Conflict type must be one of: ' . implode(', ', $validConflictTypes)], 400);
        // }
        //
        // // Validate expires_at if provided
        // if (isset($data['expires_at']) && $data['expires_at'] !== null) {
        //     $expiresAt = strtotime($data['expires_at']);
        //     if ($expiresAt === false) {
        //         return ResponseHelper::json($response,['error' => 'expires_at must be a valid datetime'], 400);
        //     }
        // }

        try {
            $exemption = ConflictExemption::create([
                'type' => $data['type'],
                'entity_id' => $data['entity_id'],
                'conflict_type' => $data['conflict_type'],
                'reason' => $data['reason'],
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            return ResponseHelper::json($response,['exemption' => $exemption], 201);
        } catch (\Exception $e) {
            return ResponseHelper::json($response,['error' => 'Failed to create exemption'], 500);
        }
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