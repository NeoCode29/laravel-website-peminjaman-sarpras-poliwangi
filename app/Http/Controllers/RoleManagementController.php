<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user.role_edit')->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy', 'toggleStatus', 'bulkToggleStatus']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Role::withCount(['permissions', 'users']);

        // Filter berdasarkan status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter berdasarkan guard_name
        if ($request->filled('guard_name')) {
            $query->where('guard_name', $request->guard_name);
        }

        // Search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('display_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Sort - default sorting by created_at desc
        $query->orderBy('created_at', 'desc');

        $roles = $query->paginate(15)->appends($request->query());

        return view('role-management.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
        
        return view('role-management.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:125',
                'unique:roles,name',
                'regex:/^[a-z_]+$/',
                'min:2'
            ],
            'display_name' => 'required|string|max:255|min:2',
            'description' => 'nullable|string|max:1000',
            'guard_name' => 'required|string|in:web,api',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ], [
            'name.regex' => 'Nama role hanya boleh menggunakan huruf kecil, angka, dan underscore (_).',
            'name.min' => 'Nama role minimal 2 karakter.',
            'display_name.min' => 'Nama tampilan minimal 2 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'guard_name.in' => 'Guard name harus web atau api.',
            'permissions.*.exists' => 'Permission yang dipilih tidak valid.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate permissions based on database structure
        if ($request->has('permissions')) {
            $validPermissions = Permission::where('is_active', true)
                ->whereIn('id', $request->permissions)
                ->pluck('id')
                ->toArray();
            
            if (count($validPermissions) !== count($request->permissions)) {
                return redirect()->back()
                    ->withErrors(['permissions' => 'Beberapa permission yang dipilih tidak valid atau tidak aktif.'])
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => strtolower(trim($request->name)),
                'display_name' => trim($request->display_name),
                'description' => $request->description ? trim($request->description) : null,
                'guard_name' => $request->guard_name,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // Assign permissions
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            DB::commit();

            $this->forgetRoleGateCache();

            return redirect()->route('role-management.index')
                ->with('success', 'Role berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat membuat role. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $role->loadCount('users');
        
        // Get paginated users for this role
        $usersQuery = $role->users();
        
        // Search users if search parameter is provided
        if ($request->filled('user_search')) {
            $searchTerm = $request->user_search;
            $usersQuery->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Filter by status if provided
        if ($request->filled('user_status')) {
            $usersQuery->where('status', $request->user_status);
        }
        
        // Order users by name
        $usersQuery->orderBy('name');
        
        // Paginate users - 5 per page
        $users = $usersQuery->paginate(5, ['*'], 'users_page')->appends($request->query());
        
        return view('role-management.show', compact('role', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');
        
        return view('role-management.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:125',
                Rule::unique('roles', 'name')->ignore($role->id),
                'regex:/^[a-z_]+$/',
                'min:2'
            ],
            'display_name' => 'required|string|max:255|min:2',
            'description' => 'nullable|string|max:1000',
            'guard_name' => 'required|string|in:web,api',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ], [
            'name.regex' => 'Nama role hanya boleh menggunakan huruf kecil, angka, dan underscore (_).',
            'name.min' => 'Nama role minimal 2 karakter.',
            'display_name.min' => 'Nama tampilan minimal 2 karakter.',
            'description.max' => 'Deskripsi maksimal 1000 karakter.',
            'guard_name.in' => 'Guard name harus web atau api.',
            'permissions.*.exists' => 'Permission yang dipilih tidak valid.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate permissions based on database structure
        if ($request->has('permissions')) {
            $validPermissions = Permission::where('is_active', true)
                ->whereIn('id', $request->permissions)
                ->pluck('id')
                ->toArray();
            
            if (count($validPermissions) !== count($request->permissions)) {
                return redirect()->back()
                    ->withErrors(['permissions' => 'Beberapa permission yang dipilih tidak valid atau tidak aktif.'])
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            $role->update([
                'name' => strtolower(trim($request->name)),
                'display_name' => trim($request->display_name),
                'description' => $request->description ? trim($request->description) : null,
                'guard_name' => $request->guard_name,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // Sync permissions
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            $this->forgetRoleGateCache();

            return redirect()->route('role-management.index')
                ->with('success', 'Role berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui role. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->users()->count() > 0) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak dapat dihapus karena masih digunakan oleh ' . $role->users()->count() . ' user. Pindahkan user ke role lain terlebih dahulu.'
                ], 400);
            }

            return redirect()->route('role-management.index')
                ->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh ' . $role->users()->count() . ' user. Pindahkan user ke role lain terlebih dahulu.');
        }

        if (in_array($role->name, ['admin', 'super_admin'])) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role admin tidak dapat dihapus karena merupakan role sistem.'
                ], 400);
            }

            return redirect()->route('role-management.index')
                ->with('error', 'Role admin tidak dapat dihapus karena merupakan role sistem.');
        }

        try {
            DB::beginTransaction();

            $roleName = $role->name;
            $role->delete();

            \Log::info('Role deleted', [
                'role_id' => $id,
                'role_name' => $roleName,
                'deleted_by' => auth()->id(),
                'deleted_at' => now()
            ]);

            DB::commit();

            $this->forgetRoleGateCache();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role berhasil dihapus secara permanen.'
                ]);
            }

            return redirect()->route('role-management.index')
                ->with('success', 'Role berhasil dihapus secara permanen.');

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus role. Silakan coba lagi.'
                ], 500);
            }

            return redirect()->route('role-management.index')
                ->with('error', 'Terjadi kesalahan saat menghapus role. Silakan coba lagi.');
        }
    }

    /**
     * Toggle role status
     */
    public function toggleStatus($id)
    {
        $role = Role::findOrFail($id);

        if (!$role->is_active && in_array($role->name, ['admin', 'super_admin'])) {
            return redirect()->route('role-management.index')
                ->with('error', 'Role admin tidak dapat dinonaktifkan karena merupakan role sistem.');
        }

        if ($role->is_active && $role->users()->count() > 0) {
            return redirect()->route('role-management.index')
                ->with('error', 'Role tidak dapat dinonaktifkan karena masih digunakan oleh ' . $role->users()->count() . ' user. Pindahkan user ke role lain terlebih dahulu.');
        }

        try {
            DB::beginTransaction();

            $role->update(['is_active' => !$role->is_active]);

            \Log::info('Role status toggled', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'new_status' => $role->is_active ? 'active' : 'inactive',
                'toggled_by' => auth()->id(),
                'toggled_at' => now()
            ]);

            DB::commit();

            $this->forgetRoleGateCache();

            $status = $role->is_active ? 'diaktifkan' : 'dinonaktifkan';

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Role berhasil {$status}.",
                    'role' => $role
                ]);
            }

            return redirect()->route('role-management.index')
                ->with('success', "Role berhasil {$status}.");

        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengubah status role. Silakan coba lagi.'
                ], 500);
            }

            return redirect()->route('role-management.index')
                ->with('error', 'Terjadi kesalahan saat mengubah status role. Silakan coba lagi.');
        }
    }

    /**
     * Bulk toggle status
     */
    public function bulkToggleStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'exists:roles,id',
            'action' => 'required|in:activate,deactivate'
        ], [
            'role_ids.required' => 'Pilih minimal satu role.',
            'role_ids.min' => 'Pilih minimal satu role.',
            'role_ids.*.exists' => 'Beberapa role yang dipilih tidak valid.',
            'action.required' => 'Pilih aksi yang akan dilakukan.',
            'action.in' => 'Aksi tidak valid.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Data tidak valid.');
        }

        $isActive = $request->action === 'activate';
        $roleIds = $request->role_ids;

        $roles = Role::whereIn('id', $roleIds)->get();

        if (!$isActive) {
            $adminRoles = $roles->whereIn('name', ['admin', 'super_admin']);
            if ($adminRoles->count() > 0) {
                return redirect()->route('role-management.index')
                    ->with('error', 'Role admin tidak dapat dinonaktifkan karena merupakan role sistem.');
            }

            $rolesWithUsers = $roles->filter(function ($role) {
                return $role->users()->count() > 0;
            });

            if ($rolesWithUsers->count() > 0) {
                $roleNames = $rolesWithUsers->pluck('name')->implode(', ');
                return redirect()->route('role-management.index')
                    ->with('error', "Role {$roleNames} tidak dapat dinonaktifkan karena masih digunakan oleh user.");
            }
        }

        try {
            DB::beginTransaction();

            $count = Role::whereIn('id', $roleIds)
                ->update(['is_active' => $isActive]);

            \Log::info('Bulk role status change', [
                'role_ids' => $roleIds,
                'action' => $request->action,
                'affected_count' => $count,
                'changed_by' => auth()->id(),
                'changed_at' => now()
            ]);

            DB::commit();

            $this->forgetRoleGateCache();

            $action = $isActive ? 'diaktifkan' : 'dinonaktifkan';
            
            return redirect()->route('role-management.index')
                ->with('success', "{$count} role berhasil {$action}.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('role-management.index')
                ->with('error', 'Terjadi kesalahan saat mengubah status role. Silakan coba lagi.');
        }
    }

    /**
     * Get role statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total_roles' => Role::count(),
            'active_roles' => Role::where('is_active', true)->count(),
            'inactive_roles' => Role::where('is_active', false)->count(),
            'roles_with_users' => Role::has('users')->count(),
            'roles_without_users' => Role::doesntHave('users')->count(),
        ];

        return response()->json($stats);
    }

    private function forgetRoleGateCache(): void
    {
        Cache::forget('auth.gates.roles');
    }
}
