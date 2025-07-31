<?php
namespace App\Models;

class Room
{
    public string $id;
    public int $capacity;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->capacity = $data['capacity'];
    }
}