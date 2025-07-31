<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'teachers';
    protected $fillable = ['code', 'name', 'qualified_subject_ids'];
    protected $casts = [
        'qualified_subject_ids' => 'array'
    ];
    public $timestamps = false;
    
    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        qualified_subject_ids TEXT NULL,
        UNIQUE KEY unique_code (code)
    )";
}