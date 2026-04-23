<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Profile;
use App\Models\ForumThread;
use App\Models\ForumReply;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    // app/Models/User.php

protected $fillable = [
    'name',
    'email',
    'password',
    'promotion_year',
    'role',
    'last_seen_at',
];

public function isOnline(): bool
{
    return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
}

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function profile()
{
    return $this->hasOne(Profile::class);
}

    /**
 * Obtenir les offres d'emploi postées par l'utilisateur.
 */
public function jobPostings()
{
    return $this->hasMany(JobPosting::class);
}

/**
 * Obtenir les événements créés par l'utilisateur.
 */
public function events()
{
    return $this->hasMany(Event::class);
}


/**
 * Obtenir les sujets de forum créés par l'utilisateur.
 */
public function forumThreads()
{
    return $this->hasMany(ForumThread::class);
}

/**
 * Obtenir les réponses de forum créées par l'utilisateur.
 */
public function forumReplies()
{
    return $this->hasMany(ForumReply::class);
}

public function posts()
{
    return $this->hasMany(Post::class);
}

public function postComments()
{
    return $this->hasMany(PostComment::class);
}

public function postLikes()
{
    return $this->hasMany(PostLike::class);
}

public function conversations()
{
    // Les conversations où l'utilisateur est soit user_one soit user_two
    return Conversation::where('user_one_id', $this->id)
                       ->orWhere('user_two_id', $this->id);
}

public function sentMessages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

public function workGroups()
{
    return $this->belongsToMany(WorkGroup::class, 'work_group_members');
}

public function createdGroups()
{
    return $this->hasMany(WorkGroup::class, 'creator_id');
}

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $appends = ['is_online'];

    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at && \Carbon\Carbon::parse($this->last_seen_at)->gt(now()->subMinutes(5));
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
