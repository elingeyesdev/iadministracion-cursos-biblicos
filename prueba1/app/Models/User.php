<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;
    

    public function isAdmin(): bool
    {
        return $this->esAdmin();
    }

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
        ];
    }

    // Perfil extendido 1:1
    public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne {
        return $this->hasOne(UserProfile::class);
    }

    // Roles N:M
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('fecha_asignacion', 'estado')
            ->withTimestamps();
    }

    // Helpers
    public function tieneRol(string $nombre): bool {
        return $this->roles()
            ->where('nombre', $nombre)
            ->where('role_user.estado', 'activo')
            ->exists();
    }

    public function esAdmin(): bool {
        return $this->tieneRol('Administrador');
    }
}