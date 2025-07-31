<?php
namespace App\Models;

class Student
{
    public string $id;
    public string $name;
    public string $curriculumId;
    public int $enrollmentCount; // Number of students enrolled

    public function __construct($data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->curriculumId = $data['curriculumId'];
        $this->enrollmentCount = $data['enrollmentCount'] ?? 1;
    }
}