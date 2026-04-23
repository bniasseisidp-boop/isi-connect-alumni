<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallIceCandidate extends Model
{
    protected $fillable = ['call_id', 'user_id', 'candidate'];
}
