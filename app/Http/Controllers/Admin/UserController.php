<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{

    public function index(): View
    {
        $users = User::get();
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'roles'    => 'required|array',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // 🔥 Convertir IDs enviados → nombres de roles
        $roles = Role::whereIn('id', $validated['roles'])->pluck('name')->toArray();

        // 🔥 Asignar roles correctamente
        $user->syncRoles($roles);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente');
    }



    public function show(User $user): View
    {
        //$user = User::find($user);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        //$user = User::find($user);
        $roles = Role::get();
        $userRole = $user->roles->pluck('id')->toArray();

        return view('admin.users.edit', compact('user', 'roles', 'userRole'));
    }


    public function asignar(User $user): View
    {
        $roles = Role::all();
        return view('admin.users.asignar', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user['id'],
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);
        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            $input = Arr::except($input, array('password'));
        }

        $user->update($input);
        $user->roles()->sync($request->roles);

        return to_route('admin.users.index')
            ->with('info', __('Update successfully'));
    }

    public function asignarUpdate(Request $request, User $user): RedirectResponse
    {
        $user->roles()->sync($request->roles);
        return redirect()->route('admin.users.index')
            ->with('info', __('Assigned successfully'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('info', __('Deleted successfully'));
    }
}
