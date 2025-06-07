<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $full_name
 * @property string $email
 * @property string $password
 * @property bool $is_active
 * @property string $address
 * @property Carbon $email_verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $phone
 * @property string|null $avatar_path
 * @property Payment[] $payments
 * @property Certificate[] $certificates
 */
class User extends Authenticatable implements MustVerifyEmail, OAuthenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'email',
        'password',
        'is_active',
        'address',
        'phone',
        'avatar_path',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Send the email verification notification (queued).
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPassword($token));
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
