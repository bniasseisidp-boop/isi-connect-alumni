<?php
// app/Models/ForumThread.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'image_path',
    ];

    /**
     * Obtenir l'auteur (utilisateur) du sujet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir toutes les réponses de ce sujet.
     */
    public function replies()
    {
        return $this->hasMany(ForumReply::class);
    }
}