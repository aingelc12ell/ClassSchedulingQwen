<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $table = 'time_slots';
    protected $fillable = ['label', 'start_time', 'end_time', 'is_active'];
    public $timestamps = true;
}