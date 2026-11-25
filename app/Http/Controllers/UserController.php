<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:criar-usuario|editar-usuario|excluir-usuario', only: ['index']),
            new Middleware('permission:criar-usuario', only: ['create', 'store']),
            new Middleware('permission:editar-usuario', only: ['edit', 'update']),
            new Middleware('permission:excluir-usuario', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        return view('users.index', ['users' => User::with('roles')->latest('id')->paginate(5)]);
    }

    public function create(): View
    {
        return view('users.create', ['roles' => $this->assignableRoles()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->ensureRolesAreAssignable($request->validated('roles'));

        $user = User::create($request->safe()->only(['name', 'email', 'password']));
        $user->assignRole($request->validated('roles'));

        return redirect()->route('users.index')->withSuccess('Usuário criado com sucesso.');
    }

    public function edit(User $user): View
    {
        $this->ensureCanManage($user);

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'userRoles' => $user->roles->pluck('name')->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureCanManage($user);
        $this->ensureRolesAreAssignable($request->validated('roles'));

        $data = $request->safe()->only(['name', 'email']);
        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        $user->update($data);
        $user->syncRoles($request->validated('roles'));

        return redirect()->back()->withSuccess('Usuário atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->hasRole('Super Admin'), 403, 'Usuários Super Admin não podem ser excluídos.');
        abort_if($user->is(auth()->user()), 403, 'Você não pode excluir o próprio usuário.');

        $user->syncRoles([]);
        $user->delete();

        return redirect()->route('users.index')->withSuccess('Usuário excluído com sucesso.');
    }

    /** Somente o próprio Super Admin pode editar um perfil Super Admin. */
    private function ensureCanManage(User $user): void
    {
        abort_if(
            $user->hasRole('Super Admin') && ! $user->is(auth()->user()),
            403,
            'Somente o próprio Super Admin pode editar este perfil.',
        );
    }

    /** O papel Super Admin só pode ser atribuído por outro Super Admin. */
    private function assignableRoles(): array
    {
        return Role::orderBy('id')->pluck('name')
            ->reject(fn (string $name) => $name === 'Super Admin' && ! auth()->user()->hasRole('Super Admin'))
            ->values()
            ->all();
    }

    private function ensureRolesAreAssignable(array $roles): void
    {
        abort_unless(empty(array_diff($roles, $this->assignableRoles())), 403, 'Você não pode atribuir este papel.');
    }
}
