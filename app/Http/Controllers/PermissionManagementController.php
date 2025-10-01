<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PermissionManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user.role_edit')->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Permission::with('roles');

        // Filter berdasarkan kategori
        if ($request->has('category') && $request->category !== '') {
            $query->where('category', $request->category);
        }

        // Filter berdasarkan status
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('display_name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $permissions = $query->orderBy('category')->orderBy('name')->paginate(20);

        // Get unique categories for filter
        $categories = Permission::distinct()->pluck('category')->filter()->sort()->values();

        return view('permission-management.index', compact('permissions', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('permission-management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:125|unique:permissions,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('permission-management.index')
            ->with('success', 'Permission berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $permission = Permission::with('roles')->findOrFail($id);
        return view('permission-management.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('permission-management.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:125',
                Rule::unique('permissions', 'name')->ignore($permission->id)
            ],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $permission->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'category' => $request->category,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('permission-management.index')
            ->with('success', 'Permission berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        
        // Check if permission is being used by roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('permission-management.index')
                ->with('error', 'Permission tidak dapat dihapus karena masih digunakan oleh role.');
        }

        // Soft delete - ubah status menjadi inactive
        $permission->update(['is_active' => false]);
        
        return redirect()->route('permission-management.index')
            ->with('success', 'Permission berhasil dinonaktifkan.');
    }

    /**
     * Toggle permission status
     */
    public function toggleStatus($id)
    {
        $permission = Permission::findOrFail($id);
        
        $permission->update(['is_active' => !$permission->is_active]);
        
        $status = $permission->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('permission-management.index')
            ->with('success', "Permission berhasil {$status}.");
    }
}
