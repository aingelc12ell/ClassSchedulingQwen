<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $table = 'curriculums';
    protected $fillable = ['code', 'name', 'term', 'subject_ids'];
    protected $casts = [
        'subject_ids' => 'array'
    ];
    public $timestamps = false;
    
    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE curriculums (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL,
        name VARCHAR(150) NOT NULL,
        term VARCHAR(50) NOT NULL,
        subject_ids TEXT NULL,
        UNIQUE KEY unique_code (code),
        INDEX idx_term (term)
    )";
}