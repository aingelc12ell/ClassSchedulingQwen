<?php
namespace App\Controllers;

use App\Services\ValidationService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Subject as SubjectModel;

class SubjectController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        $rules = [
            'id' => 'required|string|min:1|unique:App\Models\Subject,id',
            'title' => 'required|string|min:2|max:255',
            'units' => 'required|integer|min:1',
            'weeklyHours' => 'required|numeric|min:1|subject_hours',
        ];

        if (!$validator->validate($data, $rules)) {
            return $response->withJson(['errors' => $validator->getErrors()], 400);
        }

        try {
            $subject = SubjectModel::create([
                'id' => $data['id'],
                'title' => $data['title'],
                'units' => (int)$data['units'],
                'weekly_hours' => (int)$data['weeklyHours'],
            ]);

            return $response->withJson(['subject' => $subject], 201);
        } catch (\Exception $e) {
            return $response->withJson(['error' => 'Failed to create subject'], 500);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $subjects = SubjectModel::all();
        return $response->withJson(['subjects' => $subjects], 200);
    }
}