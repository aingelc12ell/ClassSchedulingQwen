<?php
namespace App\Models;

class Curriculum
{
    public string $id;
    public string $name;
    public string $term;
    public array $subjectIds;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->term = $data['term'];
        $this->subjectIds = $data['subjectIds'];
    }
}