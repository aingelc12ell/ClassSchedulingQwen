<?php
namespace App\Models;

class Subject
{
    public string $id;
    public string $title;
    public int $units;
    public int $weeklyHours;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->units = $data['units'];
        $this->weeklyHours = $data['weeklyHours'];
    }
}