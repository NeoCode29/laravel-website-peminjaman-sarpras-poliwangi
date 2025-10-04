<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user.view')->only(['index', 'show']);
        $this->middleware('permission:user.create')->only(['create', 'store']);
        $this->middleware('permission:user.edit')->only(['edit', 'update']);
        $this->middleware('permission:user.delete')->only(['destroy']);
        $this->middleware('permission:user.block')->only(['block', 'unblock']);
        $this->middleware('permission:user.role_edit')->only(['updateRole']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tipe user
        if ($request->filled('user_type')) {
            $query->byType($request->user_type);
        }

        // Filter berdasarkan role
        if ($request->filled('role_id') && is_numeric($request->role_id)) {
            $query->byRole($request->role_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $users = $query->paginate(15);
        $roles = Role::where('is_active', true)->orderBy('display_name')->get();

        return view('user-management.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::where('is_active', true)->get();
        
        // Load data untuk dropdown mahasiswa
        $jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
        $prodi = \App\Models\Prodi::orderBy('nama_prodi')->get();
        
        // Load data untuk dropdown staff
        $units = \App\Models\Unit::orderBy('nama')->get();
        $positions = \App\Models\Position::orderBy('nama')->get();
        
        return view('user-management.create', compact('roles', 'jurusan', 'prodi', 'units', 'positions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|min:10|max:15',
            'user_type' => 'required|in:mahasiswa,staff',
            'status' => 'required|in:active,inactive',
            'role_id' => 'required|exists:roles,id',
        ];

        // Add validation rules based on user type
        if ($request->user_type === 'mahasiswa') {
            $rules['nim'] = 'required|string|max:20|unique:students,nim';
            $rules['angkatan'] = 'required|integer|min:2000|max:2030';
            $rules['jurusan_id'] = 'required|exists:jurusan,id';
            $rules['prodi_id'] = 'required|exists:prodi,id';
        } elseif ($request->user_type === 'staff') {
            $rules['nip'] = 'nullable|string|min:8|max:20|unique:staff_employees,nip';
            $rules['unit_id'] = 'required|exists:units,id';
            $rules['position_id'] = 'required|exists:positions,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'user_type' => $request->user_type,
                'status' => $request->status,
                'role_id' => $request->role_id,
                'profile_completed' => true,
                'profile_completed_at' => now(),
                'password_changed_at' => now(),
            ]);

            // Create specific data based on user type
            if ($request->user_type === 'mahasiswa') {
                \App\Models\Student::create([
                    'user_id' => $user->id,
                    'nim' => $request->nim,
                    'angkatan' => $request->angkatan,
                    'jurusan_id' => $request->jurusan_id,
                    'prodi_id' => $request->prodi_id,
                ]);
            } elseif ($request->user_type === 'staff') {
                \App\Models\StaffEmployee::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'unit_id' => $request->unit_id,
                    'position_id' => $request->position_id,
                ]);
            }

            DB::commit();

            return redirect()->route('user-management.index')
                ->with('success', 'User berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat membuat user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Muat relasi yang dibutuhkan untuk halaman detail user
        $user = User::with(['role.permissions', 'student', 'staffEmployee'])->findOrFail($id);
        return view('user-management.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::with(['role', 'student', 'staffEmployee'])->findOrFail($id);
        $roles = Role::where('is_active', true)->get();
        
        // Load data untuk dropdown mahasiswa
        $jurusan = \App\Models\Jurusan::orderBy('nama_jurusan')->get();
        $prodi = \App\Models\Prodi::orderBy('nama_prodi')->get();
        
        // Load data untuk dropdown staff
        $units = \App\Models\Unit::orderBy('nama')->get();
        $positions = \App\Models\Position::orderBy('nama')->get();
        
        return view('user-management.edit', compact('user', 'roles', 'jurusan', 'prodi', 'units', 'positions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id)
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'phone' => 'required|string|min:10|max:15',
            'user_type' => 'required|in:mahasiswa,staff',
            'status' => 'required|in:active,inactive,blocked',
            'role_id' => 'required|exists:roles,id',
        ];

        // Add validation rules based on user type
        if ($request->user_type === 'mahasiswa') {
            $rules['nim'] = 'required|string|max:20|unique:students,nim,' . ($user->student ? $user->student->id : 'NULL') . ',id';
            $rules['angkatan'] = 'required|integer|min:2000|max:2030';
            $rules['jurusan_id'] = 'required|exists:jurusan,id';
            $rules['prodi_id'] = 'required|exists:prodi,id';
        } elseif ($request->user_type === 'staff') {
            $rules['nip'] = 'nullable|string|min:8|max:20|unique:staff_employees,nip,' . ($user->staffEmployee ? $user->staffEmployee->id : 'NULL') . ',id';
            $rules['unit_id'] = 'required|exists:units,id';
            $rules['position_id'] = 'required|exists:positions,id';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user
            $user->update([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'user_type' => $request->user_type,
                'status' => $request->status,
                'role_id' => $request->role_id,
            ]);

            // Sync Spatie role pivot agar konsisten dengan role_id
            if ($request->filled('role_id')) {
                $roleModel = \Spatie\Permission\Models\Role::find($request->role_id);
                if ($roleModel) {
                    $user->syncRoles([$roleModel]);
                    // Invalidate permission cache
                    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                }
            }

            // Update or create specific data based on user type
            if ($request->user_type === 'mahasiswa') {
                if ($user->student) {
                    $user->student->update([
                        'nim' => $request->nim,
                        'angkatan' => $request->angkatan,
                        'jurusan_id' => $request->jurusan_id,
                        'prodi_id' => $request->prodi_id,
                    ]);
                } else {
                    \App\Models\Student::create([
                        'user_id' => $user->id,
                        'nim' => $request->nim,
                        'angkatan' => $request->angkatan,
                        'jurusan_id' => $request->jurusan_id,
                        'prodi_id' => $request->prodi_id,
                    ]);
                }
                
                // Delete staff data if exists
                if ($user->staffEmployee) {
                    $user->staffEmployee->delete();
                }
            } elseif ($request->user_type === 'staff') {
                if ($user->staffEmployee) {
                    $user->staffEmployee->update([
                        'nip' => $request->nip,
                        'unit_id' => $request->unit_id,
                        'position_id' => $request->position_id,
                    ]);
                } else {
                    \App\Models\StaffEmployee::create([
                        'user_id' => $user->id,
                        'nip' => $request->nip,
                        'unit_id' => $request->unit_id,
                        'position_id' => $request->position_id,
                    ]);
                }
                
                // Delete student data if exists
                if ($user->student) {
                    $user->student->delete();
                }
            }

            DB::commit();

            return redirect()->route('user-management.index')
                ->with('success', 'User berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui user: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Check if user can be deleted (optional business logic)
            if ($user->id === auth()->id()) {
                return redirect()->route('user-management.index')
                    ->with('error', 'Tidak dapat menghapus akun sendiri.');
            }
            
            // Soft delete or hard delete based on business requirements
            $user->delete();
            
            return redirect()->route('user-management.index')
                ->with('success', 'User berhasil dihapus.');
                
        } catch (\Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage());
            
            return redirect()->route('user-management.index')
                ->with('error', 'Gagal menghapus user. Silakan coba lagi.');
        }
    }

    /**
     * Block user
     */
    public function block(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'block_duration' => 'required|integer|min:1|max:30'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::findOrFail($id);
        
        // Check if user can be blocked
        if ($user->id === auth()->id()) {
            return redirect()->route('user-management.index')
                ->with('error', 'Tidak dapat memblokir akun sendiri.');
        }

        $blockDuration = $request->block_duration;
        
        $user->update([
            'status' => 'blocked',
            'blocked_until' => now()->addDays($blockDuration)
        ]);

        return redirect()->route('user-management.index')
            ->with('success', "User berhasil diblokir selama {$blockDuration} hari.");
    }

    /**
     * Unblock user
     */
    public function unblock($id)
    {
        $user = User::findOrFail($id);
        
        // Check if user is actually blocked
        if (!$user->isBlocked()) {
            return redirect()->route('user-management.index')
                ->with('error', 'User tidak dalam status diblokir.');
        }
        
        $user->update([
            'status' => 'active',
            'blocked_until' => null
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User berhasil diaktifkan kembali.');
    }

    /**
     * Update user role
     */
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update(['role_id' => $request->role_id]);

        // Sinkronkan juga Spatie roles agar efektif di permission checks
        $roleModel = \Spatie\Permission\Models\Role::find($request->role_id);
        if ($roleModel) {
            $user->syncRoles([$roleModel]);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        return redirect()->route('user-management.index')
            ->with('success', 'Role user berhasil diperbarui.');
    }
}
