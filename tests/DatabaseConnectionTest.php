<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

class DatabaseConnectionTest extends TestCase
{
    private $originalEnv;

    protected function setUp(): void
    {
        // Store original environment variables
        $this->originalEnv = [
            'DB_HOST' => $_ENV['DB_HOST'] ?? null,
            'DB_PORT' => $_ENV['DB_PORT'] ?? null,
            'DB_DATABASE' => $_ENV['DB_DATABASE'] ?? null,
            'DB_USERNAME' => $_ENV['DB_USERNAME'] ?? null,
            'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? null,
        ];
    }

    protected function tearDown(): void
    {
        // Restore original environment variables
        foreach ($this->originalEnv as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
    }

    public function testDatabaseConfigurationLoading()
    {
        // Test that environment variables are loaded correctly
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        $this->assertNotEmpty($_ENV['DB_HOST']);
        $this->assertNotEmpty($_ENV['DB_DATABASE']);
        $this->assertNotEmpty($_ENV['DB_USERNAME']);
    }

    public function testDatabaseConnectionWithValidCredentials()
    {
        // Set up test environment variables
        $_ENV['DB_HOST'] = 'localhost';
        $_ENV['DB_PORT'] = '3306';
        $_ENV['DB_DATABASE'] = 'test_db';
        $_ENV['DB_USERNAME'] = 'test_user';
        $_ENV['DB_PASSWORD'] = 'test_pass';

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'],
            'port'      => $_ENV['DB_PORT'],
            'database'  => $_ENV['DB_DATABASE'],
            'username'  => $_ENV['DB_USERNAME'],
            'password'  => $_ENV['DB_PASSWORD'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => 'InnoDB',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // Test that the connection configuration is set correctly
        $connection = $capsule->getConnection();
        $config = $connection->getConfig();

        $this->assertEquals('mysql', $config['driver']);
        $this->assertEquals('localhost', $config['host']);
        $this->assertEquals('3306', $config['port']);
        $this->assertEquals('test_db', $config['database']);
        $this->assertEquals('test_user', $config['username']);
        $this->assertEquals('utf8mb4', $config['charset']);
        $this->assertEquals('utf8mb4_unicode_ci', $config['collation']);
        $this->assertEquals('InnoDB', $config['engine']);
        $this->assertTrue($config['strict']);
    }

    public function testDatabaseConnectionWithFallbackDefaults()
    {
        // Clear environment variables to test fallbacks
        unset($_ENV['DB_HOST']);
        unset($_ENV['DB_PORT']);
        unset($_ENV['DB_DATABASE']);
        unset($_ENV['DB_USERNAME']);
        unset($_ENV['DB_PASSWORD']);

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => $_ENV['DB_HOST'] ?? 'localhost',
            'port'      => $_ENV['DB_PORT'] ?? 3306,
            'database'  => $_ENV['DB_DATABASE'] ?? 'scheduling_db',
            'username'  => $_ENV['DB_USERNAME'] ?? 'root',
            'password'  => $_ENV['DB_PASSWORD'] ?? '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => 'InnoDB',
        ]);

        $connection = $capsule->getConnection();
        $config = $connection->getConfig();

        // Test fallback values
        $this->assertEquals('localhost', $config['host']);
        $this->assertEquals(3306, $config['port']);
        $this->assertEquals('scheduling_db', $config['database']);
        $this->assertEquals('root', $config['username']);
        $this->assertEquals('', $config['password']);
    }

    public function testDatabaseConnectionConfigurationStructure()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => 3306,
            'database'  => 'test_db',
            'username'  => 'test_user',
            'password'  => 'test_pass',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => 'InnoDB',
        ]);

        $connection = $capsule->getConnection();
        $config = $connection->getConfig();

        // Test that all required configuration keys exist
        $requiredKeys = ['driver', 'host', 'port', 'database', 'username', 'password', 'charset', 'collation', 'prefix', 'strict', 'engine'];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $config, "Configuration missing key: {$key}");
        }
    }

    /*public function testEnvironmentVariableTypes()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        // Test that DB_PORT is numeric when provided
        if (isset($_ENV['DB_PORT'])) {
            $this->assertTrue(is_numeric($_ENV['DB_PORT']), 'DB_PORT should be numeric');
        }

        // Test that required string variables are not empty
        $requiredStringVars = ['DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($requiredStringVars as $var) {
            if (isset($_ENV[$var])) {
                $this->assertNotEmpty($_ENV[$var], "{$var} should not be empty");
                $this->assertIsString($_ENV[$var], "{$var} should be a string");
            }
        }
    }*/

    public function testDatabaseConnectionInstance()
    {
        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => 3306,
            'database'  => 'test_db',
            'username'  => 'test_user',
            'password'  => 'test_pass',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => 'InnoDB',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // Test that we can get a connection instance
        $connection = $capsule->getConnection();
        $this->assertInstanceOf(\Illuminate\Database\Connection::class, $connection);

        // Test that the connection name is 'default'
        $this->assertEquals('default', $connection->getName());
    }
}