<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all permissions for this role
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Get all users with this role
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission($permission)
    {
        if (is_string($permission)) {
            return $this->permissions()->where('slug', $permission)->exists();
        }
        
        if ($permission instanceof Permission) {
            return $this->permissions()->where('permissions.id', $permission->id)->exists();
        }
        
        return $this->permissions()->where('permissions.id', $permission)->exists();
    }

    /**
     * Assign permission to role
     */
    public function assignPermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }
        
        if ($permission instanceof Permission) {
            $this->permissions()->syncWithoutDetaching([$permission->id]);
            return true;
        }
        
        if (is_numeric($permission)) {
            $this->permissions()->syncWithoutDetaching([$permission]);
            return true;
        }
        
        return false;
    }

    /**
     * Remove permission from role
     */
    public function revokePermission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->first();
        }
        
        if ($permission instanceof Permission) {
            return $this->permissions()->detach($permission->id);
        }
        
        if (is_numeric($permission)) {
            return $this->permissions()->detach($permission);
        }
        
        return false;
    }
}
