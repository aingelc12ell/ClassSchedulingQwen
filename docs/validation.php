<?php

## teacher
$rules = [
    'id' => 'required|string|unique:App\Models\Teacher,id',
    'name' => 'required|string|min:2',
    'qualifiedSubjectIds' => 'required|array|json_array',
];

if (!$validator->validate($data, $rules)) {
    return $response->withJson(['errors' => $validator->getErrors()], 400);
}

## conflict exemption
$rules = [
    'type' => 'required|in:student,teacher,room',
    'entity_id' => 'required|string',
    'conflict_type' => 'required|in:schedule,capacity',
    'reason' => 'required|string|max:500',
    'expires_at' => 'date_format:Y-m-d H:i:s',
];


## time slot
$rules = [
    'label' => 'required|string|max:50',
    'start_time' => 'required|time',
    'end_time' => 'required|time',
    'is_active' => 'boolean',
];

if (!$validator->validate($data, $rules)) {
    return $response->withJson(['errors' => $validator->getErrors()], 400);
}