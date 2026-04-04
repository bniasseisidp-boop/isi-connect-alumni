<?php
// app/Models/JobPosting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Le nom de la classe est JobPosting
class JobPosting extends Model
{
    use HasFactory;

    // Laravel est assez intelligent pour savoir que
    // 'JobPosting' correspond à la table 'job_postings',
    // mais on peut le préciser pour être 100% sûr.
    protected $table = 'job_postings'; 

    protected $fillable = [
        'user_id',
        'title',
        'company_name',
        'description',
        'location',
        'type',
        'apply_url',
        'apply_email',
        'image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}