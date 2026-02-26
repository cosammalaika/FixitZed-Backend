<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, SoftDeletes;

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
        'province',
        'district',
        'profile_photo_path',
        'nrc_front_path',
        'nrc_back_path',
        'documents',
        'work_photos',
        'loyalty_points',
        'password',
        'mfa_secret',
        'mfa_temp_secret',
        'mfa_enabled',
        'mfa_backup_codes',
        'mfa_last_confirmed_at',
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
        'avatar_updated_at',
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
            'work_photos' => 'array',
            'loyalty_points' => 'integer',
            'mfa_enabled' => 'boolean',
            'mfa_backup_codes' => 'array',
            'mfa_last_confirmed_at' => 'datetime',
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

    public function trustedDevices()
    {
        return $this->hasMany(UserTrustedDevice::class);
    }

    public function consumeBackupCode(string $code): bool
    {
        $codes = is_array($this->mfa_backup_codes) ? $this->mfa_backup_codes : [];
        $remaining = [];
        $used = false;

        foreach ($codes as $stored) {
            if (! $used && Hash::check($code, $stored)) {
                $used = true;
                continue;
            }
            $remaining[] = $stored;
        }

        if ($used) {
            $this->mfa_backup_codes = $remaining;
            $this->save();
        }

        return $used;
    }

    public function issueTrustedDevice(?string $deviceName = null): string
    {
        $token = Str::random(64);
        $hash = hash('sha256', $token);

        $this->trustedDevices()->create([
            'device_key' => $hash,
            'device_name' => $deviceName ?: 'Device ' . now()->format('Y-m-d H:i'),
            'last_ip' => request()->ip(),
            'last_used_at' => now(),
        ]);

        return $token;
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
            return $this->normalizeAvatarPublicUrl($path);
        }
        $url = Storage::disk('public')->url($path);
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            $url = url($url);
        }
        return $this->normalizeAvatarPublicUrl($url);
    }

    public function getAvatarUpdatedAtAttribute(): ?string
    {
        return optional($this->updated_at)->toISOString();
    }

    protected function normalizeAvatarPublicUrl(string $url): string
    {
        $request = request();
        if (! $request) {
            return $url;
        }

        $requestHost = $request->getHost();
        $requestIsLocal = in_array($requestHost, ['localhost', '127.0.0.1'], true);

        $parts = parse_url($url);
        if (! is_array($parts)) {
            return $url;
        }

        $urlHost = $parts['host'] ?? null;
        $urlIsLocal = in_array($urlHost, ['localhost', '127.0.0.1'], true);

        if ($urlIsLocal && ! $requestIsLocal && ! empty($requestHost)) {
            $scheme = $request->getScheme();
            $path = $parts['path'] ?? '';
            $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
            $fragment = isset($parts['fragment']) ? ('#' . $parts['fragment']) : '';
            $port = $request->getPort();
            $defaultPort = $request->isSecure() ? 443 : 80;
            $portPart = ($port && $port !== $defaultPort) ? (':' . $port) : '';

            return $scheme . '://' . $requestHost . $portPart . $path . $query . $fragment;
        }

        if (($parts['scheme'] ?? null) === 'http' && $request->isSecure() && ! empty($urlHost) && $urlHost === $requestHost) {
            return 'https://' . ($parts['host'] ?? '') .
                (isset($parts['port']) ? ':' . $parts['port'] : '') .
                ($parts['path'] ?? '') .
                (isset($parts['query']) ? ('?' . $parts['query']) : '') .
                (isset($parts['fragment']) ? ('#' . $parts['fragment']) : '');
        }

        return $url;
    }
}
