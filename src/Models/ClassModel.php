<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $fillable = [
        'class_id', 'subject_id', 'teacher_id', 'room_id',
        'time_slot_id', 'day', 'term', 'is_override'
    ];
    protected $casts = ['is_override' => 'boolean'];
    public $timestamps = true;
}