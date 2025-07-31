<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\User;

class AuthController
{
    public function login(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::where('username', $username)->first();

        if (!$user || !$user->verifyPassword($password)) {
            return $response->withJson([
                'error' => 'Invalid username or password'
            ], 401);
        }

        $token = $user->createToken();

        return $response->withJson([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'role' => $user->role,
                'email' => $user->email
            ]
        ], 200);
    }

    public function profile(Request $request, Response $response): Response
    {
        $token = $request->getAttribute('token');
        $user = User::getUserFromToken($token);

        if (!$user) {
            return $response->withJson(['error' => 'User not found'], 404);
        }

        return $response->withJson(['user' => $user], 200);
    }

    public function logout(Request $request, Response $response): Response
    {
        // JWT is stateless; logout = client-side token discard
        // Optionally implement a token blacklist (DB or Redis) if needed
        return $response->withJson(['message' => 'Logged out. Discard token.'], 200);
    }
}