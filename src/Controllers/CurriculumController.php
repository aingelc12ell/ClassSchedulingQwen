<?php
namespace App\Controllers;

use App\Helpers\ResponseHelper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Curriculum as CurriculumModel;
use App\Services\ValidationService;

class CurriculumController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $validator = new ValidationService();

        $rules = [
            'id' => 'required|integer|unique:App\Models\Curriculum,id',
            'code' => 'required|string|unique:App\Models\Curriculum,code',
            'name' => 'required|string|min:2|max:100',
            'term' => 'required|string|max:20',
            'subjectIds' => 'required|json_array'
        ];

        if (!$validator->validate($data, $rules)) {
            return ResponseHelper::json($response,[
                'error' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 400);
        }

        // Previously deleted manual validation code:
        // $required = ['id', 'name', 'term', 'subjectIds'];
        // $missing = array_filter($required, fn($key) => !isset($data[$key]));
        // if (!empty($missing)) {
        //     return ResponseHelper::json($response,['error' => 'Missing fields', 'fields' => $missing], 400);
        // }
        //
        // if (!is_array($data['subjectIds'])) {
        //     return ResponseHelper::json($response,['error' => 'subjectIds must be an array'], 400);
        // }

        try {
            $curriculum = CurriculumModel::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'term' => $data['term'],
                'subject_ids' => json_encode($data['subjectIds']),
            ]);

            return ResponseHelper::json($response,['curriculum' => $curriculum], 201);
        } catch (\Exception $e) {
            return ResponseHelper::json($response,['error' => 'Failed to create curriculum'], 500);
        }
    }

    public function list(Request $request, Response $response): Response
    {
        $term = $request->getQueryParam('term');
        $query = CurriculumModel::query();
        if ($term) {
            $query->where('term', $term);
        }
        $curriculums = $query->get();
        return ResponseHelper::json($response,['curriculums' => $curriculums], 200);
    }
}