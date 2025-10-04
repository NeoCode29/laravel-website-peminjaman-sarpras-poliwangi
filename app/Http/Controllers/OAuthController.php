<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OAuthToken;

class OAuthController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Redirect to SSO server
     */
    public function redirect()
    {
        $queries = http_build_query([
            'client_id' => config('services.oauth_server.client_id'),
            'redirect_uri' => config('services.oauth_server.redirect'),
            'response_type' => 'code',
        ]);
        
        return redirect(config('services.oauth_server.uri') . '/oauth/authorize?' . $queries);
    }

    /**
     * Handle SSO callback
     */
    public function callback(Request $request)
    {
		$response = Http::withoutVerifying()->post(config('services.oauth_server.uri') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.oauth_server.client_id'),
            'client_secret' => config('services.oauth_server.client_secret'),
            'redirect_uri' => config('services.oauth_server.redirect'),
            'code' => $request->code
        ]);

		$response = $response->json();

		// Pastikan token tersedia sebelum lanjut
		if (!isset($response['access_token'])) {
			return redirect('/login')->withErrors(['oauth' => 'Gagal mendapatkan access token dari SSO server.']);
		}

		try {
			// Proses login/registrasi dan simpan token melalui service (tanpa duplikasi)
			$this->authAfterSso($response);
		} catch (\Throwable $e) {
			return redirect('/login')->withErrors(['oauth' => 'Gagal memproses data SSO.']);
		}

		// Arahkan ke setup profil jika perlu
		if (Auth::check() && !Auth::user()->isProfileCompleted()) {
			return redirect()->route('profile.setup')
				->with('info', 'Silakan lengkapi profile Anda terlebih dahulu.');
		}

		return redirect()->route('dashboard');
    }

    /**
     * Authenticate user after SSO
     */
    protected function authAfterSso($response)
    {
        if (!isset($response['access_token'])) {
            return redirect('/login')->withErrors(['oauth' => 'Access token tidak ditemukan.']);
        }

        // Get user data from SSO server
        $userResponse = Http::withoutVerifying()->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $response['access_token']
        ])->get(config('services.oauth_server.uri') . '/api/user');

        if ($userResponse->status() !== 200) {
            return redirect('/login')->withErrors(['oauth' => 'Gagal mendapatkan data user dari SSO server.']);
        }

        $ssoUser = $userResponse->json();
        
        // Use OAuthService to handle login/registration
        $oauthService = app(\App\Services\OAuthService::class);
        $oauthService->loginOrRegisterFromSso($ssoUser, $response);
    }

    /**
     * Refresh SSO token
     */
    public function refresh(Request $request)
    {
        $response = Http::withoutVerifying()->post(config('services.oauth_server.uri') . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->user()->token->refresh_token,
            'client_id' => config('services.oauth_server.client_id'),
            'client_secret' => config('services.oauth_server.client_secret'),
            'redirect_uri' => config('services.oauth_server.redirect'),
        ]);

        if ($response->status() !== 200) {
            $request->user()->token()->delete();
            return redirect('/login')
                ->withErrors(['oauth' => 'Authorization failed from OAuth server.']);
        }

        $response = $response->json();
        $request->user()->token()->update([
            'access_token' => $response['access_token'],
            'expires_in' => $response['expires_in'],
            'refresh_token' => $response['refresh_token']
        ]);

        return redirect('/dashboard');
    }

	/**
	 * OAuth status informasi sederhana.
	 */
	public function status()
	{
		$authenticated = Auth::check();
		$token = null;
		if ($authenticated && Auth::user()->token) {
			$tokenModel = Auth::user()->token;
			$token = [
				'has_token' => true,
				'expires_at' => optional($tokenModel->created_at)->copy()->addSeconds($tokenModel->expires_in)->toDateTimeString(),
				'valid' => method_exists($tokenModel, 'isValid') ? $tokenModel->isValid() : null,
			];
		}
		return response()->json([
			'authenticated' => $authenticated,
			'token' => $token ?? ['has_token' => false],
		]);
	}

	/**
	 * Endpoint debug sederhana (hanya aktif saat debug).
	 */
	public function debug()
	{
		if (!config('app.debug')) {
			abort(404);
		}
		return response()->json([
			'config' => [
				'sso_enable' => config('services.oauth_server.sso_enable'),
				'uri' => config('services.oauth_server.uri'),
				'redirect' => config('services.oauth_server.redirect'),
			],
		]);
	}

	/**
	 * Generate URL otorisasi OAuth.
	 */
	public function generateAuthUrl()
	{
		$queries = http_build_query([
			'client_id' => config('services.oauth_server.client_id'),
			'redirect_uri' => config('services.oauth_server.redirect'),
			'response_type' => 'code',
		]);
		return response()->json([
			'url' => rtrim(config('services.oauth_server.uri'), '/') . '/oauth/authorize?' . $queries,
		]);
	}

	/**
	 * Endpoint uji callback (placeholder).
	 */
	public function testCallback()
	{
		return response('Test callback endpoint OK', 200);
	}

	/**
	 * Logout dari aplikasi dan opsi logout SSO.
	 */
	public function logout(Request $request)
	{
		if (Auth::check() && Auth::user()->token) {
			Auth::user()->token()->delete();
		}
		Auth::logout();
		$request->session()->invalidate();
		$request->session()->regenerateToken();

		$uriLogout = config('services.oauth_server.uriLogout');
		if ($uriLogout) {
			return redirect($uriLogout);
		}
		return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
	}

}

