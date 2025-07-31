<?php
namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;

class JwtService
{
    public static function decode($token)
    {
        try {
            return JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
        } catch (ExpiredException $e) {
            throw new \Exception('Token has expired');
        } catch (SignatureInvalidException $e) {
            throw new \Exception('Invalid token signature');
        } catch (\Exception $e) {
            throw new \Exception('Invalid token');
        }
    }

    public static function validateToken(string $token): bool
    {
        try {
            self::decode($token);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}