<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
            'role' => UserRole::class,
        ];
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole(UserRole::Superadmin);
    }

    public function isDoctor(): bool
    {
        return $this->hasRole(UserRole::Doctor);
    }

    public function hasPhoto(): bool
    {
        return $this->photo_path !== null && $this->photo_path !== '';
    }

    /**
     * Iniciales para el avatar de reserva cuando el usuario no tiene foto de perfil.
     */
    protected function initials(): Attribute
    {
        return Attribute::make(get: function () {
            $words = preg_split('/\s+/', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if ($words === []) {
                return '';
            }

            $first = Str::substr($words[0], 0, 1);
            $last = count($words) > 1 ? Str::substr(end($words), 0, 1) : '';

            return Str::upper($first.$last);
        });
    }
}
