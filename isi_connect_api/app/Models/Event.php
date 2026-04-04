<?php
// app/Models/Event.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'location',
        'starts_at',
        'ends_at',
        'cover_image_url',
    ];

    /**
     * Définir les champs qui doivent être convertis en types natifs.
     * (Très important pour les dates !)
     *
     * @var array
     */
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Obtenir l'utilisateur qui a créé l'événement.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}