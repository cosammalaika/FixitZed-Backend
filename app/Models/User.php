<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'contact_number',
        'status',
        'address',
        'profile_photo_path',
        'nrc_front_path',
        'nrc_back_path',
        'documents',
        'loyalty_points',
        'password',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',
        'primary_role',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'documents' => 'array',
            'loyalty_points' => 'integer',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return strtoupper(
            Str::substr($this->first_name, 0, 1) .
            Str::substr($this->last_name, 0, 1)
        );
    }

    public function receivedRatings()
{
    return $this->hasMany(\App\Models\Rating::class, 'rated_user_id');
}

    public function fixer()
    {
        return $this->hasOne(Fixer::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    

    public function ratingsGiven()
    {
        return $this->hasMany(Rating::class, 'rater_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->getRoleNames()->first() ?: null;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        $path = $this->profile_photo_path;
        if (! $path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return Storage::disk('public')->url($path);
    }
}
