<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionMatrixController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user.role_edit');
    }

    /**
     * Display the role permission matrix
     */
    public function index()
    {
        $roles = Role::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $permissions = Permission::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('role-permission-matrix.index', compact('roles', 'permissions'));
    }

    /**
     * Update role permissions via AJAX
     */
    public function updateRolePermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
            'granted' => 'required|boolean'
        ]);

        $role = Role::findOrFail($request->role_id);
        $permission = Permission::findOrFail($request->permission_id);

        if ($request->granted) {
            $role->givePermissionTo($permission);
            $message = "Permission '{$permission->display_name}' berhasil diberikan ke role '{$role->display_name}'";
        } else {
            $role->revokePermissionTo($permission);
            $message = "Permission '{$permission->display_name}' berhasil dicabut dari role '{$role->display_name}'";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Bulk update permissions for a role
     */
    public function bulkUpdateRolePermissions(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($request->role_id);
        
        // Sync permissions
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('role-permission-matrix.index')
            ->with('success', "Permissions untuk role '{$role->display_name}' berhasil diperbarui.");
    }

    /**
     * Get role permissions as JSON
     */
    public function getRolePermissions($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        
        return response()->json([
            'role' => $role,
            'permissions' => $role->permissions->pluck('id')->toArray()
        ]);
    }
}
