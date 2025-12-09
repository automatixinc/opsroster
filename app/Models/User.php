<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Exception;
use App\Exceptions\EmailOccupiedException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Post;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasTeams;
    use HasApiTokens;
    use HasProfilePhoto;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'profile_photo_url',
        'is_owner',
    ];

    public static function booted()
    {
        static::updating(function (self $user) {
            if ($user->isOwner()) {
                $tenant = tenant();

                if (Tenant::where('email', $user->email)->where('id', '!=', $tenant->id)->exists()) {
                    throw new EmailOccupiedException;
                }

                // We update the tenant's email when the admin user's email is updated
                // so that the tenant can find his account even after email change.
                $tenant->update($user->only(['email']));
            }
        });

        static::deleting(function (self $user) {
            if ($user->isOwner()) {
                throw new Exception('Tenant owner cannot be deleted.');
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return list<string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * This is the "organization" owner.
     */
    public function isOwner(): bool
    {
        // We assume the superadmin is the first user in the tenant DB.
        // Feel free to change this logic.
        if (tenant()) {
            return $this->getKey() === User::query()->orderBy('id')->first()->id;
        }

        return false;
    }

    public function getIsOwnerAttribute()
    {
        return $this->isOwner();
    }
}
