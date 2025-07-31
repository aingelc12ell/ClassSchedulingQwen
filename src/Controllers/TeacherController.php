<?php
namespace App\Controllers;

use App\Services\ValidationService;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Teacher as TeacherModel;

class TeacherController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        /*$required = ['id', 'name', 'qualifiedSubjectIds'];
        $missing = array_filter($required, fn($key) => !isset($data[$key]));
        if (!empty($missing)) {
            return $response->withJson(['error' => 'Missing fields', 'fields' => $missing], 400);
        }

        if (!is_array($data['qualifiedSubjectIds'])) {
            return $response->withJson(['error' => 'qualifiedSubjectIds must be an array'], 400);
        }*/

        $rules = [
            'id' => 'required|string|unique:App\Models\Teacher,id',
            'name' => 'required|string|min:2',
            'qualifiedSubjectIds' => 'required|array|json_array',
        ];

        if (!$validator->validate($data, $rules)) {
            return $response->withJson(['errors' => $validator->getErrors()], 400);
        }

        try {
            $teacher = TeacherModel::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'qualified_subject_ids' => json_encode($data['qualifiedSubjectIds']),
            ]);

            return $response->withJson(['teacher' => $teacher], 201);
        } catch (\Exception $e) {
            return $response->withJson(['error' => 'Failed to create teacher'], 500);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $teachers = TeacherModel::all();
        return $response->withJson(['teachers' => $teachers], 200);
    }
}