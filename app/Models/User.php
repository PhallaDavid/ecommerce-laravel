<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Add all profile fields to fillable
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'avatar',
        'images',
        'verify_status',
        'google_id',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'images' => 'array', // cast images as array
    ];

    // Append verify_status to API responses
    protected $appends = ['verify_status'];

    /**
     * Get verification status for frontend
     *
     * @return string
     */
    public function getVerifyStatusAttribute()
    {
        return $this->attributes['verify_status'] ?? 'pending';
    }

    // User's favorites
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // User's cart items
    public function cart()
    {
        return $this->hasMany(\App\Models\Cart::class);
    }

    /**
     * Get all roles for the user
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Get all permissions for the user (through roles)
     */
    public function permissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles()->where('slug', $role)->exists();
        }
        
        if ($role instanceof Role) {
            return $this->roles()->where('roles.id', $role->id)->exists();
        }
        
        return $this->roles()->where('roles.id', $role)->exists();
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        // Check direct permission through roles
        if (is_string($permission)) {
            return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('slug', $permission);
            })->exists();
        }
        
        if ($permission instanceof Permission) {
            return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('permissions.id', $permission->id);
            })->exists();
        }
        
        if (is_numeric($permission)) {
            return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
                $query->where('permissions.id', $permission);
            })->exists();
        }
        
        return false;
    }

    /**
     * Assign role to user
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }
        
        if ($role instanceof Role) {
            $this->roles()->syncWithoutDetaching([$role->id]);
            return true;
        }
        
        if (is_numeric($role)) {
            $this->roles()->syncWithoutDetaching([$role]);
            return true;
        }
        
        return false;
    }

    /**
     * Remove role from user
     */
    public function revokeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }
        
        if ($role instanceof Role) {
            return $this->roles()->detach($role->id);
        }
        
        if (is_numeric($role)) {
            return $this->roles()->detach($role);
        }
        
        return false;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all given roles
     */
    public function hasAllRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        
        return true;
    }
}
