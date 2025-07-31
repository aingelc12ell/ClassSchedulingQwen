<?php
// Public routes
$app->post('/auth/register', [App\Controllers\AuthController::class, 'register']);
$app->post('/auth/login', [App\Controllers\AuthController::class, 'login']);