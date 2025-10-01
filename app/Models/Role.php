<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Scope untuk filter role berdasarkan status
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter role berdasarkan status
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope untuk search role
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('display_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Get role display name
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ?: $this->name;
    }

    /**
     * Check if role is active
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get users count
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }

    /**
     * Get permissions count
     */
    public function getPermissionsCountAttribute()
    {
        return $this->permissions()->count();
    }
}
