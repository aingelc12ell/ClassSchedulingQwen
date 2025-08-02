<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Services\JwtService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class JwtServiceTest extends TestCase
{
    private $originalJwtSecret;
    private $testSecret = 'test_secret_key_for_testing_purposes_123456789';

    protected function setUp(): void
    {
        // Store original JWT_SECRET
        $this->originalJwtSecret = $_ENV['JWT_SECRET'] ?? null;
        
        // Set test JWT secret
        $_ENV['JWT_SECRET'] = $this->testSecret;
        putenv('JWT_SECRET=' . $this->testSecret);
    }

    protected function tearDown(): void
    {
        // Restore original JWT_SECRET
        if ($this->originalJwtSecret !== null) {
            $_ENV['JWT_SECRET'] = $this->originalJwtSecret;
            putenv('JWT_SECRET=' . $this->originalJwtSecret);
        } else {
            unset($_ENV['JWT_SECRET']);
            putenv('JWT_SECRET');
        }
    }

    private function createValidToken($payload = null, $expiration = null)
    {
        if ($payload === null) {
            $payload = [
                'user_id' => 123,
                'username' => 'testuser',
                'role' => 'admin'
            ];
        }

        if ($expiration === null) {
            $expiration = time() + 3600; // 1 hour from now
        }

        $payload['exp'] = $expiration;
        $payload['iat'] = time();

        return JWT::encode($payload, $this->testSecret, 'HS256');
    }

    private function createExpiredToken()
    {
        $payload = [
            'user_id' => 123,
            'username' => 'testuser',
            'exp' => time() - 3600, // 1 hour ago
            'iat' => time() - 7200  // 2 hours ago
        ];

        return JWT::encode($payload, $this->testSecret, 'HS256');
    }

    private function createInvalidSignatureToken()
    {
        $payload = [
            'user_id' => 123,
            'username' => 'testuser',
            'exp' => time() + 3600,
            'iat' => time()
        ];

        // Use different secret to create invalid signature
        return JWT::encode($payload, 'wrong_secret_key', 'HS256');
    }

    public function testDecodeValidToken()
    {
        $payload = [
            'user_id' => 123,
            'username' => 'testuser',
            'role' => 'admin'
        ];

        $token = $this->createValidToken($payload);
        $decoded = JwtService::decode($token);

        $this->assertEquals(123, $decoded->user_id);
        $this->assertEquals('testuser', $decoded->username);
        $this->assertEquals('admin', $decoded->role);
        $this->assertObjectHasProperty('exp', $decoded);
        $this->assertObjectHasProperty('iat', $decoded);
    }

    public function testDecodeExpiredToken()
    {
        $expiredToken = $this->createExpiredToken();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token has expired');

        JwtService::decode($expiredToken);
    }

    public function testDecodeInvalidSignatureToken()
    {
        $invalidToken = $this->createInvalidSignatureToken();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid token signature');

        JwtService::decode($invalidToken);
    }

    public function testDecodeInvalidToken()
    {
        $invalidToken = 'invalid.token.format';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid token');

        JwtService::decode($invalidToken);
    }

    public function testDecodeMalformedToken()
    {
        $malformedToken = 'not.a.jwt';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid token');

        JwtService::decode($malformedToken);
    }

    public function testDecodeEmptyToken()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid token');

        JwtService::decode('');
    }

    public function testValidateTokenWithValidToken()
    {
        $validToken = $this->createValidToken();
        $result = JwtService::validateToken($validToken);

        $this->assertTrue($result, 'Valid token should return true');
    }

    public function testValidateTokenWithExpiredToken()
    {
        $expiredToken = $this->createExpiredToken();
        $result = JwtService::validateToken($expiredToken);

        $this->assertFalse($result, 'Expired token should return false');
    }

    public function testValidateTokenWithInvalidSignature()
    {
        $invalidToken = $this->createInvalidSignatureToken();
        $result = JwtService::validateToken($invalidToken);

        $this->assertFalse($result, 'Token with invalid signature should return false');
    }

    public function testValidateTokenWithInvalidToken()
    {
        $invalidToken = 'invalid.token.format';
        $result = JwtService::validateToken($invalidToken);

        $this->assertFalse($result, 'Invalid token should return false');
    }

    public function testValidateTokenWithEmptyToken()
    {
        $result = JwtService::validateToken('');

        $this->assertFalse($result, 'Empty token should return false');
    }

    public function testDecodeWithDifferentPayloads()
    {
        // Test with minimal payload
        $minimalPayload = ['user_id' => 1];
        $token1 = $this->createValidToken($minimalPayload);
        $decoded1 = JwtService::decode($token1);
        $this->assertEquals(1, $decoded1->user_id);

        // Test with complex payload
        $complexPayload = [
            'user_id' => 999,
            'username' => 'complex_user',
            'role' => 'super_admin',
            'permissions' => ['read', 'write', 'delete'],
            'metadata' => [
                'last_login' => '2024-01-01',
                'ip_address' => '192.168.1.1'
            ]
        ];
        $token2 = $this->createValidToken($complexPayload);
        $decoded2 = JwtService::decode($token2);
        
        $this->assertEquals(999, $decoded2->user_id);
        $this->assertEquals('complex_user', $decoded2->username);
        $this->assertEquals('super_admin', $decoded2->role);
        $this->assertEquals(['read', 'write', 'delete'], $decoded2->permissions);
        $this->assertEquals('2024-01-01', $decoded2->metadata->last_login);
        $this->assertEquals('192.168.1.1', $decoded2->metadata->ip_address);
    }

    public function testJwtSecretEnvironmentVariable()
    {
        // Test that JWT_SECRET is required
        $originalSecret = getenv('JWT_SECRET');
        putenv('JWT_SECRET');

        // This should cause an error when trying to decode
        $token = $this->createValidToken();
        
        $this->expectException(\Exception::class);
        JwtService::decode($token);

        // Restore the secret
        putenv('JWT_SECRET=' . $originalSecret);
    }

    public function testTokenExpirationBoundary()
    {
        // Test token that expires in 1 second
        $soonToExpireToken = $this->createValidToken(null, time() + 1);
        
        // Should be valid now
        $this->assertTrue(JwtService::validateToken($soonToExpireToken));
        
        // Wait for token to expire (in real scenario, you might mock time)
        // For testing purposes, we'll create an already expired token
        $justExpiredToken = $this->createValidToken(null, time() - 1);
        $this->assertFalse(JwtService::validateToken($justExpiredToken));
    }

    public function testDecodeReturnsStdClassObject()
    {
        $payload = ['user_id' => 123, 'test' => 'value'];
        $token = $this->createValidToken($payload);
        $decoded = JwtService::decode($token);

        $this->assertInstanceOf(\stdClass::class, $decoded);
        $this->assertObjectHasProperty('user_id', $decoded);
        $this->assertObjectHasProperty('test', $decoded);
        $this->assertObjectHasProperty('exp', $decoded);
        $this->assertObjectHasProperty('iat', $decoded);
    }

    public function testStaticMethodsExist()
    {
        // Test that the required static methods exist
        $this->assertTrue(method_exists(JwtService::class, 'decode'));
        $this->assertTrue(method_exists(JwtService::class, 'validateToken'));
        
        // Test method visibility
        $reflection = new ReflectionClass(JwtService::class);
        $decodeMethod = $reflection->getMethod('decode');
        $validateMethod = $reflection->getMethod('validateToken');
        
        $this->assertTrue($decodeMethod->isStatic());
        $this->assertTrue($decodeMethod->isPublic());
        $this->assertTrue($validateMethod->isStatic());
        $this->assertTrue($validateMethod->isPublic());
    }
}