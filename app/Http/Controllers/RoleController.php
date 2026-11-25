<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:criar-papel|editar-papel|excluir-papel', only: ['index']),
            new Middleware('permission:criar-papel', only: ['create', 'store']),
            new Middleware('permission:editar-papel', only: ['edit', 'update']),
            new Middleware('permission:excluir-papel', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('roles.index', [
            'roles' => Role::with('permissions')->withCount('users')->orderBy('id')->paginate(5),
        ]);
    }

    public function create(): View
    {
        return view('roles.create', ['permissions' => Permission::orderBy('id')->get()]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name')]);
        $role->syncPermissions(Permission::whereIn('id', $request->validated('permissions'))->get());

        return redirect()->route('roles.index')->withSuccess('Papel criado com sucesso.');
    }

    public function edit(Role $role): View
    {
        abort_if($role->name === 'Super Admin', 403, 'O papel Super Admin não pode ser editado.');

        return view('roles.edit', [
            'role' => $role,
            'permissions' => Permission::orderBy('id')->get(),
            'rolePermissions' => $role->permissions->pluck('id')->all(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'Super Admin', 403, 'O papel Super Admin não pode ser editado.');

        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions(Permission::whereIn('id', $request->validated('permissions'))->get());

        return redirect()->back()->withSuccess('Papel atualizado com sucesso.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->name === 'Super Admin', 403, 'O papel Super Admin não pode ser excluído.');
        abort_if(auth()->user()->hasRole($role->name), 403, 'Você não pode excluir um papel atribuído a você.');

        $role->delete();

        return redirect()->route('roles.index')->withSuccess('Papel excluído com sucesso.');
    }
}
