<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $fillable = ['id', 'capacity'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
}