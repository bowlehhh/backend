<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_ADMIN_BESAR = 'admin_besar';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

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
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function isAdmin(): bool
    {
        return strtolower((string) $this->role) === self::ROLE_ADMIN;
    }

    public function isAdminBesar(): bool
    {
        return strtolower((string) $this->role) === self::ROLE_ADMIN_BESAR;
    }

    public function setRoleAttribute(mixed $value): void
    {
        $normalized = strtolower(trim((string) $value));

        $this->attributes['role'] = match ($normalized) {
            'adminbesar', 'admin-besar', 'utang_piutang', 'cashier' => self::ROLE_ADMIN_BESAR,
            default => $normalized,
        };
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin'
            && $this->is_active
            && $this->isAdmin();
    }
}
