<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'teachers';
    protected $fillable = ['id', 'name', 'qualified_subject_ids'];
    protected $casts = [
        'qualified_subject_ids' => 'array'
    ];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;
}