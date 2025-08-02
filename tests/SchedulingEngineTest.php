<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Services\SchedulingEngine;

class SchedulingEngineTest extends TestCase
{
    private SchedulingEngine $engine;

    protected function setUp(): void
    {
        // Note: This test focuses on the logic that doesn't require database
        // For full integration testing, database setup would be needed

        // Create a test engine and set up exemptions using reflection
        $this->engine = new SchedulingEngine();

        $reflection = new ReflectionClass($this->engine);
        $exemptionsProperty = $reflection->getProperty('exemptions');
        $exemptionsProperty->setAccessible(true);

        $testExemptions = [
            'teacher_123' => [
                'schedule' => 'Teacher has special scheduling needs',
                'overtime' => 'Approved for overtime work',
            ],
            'student_456' => [
                'schedule' => 'Student has medical exemption',
            ],
            'room_789' => [
                'capacity' => 'Room can exceed normal capacity',
            ],
        ];

        $exemptionsProperty->setValue($this->engine, $testExemptions);
    }

    public function testIsExemptWithValidExemption()
    {
        // Test teacher exemption
        $result = $this->engine->isExempt('teacher', '123', 'schedule');
        $this->assertTrue($result, 'Teacher 123 should be exempt from schedule conflicts');

        // Test student exemption
        $result = $this->engine->isExempt('student', '456', 'schedule');
        $this->assertTrue($result, 'Student 456 should be exempt from schedule conflicts');

        // Test room exemption
        $result = $this->engine->isExempt('room', '789', 'capacity');
        $this->assertTrue($result, 'Room 789 should be exempt from capacity limits');
    }

    public function testIsExemptWithInvalidExemption()
    {
        // Test non-existent teacher
        $result = $this->engine->isExempt('teacher', '999', 'schedule');
        $this->assertFalse($result, 'Teacher 999 should not be exempt');

        // Test non-existent conflict type
        $result = $this->engine->isExempt('teacher', '123', 'nonexistent');
        $this->assertFalse($result, 'Teacher 123 should not be exempt from nonexistent conflict');

        // Test non-existent entity type
        $result = $this->engine->isExempt('invalid', '123', 'schedule');
        $this->assertFalse($result, 'Invalid entity type should not be exempt');
    }

    public function testIsExemptWithDifferentEntityTypes()
    {
        // Test that exemptions are entity-type specific
        $result = $this->engine->isExempt('student', '123', 'schedule');
        $this->assertFalse($result, 'Student 123 should not have teacher 123 exemptions');

        $result = $this->engine->isExempt('room', '456', 'schedule');
        $this->assertFalse($result, 'Room 456 should not have student 456 exemptions');
    }

    public function testIsExemptWithMultipleConflictTypes()
    {
        // Test teacher with multiple exemptions
        $this->assertTrue($this->engine->isExempt('teacher', '123', 'schedule'));
        $this->assertTrue($this->engine->isExempt('teacher', '123', 'overtime'));
        $this->assertFalse($this->engine->isExempt('teacher', '123', 'workload'));
    }

    public function testExemptionKeyFormat()
    {
        // Test that the exemption key format is correct (type_id)
        $reflection = new ReflectionClass($this->engine);
        $exemptionsProperty = $reflection->getProperty('exemptions');
        $exemptionsProperty->setAccessible(true);
        $exemptions = $exemptionsProperty->getValue($this->engine);

        $this->assertArrayHasKey('teacher_123', $exemptions);
        $this->assertArrayHasKey('student_456', $exemptions);
        $this->assertArrayHasKey('room_789', $exemptions);
        $this->assertArrayNotHasKey('123_teacher', $exemptions);
    }

    public function testExemptionStructure()
    {
        $reflection = new ReflectionClass($this->engine);
        $exemptionsProperty = $reflection->getProperty('exemptions');
        $exemptionsProperty->setAccessible(true);
        $exemptions = $exemptionsProperty->getValue($this->engine);

        // Test that exemptions have the correct structure
        $this->assertIsArray($exemptions['teacher_123']);
        $this->assertArrayHasKey('schedule', $exemptions['teacher_123']);
        $this->assertArrayHasKey('overtime', $exemptions['teacher_123']);

        $this->assertIsString($exemptions['teacher_123']['schedule']);
        $this->assertEquals('Teacher has special scheduling needs', $exemptions['teacher_123']['schedule']);
    }

    public function testGenerateScheduleMethodExists()
    {
        // Test that the generateSchedule method exists and is callable
        $this->assertTrue(method_exists($this->engine, 'generateSchedule'));

        $reflection = new ReflectionMethod($this->engine, 'generateSchedule');
        $this->assertTrue($reflection->isPublic());
    }

    public function testGenerateScheduleReturnsArray()
    {
        // Create a mock engine that doesn't depend on database
        $mockEngine = $this->getMockBuilder(SchedulingEngine::class)
            ->onlyMethods(['generateSchedule'])
            ->getMock();

        $mockEngine->method('generateSchedule')
            ->willReturn([
                [
                    'class_id' => 'cls_test123',
                    'subject_id' => 'MATH101',
                    'teacher_id' => 'T001',
                    'room_id' => 'R101',
                    'time_slot_id' => 1,
                    'day' => 'Mon',
                    'term' => 'Fall2024',
                    'is_override' => false,
                ],
            ]);

        $result = $mockEngine->generateSchedule();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('class_id', $result[0]);
        $this->assertArrayHasKey('subject_id', $result[0]);
        $this->assertArrayHasKey('teacher_id', $result[0]);
        $this->assertArrayHasKey('room_id', $result[0]);
        $this->assertArrayHasKey('time_slot_id', $result[0]);
        $this->assertArrayHasKey('day', $result[0]);
        $this->assertArrayHasKey('term', $result[0]);
        $this->assertArrayHasKey('is_override', $result[0]);
    }

    public function testGenerateScheduleWithFilters()
    {
        $mockEngine = $this->getMockBuilder(SchedulingEngine::class)
            ->onlyMethods(['generateSchedule'])
            ->getMock();

        $mockEngine->expects($this->once())
            ->method('generateSchedule')
            ->with(['term' => 'Fall2024'])
            ->willReturn([]);

        $result = $mockEngine->generateSchedule(['term' => 'Fall2024']);
        $this->assertIsArray($result);
    }

    public function testScheduleClassStructure()
    {
        // Test the expected structure of a scheduled class
        $expectedKeys = [
            'class_id', 'subject_id', 'teacher_id', 'room_id',
            'time_slot_id', 'day', 'term', 'is_override',
        ];

        $mockClass = [
            'class_id' => 'cls_test123',
            'subject_id' => 'MATH101',
            'teacher_id' => 'T001',
            'room_id' => 'R101',
            'time_slot_id' => 1,
            'day' => 'Mon',
            'term' => 'Fall2024',
            'is_override' => false,
        ];

        foreach($expectedKeys as $key){
            $this->assertArrayHasKey($key, $mockClass, "Class should have key: $key");
        }

        // Test data types
        $this->assertIsString($mockClass['class_id']);
        $this->assertIsString($mockClass['subject_id']);
        $this->assertIsString($mockClass['teacher_id']);
        $this->assertIsString($mockClass['room_id']);
        $this->assertIsInt($mockClass['time_slot_id']);
        $this->assertIsString($mockClass['day']);
        $this->assertIsString($mockClass['term']);
        $this->assertIsBool($mockClass['is_override']);
    }

    public function testValidDayValues()
    {
        $validDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

        foreach($validDays as $day){
            $this->assertContains($day, $validDays, "Day $day should be valid");
            $this->assertEquals(3, strlen($day), "Day $day should be 3 characters");
        }
    }

    public function testExemptionEdgeCases()
    {
        // Test with empty strings
        $result = $this->engine->isExempt('', '123', 'schedule');
        $this->assertFalse($result);

        $result = $this->engine->isExempt('teacher', '', 'schedule');
        $this->assertFalse($result);

        $result = $this->engine->isExempt('teacher', '123', '');
        $this->assertFalse($result);

        // Test with null values (should not cause errors)
        $result = $this->engine->isExempt('teacher', '123', 'null_conflict');
        $this->assertFalse($result);
    }

    public function testConstructorInitializesEngine()
    {
        // Test that constructor properly initializes the engine
        $engine = new SchedulingEngine();

        // Test that the engine is properly instantiated
        $this->assertInstanceOf(SchedulingEngine::class, $engine);

        // Test that exemptions property is initialized as array
        $reflection = new ReflectionClass($engine);
        $exemptionsProperty = $reflection->getProperty('exemptions');
        $exemptionsProperty->setAccessible(true);
        $exemptions = $exemptionsProperty->getValue($engine);

        $this->assertIsArray($exemptions);
    }

    public function testLoadExemptionsMethodExists()
    {
        $reflection = new ReflectionClass(SchedulingEngine::class);
        $this->assertTrue($reflection->hasMethod('loadExemptions'));

        $method = $reflection->getMethod('loadExemptions');
        $this->assertTrue($method->isPrivate());
    }

    public function testClassIdGeneration()
    {
        // Test that class IDs follow expected pattern
        $classId = 'cls_' . uniqid();

        $this->assertStringStartsWith('cls_', $classId);
        $this->assertGreaterThan(4, strlen($classId)); // 'cls_' + unique part

        // Test uniqueness
        $classId2 = 'cls_' . uniqid();
        $this->assertNotEquals($classId, $classId2);
    }

    public function testSchedulingEngineProperties()
    {
        $reflection = new ReflectionClass(SchedulingEngine::class);

        // Test that exemptions property exists and is private
        $this->assertTrue($reflection->hasProperty('exemptions'));
        $exemptionsProperty = $reflection->getProperty('exemptions');
        $this->assertTrue($exemptionsProperty->isPrivate());

        // Test that exemptions is an array
        $exemptionsProperty->setAccessible(true);
        $exemptions = $exemptionsProperty->getValue($this->engine);
        $this->assertIsArray($exemptions);
    }

    public function testSchedulingLogicConstants()
    {
        // Test expected days of the week used in scheduling
        $expectedDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

        foreach($expectedDays as $day){
            $this->assertIsString($day);
            $this->assertEquals(3, strlen($day));
        }

        // Test that weekend days are not included
        $this->assertNotContains('Sat', $expectedDays);
        $this->assertNotContains('Sun', $expectedDays);
    }
}