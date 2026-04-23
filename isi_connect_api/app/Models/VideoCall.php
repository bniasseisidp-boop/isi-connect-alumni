<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoCall extends Model
{
    protected $fillable = ['caller_id', 'callee_id', 'work_group_id', 'room_id', 'status', 'offer', 'answer'];

    public function caller() { return $this->belongsTo(User::class, 'caller_id'); }
    public function callee() { return $this->belongsTo(User::class, 'callee_id'); }
    public function iceCandidates() { return $this->hasMany(CallIceCandidate::class, 'call_id'); }
}
