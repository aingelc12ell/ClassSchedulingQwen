<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $table = 'curriculums';
    protected $fillable = ['id', 'name', 'term', 'subject_ids'];
    protected $casts = [
        'subject_ids' => 'array'
    ];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
}