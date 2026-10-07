<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Les permissions ne sont JAMAIS automatiques pour un utilisateur déjà
     * créé (voir config/role_permissions.php : elles sont copiées une seule
     * fois, à la création). Sans cette reprise, aucun utilisateur existant ne
     * verrait le nouveau module Documents tant qu'un administrateur n'irait
     * pas cocher la permission à la main pour chacun — en pratique invisible
     * pour un non-informaticien. On donne donc 'documents.voir'/'documents.gerer'
     * à qui a déjà ce droit dans son rôle actuel, exactement comme s'il venait
     * d'être créé avec le modèle à jour.
     */
    public function up(): void
    {
        if (! class_exists(Role::class) || ! \Illuminate\Support\Facades\Schema::hasTable('roles')) {
            return;
        }

        foreach (['documents.voir', 'documents.gerer'] as $permission) {
            Permission::findOrCreate($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolePermissions = config('role_permissions', []);

        foreach (\App\Models\User::with('roles')->get() as $user) {
            foreach ($user->roles->pluck('name') as $role) {
                foreach (['documents.voir', 'documents.gerer'] as $permission) {
                    if (in_array($permission, $rolePermissions[$role] ?? [], true) && ! $user->hasPermissionTo($permission)) {
                        $user->givePermissionTo($permission);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        // Reprise de permissions : on ne retire rien à la descente, un
        // administrateur ayant pu les ajuster entre-temps.
    }
};
