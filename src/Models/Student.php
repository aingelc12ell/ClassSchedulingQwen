<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $table = 'students';
    protected $fillable = ['cardid', 'name', 'curriculumId', 'enrollmentCount'];
    public $timestamps = false;
    
    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cardid VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        curriculumId VARCHAR(50) NOT NULL,
        enrollmentCount INT NOT NULL DEFAULT 1,
        UNIQUE KEY unique_cardid (cardid)
    )";

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculumId', 'id');
    }
}