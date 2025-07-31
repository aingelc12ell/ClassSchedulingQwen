<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConflictExemption extends Model
{
    protected $table = 'conflict_exemptions';
    protected $fillable = ['type', 'entity_id', 'conflict_type', 'reason', 'expires_at'];
    public $timestamps = false;
    // type: 'student', 'teacher', 'room'
    // conflict_type: 'schedule', 'capacity'
    
    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE conflict_exemptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(20) NOT NULL,
        entity_id INT NOT NULL,
        conflict_type VARCHAR(20) NOT NULL,
        reason TEXT NOT NULL,
        expires_at TIMESTAMP NULL,
        UNIQUE KEY unique_conflict_exemption (type, entity_id, conflict_type)
    )";
}