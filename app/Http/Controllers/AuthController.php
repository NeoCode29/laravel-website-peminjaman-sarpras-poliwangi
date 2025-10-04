<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Auth\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        return $this->authService->login($request);
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect('/');
        }
        
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        // Rate limiting untuk registrasi
        $key = 'register.' . $request->ip();
        $maxAttempts = 3;
        $decayMinutes = 60;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            Log::warning('Registration rate limit exceeded', [
                'ip' => $request->ip(),
                'seconds_remaining' => $seconds
            ]);
            
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan registrasi. Coba lagi dalam " . ceil($seconds / 60) . " menit."
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|min:2',
            'username' => 'required|string|max:255|min:3|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'user_type' => 'required|in:mahasiswa,staff',
            'phone' => 'required|string|min:10|max:15|regex:/^[0-9+\-\s()]+$/',
        ], [
            'name.required' => 'Nama lengkap harus diisi',
            'name.min' => 'Nama minimal 2 karakter',
            'username.required' => 'Username harus diisi',
            'username.min' => 'Username minimal 3 karakter',
            'username.unique' => 'Username sudah digunakan',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, dan underscore',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka',
            'user_type.required' => 'Tipe user harus dipilih',
            'user_type.in' => 'Tipe user tidak valid',
            'phone.required' => 'Nomor handphone harus diisi',
            'phone.min' => 'Nomor handphone minimal 10 digit',
            'phone.max' => 'Nomor handphone maksimal 15 digit',
            'phone.regex' => 'Format nomor handphone tidak valid',
        ]);

        if ($validator->fails()) {
            RateLimiter::hit($key, $decayMinutes * 60);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => trim($request->name),
                'username' => strtolower(trim($request->username)),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'user_type' => $request->user_type,
                'phone' => preg_replace('/[^0-9]/', '', $request->phone), // Hanya angka
                'status' => 'active',
                'profile_completed' => false,
                'password_changed_at' => now(),
            ]);

            // Assign default role (peminjam)
            $peminjamRole = \Spatie\Permission\Models\Role::where('name', 'peminjam')->first();
            if ($peminjamRole) {
                $user->assignRole($peminjamRole);
                $user->role_id = $peminjamRole->id;
                $user->save();
            }

            // Clear rate limiting setelah registrasi berhasil
            RateLimiter::clear($key);

            // Login user
            Auth::login($user);
            
            // Regenerate session ID for security
            $request->session()->regenerate();

            Log::info('User registered and logged in', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'session_id' => session()->getId(),
                'ip' => $request->ip()
            ]);

            DB::commit();

            return redirect()->route('profile.setup')
                ->with('success', 'Registrasi berhasil! Selamat datang, ' . $user->name . '! Silakan lengkapi profil Anda.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'username' => $request->username,
                'email' => $request->email,
                'ip' => $request->ip()
            ]);

            return redirect()->back()
                ->withErrors(['email' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        return $this->authService->logout($request);
    }

    /**
     * Redirect to OAuth provider (default to poliwangi).
     */
    public function redirectToProvider(string $provider = 'poliwangi'): RedirectResponse
    {
        try {
            if ($provider === 'poliwangi') {
                // Handle SSO Poliwangi
                $ssoUrl = config('services.oauth_server.uri');
                $clientId = config('services.oauth_server.client_id');
                $redirectUri = config('services.oauth_server.redirect');
                
                if (!$ssoUrl || !$clientId || !$redirectUri) {
                    throw new \Exception('SSO Poliwangi tidak dikonfigurasi dengan benar.');
                }
                
                $params = http_build_query([
                    'client_id' => $clientId,
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'scope' => 'openid profile email',
                    'state' => csrf_token(),
                ]);
                
                return redirect($ssoUrl . '?' . $params);
            }
            
            // Handle other OAuth providers (if needed in future)
            return Socialite::driver($provider)->redirect();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Provider OAuth tidak tersedia: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle OAuth callback (default to poliwangi).
     */
    public function handleProviderCallback(string $provider = 'poliwangi'): RedirectResponse
    {
        try {
            if ($provider === 'poliwangi') {
                // Handle SSO Poliwangi callback
                $code = request('code');
                $state = request('state');
                
                if (!$code) {
                    throw new \Exception('Authorization code tidak ditemukan.');
                }
                
                // Verify state parameter
                if ($state !== csrf_token()) {
                    throw new \Exception('Invalid state parameter.');
                }
                
                // Exchange code for access token
                $userData = $this->exchangeCodeForUserData($code);
                
                return $this->authService->handleOAuthLogin($provider, $userData);
            }
            
            // Handle other OAuth providers
            $userData = Socialite::driver($provider)->user();
            
            $userInfo = [
                'name' => $userData->getName(),
                'email' => $userData->getEmail(),
                'avatar' => $userData->getAvatar(),
            ];
            
            return $this->authService->handleOAuthLogin($provider, $userInfo);
            
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Terjadi kesalahan saat login dengan ' . ucfirst($provider) . ': ' . $e->getMessage()]);
        }
    }

    /**
     * Exchange authorization code for user data from SSO Poliwangi.
     */
    private function exchangeCodeForUserData(string $code): array
    {
        $tokenUrl = config('services.oauth_server.uri') . '/oauth/token';
        $clientId = config('services.oauth_server.client_id');
        $clientSecret = config('services.oauth_server.client_secret');
        $redirectUri = config('services.oauth_server.redirect');
        
        // Exchange code for access token
        $response = \Http::asForm()->post($tokenUrl, [
            'grant_type' => 'authorization_code',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ]);
        
        if (!$response->successful()) {
            throw new \Exception('Gagal mendapatkan access token: ' . $response->body());
        }
        
        $tokenData = $response->json();
        $accessToken = $tokenData['access_token'];
        
        // Get user info
        $userInfoUrl = config('services.oauth_server.uri') . '/api/user';
        $userResponse = \Http::withToken($accessToken)->get($userInfoUrl);
        
        if (!$userResponse->successful()) {
            throw new \Exception('Gagal mendapatkan data user: ' . $userResponse->body());
        }
        
        $userData = $userResponse->json();
        
        return [
            'name' => $userData['name'] ?? $userData['username'] ?? 'User',
            'email' => $userData['email'] ?? '',
            'avatar' => $userData['avatar'] ?? 'avatar.png',
            'username' => $userData['username'] ?? $userData['email'] ?? '',
        ];
    }

    /**
     * Refresh OAuth token (placeholder method).
     */
    public function refresh(): RedirectResponse
    {
        return redirect()->route('login')
            ->withErrors(['oauth' => 'Fitur refresh token belum tersedia.']);
    }


    /**
     * Show account security page
     */
    public function showSecurityPage()
    {
        $user = Auth::user();
        
        return view('auth.security', compact('user'));
    }

    /**
     * Get login attempts for current user
     */
    public function getLoginAttempts()
    {
        $user = Auth::user();
        
        // Get recent login attempts (last 30 days)
        $attempts = DB::table('login_attempts')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($attempts);
    }

    /**
     * Check if user can perform action (rate limiting check)
     */
    public function checkRateLimit(Request $request)
    {
        $action = $request->input('action', 'login');
        $key = $action . '.' . $request->ip();
        
        $attempts = RateLimiter::attempts($key);
        $remaining = RateLimiter::remaining($key, 5);
        $availableIn = RateLimiter::availableIn($key);
        
        return response()->json([
            'attempts' => $attempts,
            'remaining' => $remaining,
            'available_in' => $availableIn,
            'can_attempt' => $remaining > 0
        ]);
    }

}