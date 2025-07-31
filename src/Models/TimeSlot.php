<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $table = 'time_slots';
    protected $fillable = ['label', 'start_time', 'end_time', 'is_active'];
    public $timestamps = false;
    
    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE time_slots (
        id INT AUTO_INCREMENT PRIMARY KEY,
        label VARCHAR(100) NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        is_active BOOLEAN NOT NULL DEFAULT TRUE,
        UNIQUE KEY unique_label (label)
    )";
}