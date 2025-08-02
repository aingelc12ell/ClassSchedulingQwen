<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;

class DirectoryOperationsTest extends TestCase
{
    private $testDir;
    private $originalEnv;

    protected function setUp(): void
    {
        // Store original environment variables
        $this->originalEnv = [
            'CONTAINER_DIR' => $_ENV['CONTAINER_DIR'] ?? null,
        ];

        // Create a temporary test directory
        $this->testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'scheduling_test_' . uniqid();
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up test directory
        if (is_dir($this->testDir)) {
            $this->removeDirectory($this->testDir);
        }

        // Restore original environment variables
        foreach ($this->originalEnv as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
    }

    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    public function testContainerDirectoryEnvironmentVariable()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        // Test that CONTAINER_DIR is set in environment
        $this->assertArrayHasKey('CONTAINER_DIR', $_ENV, 'CONTAINER_DIR should be defined in environment');
        $this->assertNotEmpty($_ENV['CONTAINER_DIR'], 'CONTAINER_DIR should not be empty');
    }

    public function testContainerDirectoryPath()
    {
        $_ENV['CONTAINER_DIR'] = './tmp';
        
        $containerDir = $_ENV['CONTAINER_DIR'];
        
        // Test that the path is a string
        $this->assertIsString($containerDir, 'Container directory path should be a string');
        
        // Test that the path is not empty
        $this->assertNotEmpty($containerDir, 'Container directory path should not be empty');
        
        // Test that the path format is valid (relative or absolute)
        $this->assertTrue(
            str_starts_with($containerDir, './') || 
            str_starts_with($containerDir, '../') || 
            str_starts_with($containerDir, '/') ||
            (strlen($containerDir) > 1 && $containerDir[1] === ':'), // Windows absolute path
            'Container directory should be a valid path format'
        );
    }

    public function testDirectoryCreation()
    {
        $testPath = $this->testDir . DIRECTORY_SEPARATOR . 'new_directory';
        
        // Test directory creation
        $this->assertFalse(is_dir($testPath), 'Directory should not exist initially');
        
        $result = mkdir($testPath, 0755, true);
        $this->assertTrue($result, 'Directory creation should succeed');
        $this->assertTrue(is_dir($testPath), 'Directory should exist after creation');
        
        // Test directory permissions (Unix-like systems)
        if (DIRECTORY_SEPARATOR === '/') {
            $permissions = fileperms($testPath) & 0777;
            $this->assertEquals(0755, $permissions, 'Directory should have correct permissions');
        }
    }

    public function testDirectoryValidation()
    {
        // Test existing directory validation
        $this->assertTrue(is_dir($this->testDir), 'Test directory should be valid');
        $this->assertTrue(is_readable($this->testDir), 'Test directory should be readable');
        $this->assertTrue(is_writable($this->testDir), 'Test directory should be writable');
        
        // Test non-existent directory validation
        $nonExistentDir = $this->testDir . DIRECTORY_SEPARATOR . 'non_existent';
        $this->assertFalse(is_dir($nonExistentDir), 'Non-existent directory should not be valid');
        $this->assertFalse(is_readable($nonExistentDir), 'Non-existent directory should not be readable');
        $this->assertFalse(is_writable($nonExistentDir), 'Non-existent directory should not be writable');
    }

    public function testFileOperationsInDirectory()
    {
        $testFile = $this->testDir . DIRECTORY_SEPARATOR . 'test_file.txt';
        $testContent = 'This is a test file content';
        
        // Test file creation
        $result = file_put_contents($testFile, $testContent);
        $this->assertNotFalse($result, 'File creation should succeed');
        $this->assertTrue(file_exists($testFile), 'File should exist after creation');
        
        // Test file reading
        $readContent = file_get_contents($testFile);
        $this->assertEquals($testContent, $readContent, 'File content should match');
        
        // Test file deletion
        $deleteResult = unlink($testFile);
        $this->assertTrue($deleteResult, 'File deletion should succeed');
        $this->assertFalse(file_exists($testFile), 'File should not exist after deletion');
    }

    public function testDirectoryListing()
    {
        // Create test files
        $testFiles = ['file1.txt', 'file2.txt', 'file3.txt'];
        foreach ($testFiles as $fileName) {
            $filePath = $this->testDir . DIRECTORY_SEPARATOR . $fileName;
            file_put_contents($filePath, 'test content');
        }
        
        // Create test subdirectory
        $subDir = $this->testDir . DIRECTORY_SEPARATOR . 'subdir';
        mkdir($subDir);
        
        // Test directory listing
        $contents = scandir($this->testDir);
        $filteredContents = array_diff($contents, ['.', '..']);
        
        $this->assertCount(4, $filteredContents, 'Directory should contain 3 files and 1 subdirectory');
        
        foreach ($testFiles as $fileName) {
            $this->assertContains($fileName, $filteredContents, "Directory listing should contain {$fileName}");
        }
        
        $this->assertContains('subdir', $filteredContents, 'Directory listing should contain subdirectory');
    }

    public function testDirectoryPathNormalization()
    {
        // Test various path formats
        $paths = [
            './tmp' => 'tmp',
            './tmp/' => 'tmp',
            './tmp/subdir' => 'tmp' . DIRECTORY_SEPARATOR . 'subdir',
            '../tmp' => '..' . DIRECTORY_SEPARATOR . 'tmp',
        ];
        
        foreach ($paths as $input => $expected) {
            $normalized = $this->normalizePath($input);
            $this->assertStringContainsString($expected, $normalized, "Path {$input} should normalize correctly");
        }
    }

    private function normalizePath($path)
    {
        // Simple path normalization
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        
        // Remove leading ./
        if (str_starts_with($path, '.' . DIRECTORY_SEPARATOR)) {
            $path = substr($path, 2);
        }
        
        return $path;
    }

    public function testDirectoryPermissions()
    {
        // Skip permission tests on Windows
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('Permission tests are not applicable on Windows');
        }
        
        $testSubDir = $this->testDir . DIRECTORY_SEPARATOR . 'perm_test';
        mkdir($testSubDir, 0755);
        
        // Test readable directory
        $this->assertTrue(is_readable($testSubDir), 'Directory should be readable');
        
        // Test writable directory
        $this->assertTrue(is_writable($testSubDir), 'Directory should be writable');
        
        // Test executable directory (can enter)
        $this->assertTrue(is_executable($testSubDir), 'Directory should be executable');
    }

    public function testContainerDirectoryCreation()
    {
        $_ENV['CONTAINER_DIR'] = $this->testDir . DIRECTORY_SEPARATOR . 'container';
        $containerDir = $_ENV['CONTAINER_DIR'];
        
        // Test that we can create the container directory if it doesn't exist
        if (!is_dir($containerDir)) {
            $result = mkdir($containerDir, 0755, true);
            $this->assertTrue($result, 'Container directory creation should succeed');
        }
        
        $this->assertTrue(is_dir($containerDir), 'Container directory should exist');
        $this->assertTrue(is_writable($containerDir), 'Container directory should be writable');
    }

    public function testDirectorySize()
    {
        // Create files with known sizes
        $file1 = $this->testDir . DIRECTORY_SEPARATOR . 'file1.txt';
        $file2 = $this->testDir . DIRECTORY_SEPARATOR . 'file2.txt';
        
        file_put_contents($file1, str_repeat('A', 100)); // 100 bytes
        file_put_contents($file2, str_repeat('B', 200)); // 200 bytes
        
        // Calculate directory size
        $totalSize = $this->getDirectorySize($this->testDir);
        $this->assertEquals(300, $totalSize, 'Directory size should be sum of file sizes');
    }

    private function getDirectorySize($directory)
    {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
}