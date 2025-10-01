<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan tipe user
        if ($request->has('user_type') && $request->user_type !== '') {
            $query->byType($request->user_type);
        }

        // Filter berdasarkan role
        if ($request->has('role_id') && $request->role_id !== '') {
            $query->byRole($request->role_id);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $query->search($request->search);
        }

        $users = $query->paginate(15);
        $roles = Role::where('is_active', true)->get();

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
        return view('user-management.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|min:10|max:15',
            'user_type' => 'required|in:mahasiswa,staff',
            'status' => 'required|in:active,inactive,blocked',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

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

        return redirect()->route('user-management.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
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
        $user = User::with('role')->findOrFail($id);
        $roles = Role::where('is_active', true)->get();
        return view('user-management.edit', compact('user', 'roles'));
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

        $validator = Validator::make($request->all(), [
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'user_type' => $request->user_type,
            'status' => $request->status,
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Soft delete - ubah status menjadi inactive
        $user->update(['status' => 'inactive']);
        
        return redirect()->route('user-management.index')
            ->with('success', 'User berhasil dinonaktifkan.');
    }

    /**
     * Block user dengan durasi tertentu
     */
    public function block(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'blocked_until' => 'required|date|after:now',
            'reason' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user->update([
            'status' => 'blocked',
            'blocked_until' => $request->blocked_until
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User berhasil diblokir.');
    }

    /**
     * Unblock user
     */
    public function unblock($id)
    {
        $user = User::findOrFail($id);
        
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

        return redirect()->route('user-management.index')
            ->with('success', 'Role user berhasil diperbarui.');
    }
}
