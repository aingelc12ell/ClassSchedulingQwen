<?php

$rules = [
    'label' => 'required|string|max:50',
    'start_time' => 'required|time',
    'end_time' => 'required|time',
    'is_active' => 'boolean',
];

if (!$validator->validate($data, $rules)) {
    return ResponseHelper::json($response,['errors' => $validator->getErrors()], 400);
}

