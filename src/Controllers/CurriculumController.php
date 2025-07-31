<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Curriculum as CurriculumModel;

class CurriculumController
{
    public function create(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $required = ['id', 'name', 'term', 'subjectIds'];
        $missing = array_filter($required, fn($key) => !isset($data[$key]));
        if (!empty($missing)) {
            return $response->withJson(['error' => 'Missing fields', 'fields' => $missing], 400);
        }

        if (!is_array($data['subjectIds'])) {
            return $response->withJson(['error' => 'subjectIds must be an array'], 400);
        }

        try {
            $curriculum = CurriculumModel::create([
                'id' => $data['id'],
                'name' => $data['name'],
                'term' => $data['term'],
                'subject_ids' => json_encode($data['subjectIds']),
            ]);

            return $response->withJson(['curriculum' => $curriculum], 201);
        } catch (\Exception $e) {
            return $response->withJson(['error' => 'Failed to create curriculum'], 500);
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
        return $response->withJson(['curriculums' => $curriculums], 200);
    }
}