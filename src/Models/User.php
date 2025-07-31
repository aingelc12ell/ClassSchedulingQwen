<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['username', 'email', 'password', 'role'];
    protected $hidden = ['password']; // Never expose in JSON

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function createToken(): string
    {
        $issuedAt = time();
        $expire = $issuedAt + (int)getenv('JWT_EXPIRATION');

        $token = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'uid' => $this->id,
            'username' => $this->username,
            'role' => $this->role,
        ];

        return JWT::encode($token, getenv('JWT_SECRET'), 'HS256');
    }

    public static function getUserFromToken(string $token): ?self
    {
        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
            return static::find($decoded->uid);
        } catch (\Exception $e) {
            return null;
        }
    }
}