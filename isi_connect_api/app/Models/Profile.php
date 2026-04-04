<?php
// app/Models/Profile.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'bio',
        'job_title',
        'company_name',
        'city',
        'linkedin_url',
        'profile_picture_url',
        'is_visible',
        'is_featured_in_showcase',
    ];

    /**
     * Obtenir l'utilisateur qui possède le profil.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}