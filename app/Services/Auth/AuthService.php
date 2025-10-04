<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use App\Models\User;

class AuthService
{

    /**
     * Handle login request.
     */
    public function login($request): RedirectResponse
    {
        try {
            $credentials = $request->only('username', 'password');
            $remember = $request->boolean('remember');
            
            // Try to authenticate with username first, then email
            $user = $this->authenticateWithUsernameOrEmail($credentials, $remember);
            
            // Check if user is blocked
            if (method_exists($user, 'isBlocked') && $user->isBlocked()) {
                throw new \Illuminate\Auth\AuthenticationException('Akun Anda diblokir. Silakan hubungi administrator.');
            }

            // Check if user has too many failed login attempts
            if (isset($user->failed_login_attempts) && $user->failed_login_attempts >= 5 && 
                isset($user->locked_until) && $user->locked_until && $user->locked_until->isFuture()) {
                throw new \Illuminate\Auth\AuthenticationException('Akun Anda terkunci karena terlalu banyak percobaan login gagal.');
            }

            // Perform post-login actions
            $this->postLoginActions($user);
            
            
            // Ensure user has a role based on user_type
            if (method_exists($user, 'roles') && $user->roles->isEmpty()) {
                $this->assignDefaultRole($user);
            }
            
            // Check if profile needs completion - ALWAYS redirect to profile setup if not completed
            if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
                \Log::info('Redirecting to profile setup', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'profile_completed' => $user->profile_completed,
                    'profile_completed_at' => $user->profile_completed_at,
                    'session_id' => session()->getId()
                ]);
                return redirect()->route('profile.setup')
                    ->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu sebelum melanjutkan.');
            }
            
            \Log::info('Redirecting to dashboard', [
                'user_id' => $user->id,
                'username' => $user->username,
                'profile_completed' => $user->profile_completed,
                'session_id' => session()->getId(),
                'intended_url' => session('url.intended')
            ]);
            
            // Use intended() with fallback to dashboard
            return redirect()->intended('/dashboard');
            
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return redirect()->back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => $e->getMessage()]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => 'Terjadi kesalahan saat login. Silakan coba lagi.']);
        }
    }

    /**
     * Authenticate user with username or email.
     */
    private function authenticateWithUsernameOrEmail(array $credentials, bool $remember = false)
    {
        $username = $credentials['username'];
        $password = $credentials['password'];
        
        // Try username first
        $user = User::where('username', $username)->first();
        if ($user && \Hash::check($password, $user->password)) {
            // Login langsung seperti SSO - tanpa mengubah session state
            Auth::login($user, $remember);
            return $user;
        }
        
        // Try email
        $user = User::where('email', $username)->first();
        if ($user && \Hash::check($password, $user->password)) {
            // Login langsung seperti SSO - tanpa mengubah session state
            Auth::login($user, $remember);
            return $user;
        }
        
        throw new \Illuminate\Auth\AuthenticationException('Username/email atau password salah.');
    }

    /**
     * Perform actions after successful login.
     */
    private function postLoginActions($user): void
    {
        // Reset failed login attempts
        if (method_exists($user, 'resetFailedLoginAttempts')) {
            $user->resetFailedLoginAttempts();
        }
        if (method_exists($user, 'incrementLoginCount')) {
            $user->incrementLoginCount();
        }
        if (method_exists($user, 'updateLastActivity')) {
            $user->updateLastActivity();
        }


        // Log successful login
        $this->logAuthAction($user, 'login', 'User berhasil login');
    }

    /**
     * Logout user and log the action.
     */
    public function logout($request): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user) {
            $this->logAuthAction($user, 'logout', 'User berhasil logout');
        }

        Auth::logout();
        
        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }



    /**
     * Assign default role based on user type.
     */
    private function assignDefaultRole($user): void
    {
        if (method_exists($user, 'getUserType')) {
            $role = match($user->getUserType()) {
                'mahasiswa' => 'peminjam',
                'staff' => 'admin',
                default => 'peminjam'
            };
            
            // Check if role exists before assigning
            $roleModel = \Spatie\Permission\Models\Role::where('name', $role)->first();
            if ($roleModel && method_exists($user, 'assignRole')) {
                $user->assignRole($roleModel);
                $user->role_id = $roleModel->id;
                $user->save();
            }
        }
    }

    /**
     * Handle OAuth login (Google, etc.).
     */
    public function handleOAuthLogin($provider, $userData): RedirectResponse
    {
        try {
            // Find existing user by email
            $user = User::where('email', $userData['email'])->first();
            
            if (!$user) {
                // Create new user from OAuth data
                $user = $this->createUserFromOAuth($provider, $userData);
            }
            
            // Check if user is blocked
            if (method_exists($user, 'isBlocked') && $user->isBlocked()) {
                throw new \Illuminate\Auth\AuthenticationException('Akun Anda diblokir. Silakan hubungi administrator.');
            }
            
            // Login user
            Auth::login($user);
            
            
            // Assign default role if needed
            if (method_exists($user, 'roles') && $user->roles->isEmpty()) {
                $this->assignDefaultRole($user);
            }
            
            // Log successful OAuth login
            $this->logAuthAction($user, 'oauth_login', "OAuth login via {$provider}");
            
            // Check if profile needs completion - ALWAYS redirect to profile setup if not completed
            if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
                return redirect()->route('profile.setup')
                    ->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu sebelum melanjutkan.');
            }
            
            return redirect()->intended('/dashboard');
            
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Terjadi kesalahan saat login dengan ' . ucfirst($provider) . '.']);
        }
    }

    /**
     * Create user from OAuth data.
     */
    private function createUserFromOAuth(string $provider, array $userData)
    {
        $userType = $this->determineUserTypeFromOAuth($userData);
        
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'username' => $this->generateUsernameFromEmail($userData['email']),
            'password' => bcrypt(str()->random(16)), // Random password for OAuth users
            'user_type' => $userType, // Use the determined user type
            'status' => 'active', // Use string 'active' for status
            'avatar' => $userData['avatar'] ?? 'avatar.png',
            'email_verified_at' => now(),
            'profile_completed' => false, // OAuth users need to complete profile
        ]);
        
        // Assign default role for OAuth user
        $this->assignDefaultRole($user);
        
        return $user;
    }

    /**
     * Determine user type from OAuth data.
     */
    private function determineUserTypeFromOAuth(array $userData): string
    {
        // Check if email domain suggests student or employee
        $email = $userData['email'];
        
        // If email contains student domain or pattern
        if (str_contains($email, '@student.') || str_contains($email, '@mahasiswa.')) {
            return 'mahasiswa';
        }
        
        // If email contains institutional domain (Poliwangi)
        if (str_contains($email, '@poltek.') || str_contains($email, '@poliwangi.')) {
            return 'staff';
        }
        
        // Check username pattern for Poliwangi SSO
        $username = $userData['username'] ?? '';
        if (preg_match('/^\d{10}$/', $username)) {
            // 10 digit username usually indicates student NIM
            return 'mahasiswa';
        }
        
        // Default to mahasiswa for institutional users
        return 'mahasiswa';
    }

    /**
     * Generate username from email.
     */
    private function generateUsernameFromEmail(string $email): string
    {
        $username = explode('@', $email)[0];
        
        // Ensure username is unique
        $originalUsername = $username;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Log authentication action.
     */
    private function logAuthAction($user, string $action, string $description): void
    {
        try {
            if (class_exists('\App\Models\Core\AuditLog') && method_exists($user, 'id')) {
                \App\Models\Core\AuditLog::create([
                    'user_id' => $user->id,
                    'action_type' => 'auth.' . $action,
                    'action_description' => $description,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'session_id' => session()->getId(),
                ]);
            }
        } catch (\Exception $e) {
            // Ignore audit log errors
        }
    }
}
