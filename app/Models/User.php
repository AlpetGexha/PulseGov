<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        // 'role' => UserRole::class,
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function feedbackStatuses(): HasMany
    {
        return $this->hasMany(FeedbackStatus::class);
    }
}
