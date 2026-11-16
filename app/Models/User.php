<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Поле role навмисно не входить у Fillable: роль адміністратора
 * неможливо отримати через форму реєстрації (FR-09).
 */
#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** Значення за замовчуванням, щоб роль була відома ще до перечитування з БД. */
    protected $attributes = [
        'role' => 'user',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /** Анкети, привʼязані до акаунта (після підтвердження e-mail). */
    public function applications(): HasMany
    {
        return $this->hasMany(AdoptionApplication::class);
    }

    /** Тварини, яких користувач розмістив як волонтер. */
    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class, 'created_by');
    }
}
