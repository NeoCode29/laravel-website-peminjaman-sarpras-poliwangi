<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'address',
        'bio',
        'user_type',
        'status',
        'role_id',
        'profile_completed',
        'profile_completed_at',
        'blocked_until',
        'sso_id',
        'sso_provider',
        'sso_data',
        'last_sso_login',
        'last_login_at',
        'password_changed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'profile_completed_at' => 'datetime',
        'blocked_until' => 'datetime',
        'last_sso_login' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'sso_data' => 'array',
        'profile_completed' => 'boolean',
    ];

    /**
     * Relasi ke Role (Single Role Approach)
     */
    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked()
    {
        return $this->status === 'blocked' || 
               ($this->blocked_until && $this->blocked_until->isFuture());
    }

    /**
     * Check if user profile is completed
     */
    public function isProfileCompleted()
    {
        // Check if profile_completed is explicitly true AND profile_completed_at is set
        return $this->profile_completed === true && !is_null($this->profile_completed_at);
    }

    /**
     * Check if user is active
     */
    public function isActive()
    {
        return $this->status === 'active' && !$this->isBlocked();
    }

    /**
     * Get user type display name
     */
    public function getUserTypeDisplayAttribute()
    {
        return $this->user_type === 'mahasiswa' ? 'Mahasiswa' : 'Staff';
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute()
    {
        $statuses = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'blocked' => 'Diblokir'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Scope untuk filter user berdasarkan status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope untuk filter user berdasarkan tipe
     */
    public function scopeByType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Scope untuk filter user berdasarkan role
     */
    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Scope untuk search user
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    /**
     * Get the identifier name for authentication
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Check if user can login (not blocked and active)
     */
    public function canLogin()
    {
        return $this->isActive() && !$this->isBlocked();
    }

    /**
     * Check if user needs to complete profile
     */
    public function needsProfileCompletion()
    {
        // If profile_completed is explicitly false, user needs completion
        if ($this->profile_completed === false) {
            return true;
        }
        
        // If profile_completed is null (new user), user needs completion
        if ($this->profile_completed === null) {
            return true;
        }
        
        // If profile_completed is true, check if profile is actually completed
        if ($this->profile_completed === true) {
            return !$this->isProfileCompleted();
        }
        
        // Default to needing completion
        return true;
    }

    /**
     * Mark profile as completed
     */
    public function markProfileCompleted()
    {
        $this->update([
            'profile_completed' => true,
            'profile_completed_at' => now()
        ]);
        
        // Refresh the model to get updated attributes
        $this->refresh();
    }

    /**
     * Update password and set password_changed_at
     */
    public function updatePassword($password)
    {
        $this->update([
            'password' => bcrypt($password),
            'password_changed_at' => now()
        ]);
    }

    /**
     * Check if user is SSO user
     */
    public function isSsoUser()
    {
        return !is_null($this->sso_id);
    }

    /**
     * Get user's permissions through role
     */
    public function getPermissions()
    {
        if ($this->role) {
            return $this->role->permissions;
        }
        return collect();
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        if ($this->role) {
            return $this->role->hasPermissionTo($permission);
        }
        return false;
    }

    /**
     * Get user's role name
     */
    public function getRoleName()
    {
        return $this->role ? $this->role->name : 'No Role';
    }

    /**
     * Get user's role display name
     */
    public function getRoleDisplayName()
    {
        return $this->role ? $this->role->display_name : 'Tidak Ada Role';
    }

    /**
     * Relasi ke Student (One-to-One)
     */
    public function student()
    {
        return $this->hasOne(\App\Models\Student::class);
    }

    /**
     * Relasi ke StaffEmployee (One-to-One)
     */
    public function staffEmployee()
    {
        return $this->hasOne(\App\Models\StaffEmployee::class);
    }

    /**
     * Relasi ke OAuthToken (One-to-Many)
     */
    public function token()
    {
        return $this->hasOne(OAuthToken::class);
    }

    /**
     * Get user's specific data based on user_type
     */
    public function getSpecificData()
    {
        if ($this->user_type === 'mahasiswa') {
            return $this->student;
        } elseif ($this->user_type === 'staff') {
            return $this->staffEmployee;
        }
        return null;
    }

    /**
     * Check if user has specific data
     */
    public function hasSpecificData()
    {
        return $this->getSpecificData() !== null;
    }

    /**
     * Reset failed login attempts.
     */
    public function resetFailedLoginAttempts(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    /**
     * Increment login count.
     */
    public function incrementLoginCount(): void
    {
        $this->increment('login_count');
    }

    /**
     * Update last activity timestamp.
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Get user type for compatibility
     */
    public function getUserType()
    {
        return $this->user_type;
    }

    /**
     * Check if user is a student (user_peminjam).
     */
    public function isStudent(): bool
    {
        return $this->user_type === 'mahasiswa';
    }

    /**
     * Check if user is employee (administrator).
     */
    public function isEmployee(): bool
    {
        return $this->user_type === 'staff';
    }

    /**
     * Check if user is admin (administrator).
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'staff';
    }

    /**
     * Increment failed login attempts.
     */
    public function incrementFailedLoginAttempts(): void
    {
        $this->increment('failed_login_attempts');
        
        // Lock account after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->update([
                'locked_until' => now()->addMinutes(30),
                'blocked_reason' => 'Terlalu banyak percobaan login gagal'
            ]);
        }
    }
}
