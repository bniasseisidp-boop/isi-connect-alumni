<?php
// app/Models/ForumReply.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'forum_thread_id',
        'body',
    ];

    /**
     * Obtenir l'auteur (utilisateur) de la réponse.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir le sujet auquel cette réponse appartient.
     */
    public function thread()
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }
}