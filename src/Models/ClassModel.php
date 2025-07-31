<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $fillable = [
        'class_id', 'subject_id', 'teacher_id', 'room_id',
        'time_slot_id', 'day', 'term', 'is_override',
    ];
    protected $casts = ['is_override' => 'boolean'];
    public $timestamps = false;

    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        class_id INT NOT NULL,
        subject_id INT NOT NULL,
        teacher_id INT NOT NULL,
        room_id INT NOT NULL,
        time_slot_id INT NOT NULL,
        day VARCHAR(3) NOT NULL,
        term VARCHAR(50) NOT NULL,
        is_override BOOLEAN NOT NULL DEFAULT FALSE,
        INDEX idx_subject_id (subject_id),
        INDEX idx_teacher_id (teacher_id),
        INDEX idx_room_id (room_id),
        INDEX idx_time_slot_id (time_slot_id),
        INDEX idx_term (term)
    )";
}