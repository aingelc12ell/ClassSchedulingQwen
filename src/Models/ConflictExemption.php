<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConflictExemption extends Model
{
    protected $table = 'conflict_exemptions';
    protected $fillable = ['type', 'entity_id', 'conflict_type', 'reason', 'expires_at'];
    // type: 'student', 'teacher'
    // conflict_type: 'schedule', 'capacity'
}