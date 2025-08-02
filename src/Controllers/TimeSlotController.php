<?php
namespace App\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\ValidationService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\TimeSlot as TimeSlotModel;

class TimeSlotController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        $rules = [
            'label' => 'required|string|max:50',
            'start_time' => 'required|time',
            'end_time' => 'required|time',
            'is_active' => 'boolean',
        ];

        if (!$validator->validate($data, $rules)) {
            return ResponseHelper::json($response,['errors' => $validator->getErrors()], 400);
        }

        /*$required = ['label', 'start_time', 'end_time'];
        $missing = array_filter($required, fn($key) => !isset($data[$key]));
        if (!empty($missing)) {
            return ResponseHelper::json($response,['error' => 'Missing fields', 'fields' => $missing], 400);
        }

        // Validate time format (simplified)
        $timePattern = '/^\d{2}:\d{2}$/';
        if (!preg_match($timePattern, $data['start_time']) || !preg_match($timePattern, $data['end_time'])) {
            return ResponseHelper::json($response,['error' => 'Invalid time format, use HH:MM'], 400);
        }*/

        try {
            $timeSlot = TimeSlotModel::create([
                'label' => $data['label'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            return ResponseHelper::json($response,['timeSlot' => $timeSlot], 201);
        } catch (\Exception $e) {
            return ResponseHelper::json($response,['error' => 'Failed to create time slot'], 500);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $onlyActive = $request->getQueryParam('active') === 'true';
        $query = TimeSlotModel::query();
        if ($onlyActive) {
            $query->where('is_active', true);
        }
        $timeSlots = $query->get();
        return ResponseHelper::json($response,['timeSlots' => $timeSlots], 200);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $timeSlot = TimeSlotModel::find($args['id']);
        if (!$timeSlot) {
            return ResponseHelper::json($response,['error' => 'Time slot not found'], 404);
        }

        $data = $request->getParsedBody();
        $timeSlot->fill([
            'label' => $data['label'] ?? $timeSlot->label,
            'start_time' => $data['start_time'] ?? $timeSlot->start_time,
            'end_time' => $data['end_time'] ?? $timeSlot->end_time,
            'is_active' => $data['is_active'] ?? $timeSlot->is_active,
        ]);
        $timeSlot->save();

        return ResponseHelper::json($response,['timeSlot' => $timeSlot], 200);
    }
}