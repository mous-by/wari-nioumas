<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'superadmin',
            'directeur_general',
            'gestionnaire',
            'comptable',
            'caissier',
            'responsable_parc',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role);
        }

        // Toutes les permissions connues de l'application (celles listées dans
        // config/role_permissions.php, dédupliquées).
        $permissions = collect(config('role_permissions'))
            ->flatten()
            ->unique()
            ->values();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Permissions « hors rôle » : elles existent (donc assignables via
        // « Assigner permissions ») mais ne sont attribuées à AUCUN rôle par
        // défaut. La suppression d'un utilisateur est réservée au superadmin
        // (qui contourne tout via Gate::before) et peut être donnée à un DG au
        // cas par cas.
        foreach (['utilisateurs.supprimer'] as $permission) {
            Permission::findOrCreate($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Les rôles ne portent AUCUNE permission : les autorisations sont
        // strictement individuelles (permissions directes par utilisateur).
        // On détache toute permission éventuellement héritée d'un ancien seed.
        foreach ($roles as $role) {
            Role::findByName($role)->syncPermissions([]);
        }

        $superadmin = User::firstOrCreate(
            ['phone' => '74745669'],
            ['name' => 'Moustapha BARRY', 'password' => 'superadmin74']
        );

        $superadmin->assignRole('superadmin');
    }
}
