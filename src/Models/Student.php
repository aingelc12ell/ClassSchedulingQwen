<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    protected $table = 'students';
    protected $fillable = ['id', 'name', 'curriculumId', 'enrollmentCount'];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class, 'curriculumId', 'id');
    }
}