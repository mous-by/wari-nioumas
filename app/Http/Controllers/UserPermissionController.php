<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class UserPermissionController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::whereDoesntHave('roles', fn ($query) => $query->where('name', 'superadmin'))
            ->orderBy('name')
            ->get();

        $selectedUser = null;
        $groupedPermissions = collect();
        $userPermissionNames = [];

        if ($request->filled('user')) {
            $selectedUser = $users->firstWhere('id', (int) $request->input('user'));
        }

        if ($selectedUser) {
            $groupedPermissions = Permission::orderBy('name')->get()
                ->groupBy(fn (Permission $permission) => str($permission->name)->before('.')->toString());

            $userPermissionNames = $selectedUser->permissions->pluck('name')->all();
        }

        return view('user-permissions.index', [
            'users' => $users,
            'selectedUser' => $selectedUser,
            'groupedPermissions' => $groupedPermissions,
            'userPermissionNames' => $userPermissionNames,
            'totalPermissions' => Permission::count(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->hasRole('superadmin'), 403);

        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('user-permissions.index', ['user' => $user->id])
            ->with('status', 'Permissions mises à jour pour '.$user->name.'.');
    }
}
