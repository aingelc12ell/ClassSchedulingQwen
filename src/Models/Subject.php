<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subjects';
    protected $fillable = ['id', 'title', 'units', 'weekly_hours'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
}