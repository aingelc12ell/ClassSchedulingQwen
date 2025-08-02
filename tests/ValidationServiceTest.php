<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Services\ValidationService;

class ValidationServiceTest extends TestCase
{
    private ValidationService $validator;

    protected function setUp(): void
    {
        $this->validator = new ValidationService();
    }

    public function testValidateWithValidData()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25
        ];

        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'age' => 'required|integer'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result);
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testValidateWithInvalidData()
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'age' => 'not-a-number'
        ];

        $rules = [
            'name' => 'required|string',
            'email' => 'required|email',
            'age' => 'required|integer'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);
        
        $errors = $this->validator->getErrors();
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('age', $errors);
    }

    public function testRequiredValidation()
    {
        // Test required field missing
        $data = [];
        $rules = ['name' => 'required'];
        
        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);
        
        $errors = $this->validator->getErrors();
        $this->assertContains(['The name field is required.'], $errors['name']);

        // Test required field empty string
        $data = ['name' => ''];
        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);

        // Test required field with value
        $data = ['name' => 'John'];
        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result);
    }

    public function testStringValidation()
    {
        $rules = ['field' => 'string'];

        // Valid string
        $data = ['field' => 'hello'];
        $this->assertTrue($this->validator->validate($data, $rules));

        // Invalid - number
        $data = ['field' => 123];
        $this->assertFalse($this->validator->validate($data, $rules));

        // Invalid - array
        $data = ['field' => []];
        $this->assertFalse($this->validator->validate($data, $rules));
    }

    public function testIntegerValidation()
    {
        $rules = ['field' => 'integer'];

        // Valid integers
        $validIntegers = [123, '456', 0, '0', -5, '-10'];
        foreach ($validIntegers as $value) {
            $data = ['field' => $value];
            $this->assertTrue($this->validator->validate($data, $rules), "Failed for value: $value");
        }

        // Invalid integers
        $invalidIntegers = [12.5, '12.5', 'abc', [], true];
        foreach ($invalidIntegers as $value) {
            $data = ['field' => $value];
            $this->assertFalse($this->validator->validate($data, $rules), "Should fail for value: " . print_r($value, true));
        }
    }

    public function testNumericValidation()
    {
        $rules = ['field' => 'numeric'];

        // Valid numeric values
        $validNumbers = [123, '456', 12.5, '12.5', 0, '0', -5, '-10.5'];
        foreach ($validNumbers as $value) {
            $data = ['field' => $value];
            $this->assertTrue($this->validator->validate($data, $rules), "Failed for value: $value");
        }

        // Invalid numeric values
        $invalidNumbers = ['abc', [], true, 'not-a-number'];
        foreach ($invalidNumbers as $value) {
            $data = ['field' => $value];
            $this->assertFalse($this->validator->validate($data, $rules), "Should fail for value: " . print_r($value, true));
        }
    }

    public function testArrayValidation()
    {
        $rules = ['field' => 'array'];

        // Valid array
        $data = ['field' => [1, 2, 3]];
        $this->assertTrue($this->validator->validate($data, $rules));

        // Valid empty array
        $data = ['field' => []];
        $this->assertTrue($this->validator->validate($data, $rules));

        // Invalid - string
        $data = ['field' => 'not-array'];
        $this->assertFalse($this->validator->validate($data, $rules));

        // Invalid - number
        $data = ['field' => 123];
        $this->assertFalse($this->validator->validate($data, $rules));
    }

    public function testBooleanValidation()
    {
        $rules = ['field' => 'boolean'];

        // Valid boolean values
        $validBooleans = [true, false, 0, 1, '0', '1'];
        foreach ($validBooleans as $value) {
            $data = ['field' => $value];
            $this->assertTrue($this->validator->validate($data, $rules), "Failed for value: " . print_r($value, true));
        }

        // Invalid boolean values
        $invalidBooleans = ['true', 'false', 2, '2', 'yes', 'no', []];
        foreach ($invalidBooleans as $value) {
            $data = ['field' => $value];
            $this->assertFalse($this->validator->validate($data, $rules), "Should fail for value: " . print_r($value, true));
        }
    }

    public function testEmailValidation()
    {
        $rules = ['email' => 'email'];

        // Valid emails
        $validEmails = ['test@example.com', 'user.name@domain.co.uk', 'admin+tag@site.org'];
        foreach ($validEmails as $email) {
            $data = ['email' => $email];
            $this->assertTrue($this->validator->validate($data, $rules), "Failed for email: $email");
        }

        // Invalid emails
        $invalidEmails = ['invalid-email', '@domain.com', 'user@', 'user@domain', 'user name@domain.com'];
        foreach ($invalidEmails as $email) {
            $data = ['email' => $email];
            $this->assertFalse($this->validator->validate($data, $rules), "Should fail for email: $email");
        }
    }

    public function testMinValidation()
    {
        // Test numeric min
        $rules = ['number' => 'min:10'];
        
        $data = ['number' => 15];
        $this->assertTrue($this->validator->validate($data, $rules));
        
        $data = ['number' => 5];
        $this->assertFalse($this->validator->validate($data, $rules));

        // Test string min length
        $rules = ['text' => 'min:5'];
        
        $data = ['text' => 'hello world'];
        $this->assertTrue($this->validator->validate($data, $rules));
        
        $data = ['text' => 'hi'];
        $this->assertFalse($this->validator->validate($data, $rules));
    }

    public function testMaxValidation()
    {
        // Test numeric max
        $rules = ['number' => 'max:100'];
        
        $data = ['number' => 50];
        $this->assertTrue($this->validator->validate($data, $rules));
        
        $data = ['number' => 150];
        $this->assertFalse($this->validator->validate($data, $rules));

        // Test string max length
        $rules = ['text' => 'max:10'];
        
        $data = ['text' => 'short'];
        $this->assertTrue($this->validator->validate($data, $rules));
        
        $data = ['text' => 'this is a very long text'];
        $this->assertFalse($this->validator->validate($data, $rules));
    }

    public function testInValidation()
    {
        $rules = ['status' => 'in:active,inactive,pending'];

        // Valid values
        $validStatuses = ['active', 'inactive', 'pending'];
        foreach ($validStatuses as $status) {
            $data = ['status' => $status];
            $this->assertTrue($this->validator->validate($data, $rules), "Failed for status: $status");
        }

        // Invalid values
        $invalidStatuses = ['deleted', 'archived', 'unknown'];
        foreach ($invalidStatuses as $status) {
            $data = ['status' => $status];
            $this->assertFalse($this->validator->validate($data, $rules), "Should fail for status: $status");
        }
    }

    public function testDateFormatValidation()
    {
        // Test default format (Y-m-d H:i:s)
        $rules = ['date' => 'date_format'];
        
        $data = ['date' => '2024-01-15 14:30:00'];
        $this->assertTrue($this->validator->validate($data, $rules));
        
        $data = ['date' => '2024-01-15'];
        $this->assertFalse($this->validator->validate($data, $rules));

        // Test custom format
        $rules = ['date' => 'date_format:Y-m-d'];
        
        $data = ['date' => '2024-01-15'];
        $this->assertTrue($this->validator->validate($data, $rules));
        
        $data = ['date' => '15/01/2024'];
        $this->assertFalse($this->validator->validate($data, $rules));
    }

    public function testTimeValidation()
    {
        $rules = ['time' => 'time'];

        // Valid times
        $validTimes = ['09:30', '23:59', '00:00', '12:00'];
        foreach ($validTimes as $time) {
            $data = ['time' => $time];
            $this->assertTrue($this->validator->validate($data, $rules), "Failed for time: $time");
        }

        // Invalid times
        $invalidTimes = ['9:30', '24:00', '12:60', '12:5', 'invalid'];
        foreach ($invalidTimes as $time) {
            $data = ['time' => $time];
            $this->assertFalse($this->validator->validate($data, $rules), "Should fail for time: $time");
        }
    }

    public function testJsonArrayValidation()
    {
        $rules = ['data' => 'json_array'];

        // Valid non-empty array
        $data = ['data' => [1, 2, 3]];
        $this->assertTrue($this->validator->validate($data, $rules));

        // Invalid - empty array
        $data = ['data' => []];
        $this->assertFalse($this->validator->validate($data, $rules));

        // Invalid - not array
        $data = ['data' => 'not-array'];
        $this->assertFalse($this->validator->validate($data, $rules));
    }

    public function testSubjectHoursValidation()
    {
        $rules = ['weekly_hours' => 'subject_hours'];

        // Valid - weekly hours >= units
        $data = ['weekly_hours' => 4, 'units' => 3];
        $this->assertTrue($this->validator->validate($data, $rules));

        // Invalid - weekly hours < units
        $data = ['weekly_hours' => 2, 'units' => 3];
        $this->assertFalse($this->validator->validate($data, $rules));

        // Valid - no units specified
        $data = ['weekly_hours' => 2];
        $this->assertTrue($this->validator->validate($data, $rules));
    }

    public function testMultipleRulesWithPipeDelimiter()
    {
        $data = [
            'username' => 'john_doe',
            'age' => 25,
            'email' => 'john@example.com'
        ];

        $rules = [
            'username' => 'required|string|min:3|max:20',
            'age' => 'required|integer|min:18|max:100',
            'email' => 'required|email'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result);
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testMultipleRulesWithArrayFormat()
    {
        $data = [
            'username' => 'john_doe',
            'age' => 25
        ];

        $rules = [
            'username' => ['required', 'string', 'min:3', 'max:20'],
            'age' => ['required', 'integer', 'min:18']
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertTrue($result);
        $this->assertEmpty($this->validator->getErrors());
    }

    public function testUnknownRule()
    {
        $data = ['field' => 'value'];
        $rules = ['field' => 'unknown_rule'];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);

        $errors = $this->validator->getErrors();
        $this->assertArrayHasKey('field', $errors);
        $this->assertContains(['Unknown validation rule: unknown_rule.'], $errors['field']);
    }

    public function testFailMethod()
    {
        $message = 'Custom validation error';
        $result = $this->validator->fail($message);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('general', $result);
        $this->assertContains($message, $result['general']);

        // Test that getErrors() returns the same errors
        $errors = $this->validator->getErrors();
        $this->assertEquals($result, $errors);
    }

    public function testErrorAccumulation()
    {
        $data = [
            'field1' => '',
            'field2' => 'invalid-email'
        ];

        $rules = [
            'field1' => 'required|min:5',
            'field2' => 'email|min:10'
        ];

        $result = $this->validator->validate($data, $rules);
        $this->assertFalse($result);

        $errors = $this->validator->getErrors();
        
        // field1 should have required error
        $this->assertArrayHasKey('field1', $errors);
        $this->assertCount(1, $errors['field1']); // Only required error, min not checked for empty value
        
        // field2 should have email error
        $this->assertArrayHasKey('field2', $errors);
        $this->assertCount(1, $errors['field2']); // Email validation fails first
    }

    public function testValidationResetsErrors()
    {
        // First validation with errors
        $data = ['field' => ''];
        $rules = ['field' => 'required'];
        
        $this->validator->validate($data, $rules);
        $this->assertNotEmpty($this->validator->getErrors());

        // Second validation should reset errors
        $data = ['field' => 'value'];
        $result = $this->validator->validate($data, $rules);
        
        $this->assertTrue($result);
        $this->assertEmpty($this->validator->getErrors());
    }
}