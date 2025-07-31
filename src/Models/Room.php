<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $fillable = ['name', 'capacity'];
    public $timestamps = false;
    
    /**
     * Database schema as generic SQL CREATE TABLE statement
     */
    public static $schema = "CREATE TABLE rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        capacity INT NOT NULL,
        UNIQUE KEY unique_name (name)
    )";
}