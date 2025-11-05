<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\StaffEmployee;
use App\Models\Jurusan;
use App\Models\Prodi;
use App\Models\Unit;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = Auth::user();
        $user->load(['student.jurusan', 'student.prodi', 'staffEmployee.unit', 'staffEmployee.position', 'role']);
        
        return view('profile.show', compact('user'));
    }

    /**
     * Show profile edit form
     */
    public function edit()
    {
        $user = Auth::user();
        $user->load(['student.jurusan', 'student.prodi', 'staffEmployee.unit', 'staffEmployee.position']);
        
        // Get data for dropdowns
        $jurusans = Jurusan::orderBy('nama_jurusan')->get();
        $prodis = Prodi::orderBy('nama_prodi')->get();
        $units = Unit::orderBy('nama')->get();
        $positions = Position::orderBy('nama')->get();
        
        return view('profile.edit', compact('user', 'jurusans', 'prodis', 'units', 'positions'));
    }

    /**
     * Show change password form for local users
     */
    public function changePassword()
    {
        $user = Auth::user();

        if ($user->isSsoUser()) {
            return redirect()->route('profile.show')
                ->with('info', 'Akun SSO dikelola oleh penyedia SSO. Ubah password melalui portal SSO.');
        }

        return view('profile.change-password');
    }

    /**
     * Update authenticated user's password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if ($user->isSsoUser()) {
            return redirect()->route('profile.show')
                ->with('info', 'Password akun SSO tidak dapat diubah dari aplikasi ini.');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'current_password.required' => 'Password lama harus diisi.',
            'password.required' => 'Password baru harus diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                ->withInput($request->except(['current_password', 'password', 'password_confirmation']));
        }

        try {
            $user->updatePassword($request->password);

            Log::info('User password updated', [
                'user_id' => $user->id,
                'updated_by' => $user->id,
                'via' => 'profile',
            ]);

            return redirect()->route('profile.password.edit')
                ->with('success', 'Password berhasil diperbarui.');

        } catch (\Exception $e) {
            Log::error('Failed updating password', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui password. Silakan coba lagi.']);
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|min:10|max:15',
        ], [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah digunakan',
            'phone.required' => 'Nomor handphone harus diisi',
            'phone.min' => 'Nomor handphone minimal 10 digit',
            'phone.max' => 'Nomor handphone maksimal 15 digit',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user basic info
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            // Update specific data based on user type
            if ($user->user_type === 'mahasiswa') {
                $this->updateStudentData($user, $request);
            } elseif ($user->user_type === 'staff') {
                $this->updateStaffData($user, $request);
            }

            DB::commit();

            return redirect()->route('profile.show')
                ->with('success', 'Profil berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show profile setup form
     */
    public function setup()
    {
        try {
            $user = Auth::user();
            
            \Log::info('Profile setup accessed', [
                'user_id' => $user->id,
                'username' => $user->username,
                'profile_completed' => $user->profile_completed,
                'is_profile_completed' => $user->isProfileCompleted(),
                'needs_profile_completion' => $user->needsProfileCompletion(),
                'session_id' => session()->getId(),
                'user_status' => $user->status,
                'user_type' => $user->user_type,
                'is_active' => $user->isActive(),
                'is_blocked' => $user->isBlocked(),
                'can_login' => $user->canLogin()
            ]);
            
            // Redirect if profile already completed
            if ($user->isProfileCompleted()) {
                \Log::info('Profile already completed, redirecting to dashboard');
                return redirect('/')
                    ->with('info', 'Profil Anda sudah lengkap');
            }
            
            // Get data for dropdowns
            $jurusans = Jurusan::orderBy('nama_jurusan')->get();
            $prodis = Prodi::orderBy('nama_prodi')->get();
            $units = Unit::orderBy('nama')->get();
            $positions = Position::orderBy('nama')->get();
            
            \Log::info('Profile setup data loaded successfully');
            
            return view('profile.setup', compact('user', 'jurusans', 'prodis', 'units', 'positions'));
            
        } catch (\Exception $e) {
            \Log::error('Profile setup error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId()
            ]);
            
            return redirect()->route('login')
                ->withErrors(['error' => 'Terjadi kesalahan saat memuat halaman setup profile.']);
        }
    }

    /**
     * Complete profile setup
     */
    public function completeSetup(Request $request)
    {
        $user = Auth::user();
        
        \Log::info('Profile setup completion started', [
            'user_id' => $user->id,
            'username' => $user->username,
            'profile_completed' => $user->profile_completed,
            'session_id' => session()->getId()
        ]);
        
        // Redirect if profile already completed
        if ($user->isProfileCompleted()) {
            \Log::info('Profile already completed, redirecting to dashboard');
            return redirect('/')
                ->with('info', 'Profil Anda sudah lengkap');
        }

        $validator = $this->getSetupValidationRules($request, $user);

        if ($validator->fails()) {
            \Log::info('Profile setup validation failed', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user basic info (only phone, name and email are read-only)
            $user->update([
                'phone' => $request->phone,
            ]);

            // Create specific data based on user type
            if ($user->user_type === 'mahasiswa') {
                $this->createStudentData($user, $request);
            } elseif ($user->user_type === 'staff') {
                $this->createStaffData($user, $request);
            }

            // Mark profile as completed
            $user->markProfileCompleted();
            
            // Refresh user instance to get updated data
            $user->refresh();

            DB::commit();
            
            \Log::info('Profile setup completed successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'profile_completed' => $user->profile_completed,
                'is_profile_completed' => $user->isProfileCompleted(),
                'session_id' => session()->getId()
            ]);

            return redirect('/')
                ->with('success', 'Profil berhasil dilengkapi! Selamat datang di sistem peminjaman sarana dan prasarana.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Profile setup failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat melengkapi profil: ' . $e->getMessage()])
                ->withInput();
        }
    }


    /**
     * Get validation rules for setup
     */
    private function getSetupValidationRules(Request $request, User $user)
    {
        $rules = [
            'phone' => 'required|string|min:10|max:15',
        ];

        $messages = [
            'phone.required' => 'Nomor handphone harus diisi',
            'phone.min' => 'Nomor handphone minimal 10 digit',
            'phone.max' => 'Nomor handphone maksimal 15 digit',
        ];

        if ($user->user_type === 'mahasiswa') {
            $rules['jurusan_id'] = 'required|exists:jurusan,id';
            $rules['prodi_id'] = 'required|exists:prodi,id';
            
            $messages['jurusan_id.required'] = 'Jurusan harus dipilih';
            $messages['jurusan_id.exists'] = 'Jurusan tidak valid';
            $messages['prodi_id.required'] = 'Program studi harus dipilih';
            $messages['prodi_id.exists'] = 'Program studi tidak valid';
        } elseif ($user->user_type === 'staff') {
            $rules['unit_id'] = 'required|exists:units,id';
            $rules['position_id'] = 'required|exists:positions,id';
            $rules['nip'] = 'nullable|string|max:50|unique:staff_employees,nip';
            
            $messages['unit_id.required'] = 'Unit harus dipilih';
            $messages['unit_id.exists'] = 'Unit tidak valid';
            $messages['position_id.required'] = 'Posisi harus dipilih';
            $messages['position_id.exists'] = 'Posisi tidak valid';
            $messages['nip.unique'] = 'NIP sudah digunakan';
        }

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Create student data
     */
    private function createStudentData(User $user, Request $request)
    {
        // Extract angkatan from NIM (digits 3-4)
        $nim = $user->username;
        $angkatan = null;
        if (strlen($nim) >= 4) {
            $angkatanDigits = substr($nim, 2, 2);
            $angkatan = 2000 + (int)$angkatanDigits;
        }

        Student::create([
            'user_id' => $user->id,
            'nim' => $nim,
            'angkatan' => $angkatan,
            'jurusan_id' => $request->jurusan_id,
            'prodi_id' => $request->prodi_id,
            'status_mahasiswa' => 'aktif',
        ]);
    }

    /**
     * Create staff data
     */
    private function createStaffData(User $user, Request $request)
    {
        StaffEmployee::create([
            'user_id' => $user->id,
            'nip' => $request->nip,
            'unit_id' => $request->unit_id,
            'position_id' => $request->position_id,
        ]);
    }

    /**
     * Update student data
     */
    private function updateStudentData(User $user, Request $request)
    {
        if ($user->student) {
            $user->student->update([
                'jurusan_id' => $request->jurusan_id,
                'prodi_id' => $request->prodi_id,
            ]);
        }
    }

    /**
     * Update staff data
     */
    private function updateStaffData(User $user, Request $request)
    {
        if ($user->staffEmployee) {
            $user->staffEmployee->update([
                'nip' => $request->nip,
                'unit_id' => $request->unit_id,
                'position_id' => $request->position_id,
            ]);
        }
    }

    /**
     * Get prodis by jurusan (AJAX)
     */
    public function getProdisByJurusan(Request $request)
    {
        $prodis = Prodi::where('jurusan_id', $request->jurusan_id)
                      ->orderBy('nama_prodi')
                      ->get();
        
        return response()->json($prodis);
    }
}