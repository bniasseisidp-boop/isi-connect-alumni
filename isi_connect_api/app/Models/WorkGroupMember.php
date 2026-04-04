<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_group_id',
        'user_id',
        'role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workGroup()
    {
        return $this->belongsTo(WorkGroup::class);
    }
}
