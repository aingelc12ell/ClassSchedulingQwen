<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $fillable = ['code', 'title', 'units', 'weekly_hours'];
    public $timestamps = false;
    
    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE subjects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL,
        title VARCHAR(150) NOT NULL,
        units INT NOT NULL,
        weekly_hours INT NOT NULL,
        UNIQUE KEY unique_code (code)
    )";
}