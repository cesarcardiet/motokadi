<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('group')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $ids = Permission::query()
            ->whereIn('id', $request->input('permissions', []))
            ->pluck('id');

        if ($role->slug === 'administrador') {
            $panel = Permission::query()->where('slug', 'panel.admin')->value('id');
            $ids = $ids->push($panel)->filter()->unique()->values();
        }

        $role->permissions()->sync($ids);

        return back()->with('status', 'Permisos de '.$role->name.' actualizados.');
    }
}
