<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KeranjangItem[] $keranjangItems
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pesanan[] $pesanan
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Role[] $roles
 */

class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_public_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'profile_photo_public_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $appends = [
        'profile_photo_url',
        'initials',
    ];

    // --- Filament Access Control ---
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->email === 'admin@atk.com';
    }

    // --- Relationships ---
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'user_id');
    }

    public function keranjangItems(): HasMany
    {
        return $this->hasMany(KeranjangItem::class, 'user_id');
    }

    // --- Accessors ---
    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo_public_id) {
            try {
                return Cloudinary::secureUrl($this->profile_photo_public_id, [
                    'transformation' => [
                        ['width' => 200, 'height' => 200, 'crop' => 'fill', 'gravity' => 'face'],
                        ['radius' => 'max'],
                        ['fetch_format' => 'auto', 'quality' => 'auto']
                    ]
                ]);
            } catch (\Exception $e) {
                Log::error("Cloudinary URL generation failed for user {$this->id}: " . $e->getMessage());
                return 'https://via.placeholder.com/200?text=Error';
            }
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=FFFFFF&background=0D8ABC&size=200&bold=true';
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name ?? ''));
        $initials = '';

        if (isset($words[0]) && !empty($words[0])) {
            $initials .= Str::upper(substr($words[0], 0, 1));
        }

        if (count($words) >= 2 && isset($words[count($words) - 1]) && !empty($words[count($words) - 1])) {
            $initials .= Str::upper(substr($words[count($words) - 1], 0, 1));
        } elseif (strlen($initials) === 1 && isset($words[0]) && strlen($words[0]) > 1) {
            $initials .= Str::upper(substr($words[0], 1, 1));
        }

        return $initials ?: '??';
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    // --- JWT Methods ---
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
