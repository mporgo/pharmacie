<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->get();
        return response()->json($users->map(fn($u) => [
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'actif' => $u->actif,
            'roles' => $u->getRoleNames(),
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'actif'    => true,
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:users,email,{$user->id}",
            'password' => 'sometimes|nullable|string|min:8',
            'role'     => 'sometimes|exists:roles,name',
            'actif'    => 'sometimes|boolean',
        ]);

        $data = $request->only(['name', 'email', 'actif']);
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'actif' => $user->actif,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
