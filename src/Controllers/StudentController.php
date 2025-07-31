<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Student as StudentModel;
use App\Services\ValidationService;

class StudentController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        $rules = [
            'id' => 'required|string|max:50|unique:App\Models\Student,id',
            'name' => 'required|string|min:2|max:100',
            'curriculumId' => 'required|integer|exists_in:App\Models\Curriculum,id',
            'enrollmentCount' => 'integer|min:1|max:10'
        ];

        if (!$validator->validate($data, $rules)) {
            return $response->withJson([
                'error' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Previously deleted manual validation code:
        // $required = ['id', 'name', 'curriculumId'];
        // $missing = array_filter($required, fn($key) => !isset($data[$key]));
        // if (!empty($missing)) {
        //     $payload = ['error' => 'Missing fields', 'fields' => $missing];
        //     return $response->withJson($payload, 400);
        // }

        try {
            $student = StudentModel::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'curriculumId' => $data['curriculumId'],
                'enrollmentCount' => $data['enrollmentCount'] ?? 1,
            ]);

            return $response->withJson(['student' => $student], 201);
        } catch (\Exception $e) {
            return $response->withJson(['error' => 'Failed to create student'], 500);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $term = $request->getQueryParam('term');
        $query = StudentModel::query();

        if ($term) {
            $query->whereHas('curriculum', fn($q) => $q->where('term', $term));
        }

        $students = $query->get();
        return $response->withJson(['students' => $students], 200);
    }
}