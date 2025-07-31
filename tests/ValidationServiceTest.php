<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Services\ValidationService;

class ValidationServiceTest extends TestCase
{
    private $validator;

    protected function setUp(): void
    {
        $this->validator = new ValidationService();
    }

    public function testBasicValidationSuccess()
    {
        $data = [
            'name' => 'John Doe',
            'age' => 25,
            'email' => 'john@example.com'
        ];

        $rules = [
            'name' => 'required|string|min:2|max:50',
            'age' => 'required|integer|min:1|max:120',
            'email' => 'required|email'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result);
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testBasicValidationFailure()
    {
        $data = [
            'name' => '',
            'age' => 'not_a_number',
            'email' => 'invalid_email'
        ];

        $rules = [
            'name' => 'required|string|min:2',
            'age' => 'required|integer',
            'email' => 'required|email'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);
        
        $errors = $this->validator->getErrors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('age', $errors);
        $this->assertArrayHasKey('email', $errors);
    }

    public function testTimeValidation()
    {
        $data = [
            'start_time' => '08:30',
            'end_time' => '25:00' // Invalid
        ];

        $rules = [
            'start_time' => 'required|time',
            'end_time' => 'required|time'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);
        
        $errors = $this->validator->getErrors();
        $this->assertArrayNotHasKey('start_time', $errors);
        $this->assertArrayHasKey('end_time', $errors);
    }

    public function testArrayValidation()
    {
        $data = [
            'valid_array' => ['item1', 'item2'],
            'empty_array' => [],
            'not_array' => 'string'
        ];

        $rules = [
            'valid_array' => 'required|json_array',
            'empty_array' => 'required|json_array',
            'not_array' => 'required|json_array'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);
        
        $errors = $this->validator->getErrors();
        $this->assertArrayNotHasKey('valid_array', $errors);
        $this->assertArrayHasKey('empty_array', $errors);
        $this->assertArrayHasKey('not_array', $errors);
    }

    public function testInValidation()
    {
        $data = [
            'status' => 'active',
            'type' => 'invalid_type'
        ];

        $rules = [
            'status' => 'required|in:active,inactive,pending',
            'type' => 'required|in:student,teacher,admin'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);
        
        $errors = $this->validator->getErrors();
        $this->assertArrayNotHasKey('status', $errors);
        $this->assertArrayHasKey('type', $errors);
    }
}