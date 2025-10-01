<?php

namespace App\Services;

use App\Models\User;
use App\Models\OAuthToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OAuthService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('services.oauth_server');
    }

    /**
     * Check if SSO is enabled.
     */
    public function isEnabled()
    {
        return $this->config['sso_enable'] === true || $this->config['sso_enable'] === 'true';
    }

    /**
     * Handle login or registration from SSO data.
     */
    public function loginOrRegisterFromSso($ssoUser, $response)
    {
        // Validate required SSO data
        if (empty($ssoUser['username']) || empty($ssoUser['name'])) {
            throw new \Exception('Required SSO data (username, name) is missing');
        }

        $ssoId = $ssoUser['id'] ?? $ssoUser['username'];
        $userType = $this->determineUserType($ssoUser);

        // Check if user exists by SSO ID
        $user = User::where('sso_id', $ssoId)->first();

        if (!$user) {
            // Check if user exists by username or email
            $user = User::where('username', $ssoUser['username'])
                       ->orWhere('email', $ssoUser['email'] ?? '')
                       ->first();
        }

        $userData = [
            'username' => $ssoUser['username'],
            'name' => $ssoUser['name'],
            'email' => $ssoUser['email'] ?? null,
            'sso_id' => $ssoId,
            'sso_provider' => 'poliwangi',
            'sso_data' => json_encode($ssoUser),
            'user_type' => $userType,
            'status' => 'active',
            'last_sso_login' => now(),
        ];

        if ($user) {
            // Update existing user
            $user->update($userData);
            
            // Ensure user has correct role based on SSO data
            $this->assignDefaultRole($user, $ssoUser);
            
            Log::info('User updated from SSO', [
                'user_id' => $user->id,
                'username' => $user->username,
                'sso_id' => $ssoId
            ]);
        } else {
            // Create new user
            $userData['password'] = Hash::make(Str::random(32)); // Random password for SSO users
            $userData['profile_completed'] = false;
            $userData['password_changed_at'] = now();
            
            $user = User::create($userData);
            
            // Assign default role
            $this->assignDefaultRole($user, $ssoUser);
            
            Log::info('User created from SSO', [
                'user_id' => $user->id,
                'username' => $user->username,
                'sso_id' => $ssoId
            ]);
        }

        // Login user
        Auth::login($user);

        // Store OAuth token
        $this->storeToken($user, $response);

        return $user;
    }

    /**
     * Store OAuth token for user.
     */
    public function storeToken($user, $tokenData)
    {
        // Delete existing token
        OAuthToken::where('user_id', $user->id)->delete();
        
        // Create new token
        OAuthToken::create([
            'user_id' => $user->id,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_in' => $tokenData['expires_in'],
            'token_type' => 'Bearer',
            'scope' => 'openid profile email',
        ]);
    }

    /**
     * Determine user type from SSO data.
     */
    protected function determineUserType($ssoData)
    {
        if (isset($ssoData['staff'])) {
            switch ($ssoData['staff']) {
                case 999:
                    return 'mahasiswa';
                case 3: // PLP (Pranata Laboratorium Pendidikan)
                case 4: // Dosen
                case 0: // Admin
                    return 'staff';
                default:
                    return 'mahasiswa';
            }
        }

        // Fallback berdasarkan email domain
        if (isset($ssoData['email']) && strpos($ssoData['email'], '@student.') !== false) {
            return 'mahasiswa';
        }

        return 'mahasiswa'; // Default
    }

    /**
     * Assign default role to user based on SSO data.
     */
    protected function assignDefaultRole($user, $ssoData)
    {
        try {
            $roleName = $this->getRoleNameFromSSO($ssoData);
            $role = \Spatie\Permission\Models\Role::where('name', $roleName)->first();
            
            if ($role) {
                $user->assignRole($role);
                // Set role_id di database
                $user->role_id = $role->id;
                $user->save();
                
                Log::info('Role assigned to SSO user', [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'role_name' => $roleName
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to assign role to SSO user', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get role name from SSO data.
     * Semua user SSO baru selalu mendapat role peminjam.
     */
    protected function getRoleNameFromSSO($ssoData)
    {
        // Semua user SSO baru selalu mendapat role peminjam
        return 'peminjam';
    }
}

