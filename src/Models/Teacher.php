<?php
namespace App\Models;

class Teacher
{
    public string $id;
    public string $name;
    public array $qualifiedSubjectIds;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->qualifiedSubjectIds = $data['qualifiedSubjectIds'];
    }
}