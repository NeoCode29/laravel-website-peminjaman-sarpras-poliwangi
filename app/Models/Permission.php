<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'description',
        'category',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Scope untuk filter permission berdasarkan kategori
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope untuk filter permission berdasarkan status
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk filter permission berdasarkan status
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope untuk search permission
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
     * Get permission display name
     */
    public function getDisplayNameAttribute($value)
    {
        return $value ?: $this->name;
    }

    /**
     * Check if permission is active
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Get roles count
     */
    public function getRolesCountAttribute()
    {
        return $this->roles()->count();
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayAttribute()
    {
        return ucfirst($this->category);
    }
}
