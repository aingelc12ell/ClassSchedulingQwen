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
    public string $id;
    public string $subjectId;
    public string $teacherId;
    public string $roomId;
    public string $day;
    public string $timeSlot; // e.g., "09:00-10:00"
    public string $term;

    public function __construct($data) {
        $this->id = $data['id'];
        $this->subjectId = $data['subjectId'];
        $this->teacherId = $data['teacherId'];
        $this->roomId = $data['roomId'];
        $this->day = $data['day'];
        $this->timeSlot = $data['timeSlot'];
        $this->term = $data['term'];
    }
}