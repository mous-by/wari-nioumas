<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    private function superadmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('superadmin');

        return $user;
    }

    public function test_user_without_permission_cannot_access_user_list(): void
    {
        // caissier's default permissions do not include utilisateurs.voir
        $caissier = $this->userWithRole('caissier');

        $this->actingAs($caissier)->get('/utilisateurs')->assertForbidden();
    }

    public function test_directeur_general_can_create_a_user(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $response = $this->actingAs($dg)->post('/utilisateurs', [
            'name' => 'Nouveau Caissier',
            'phone' => '73333333',
            'role' => 'caissier',
            'password' => 'MotDePasse1!',
            'password_confirmation' => 'MotDePasse1!',
        ]);

        $nouveau = User::where('phone', '73333333')->first();
        $this->assertNotNull($nouveau);
        $response->assertRedirect(route('user-permissions.index', ['user' => $nouveau->id]));
        $this->assertTrue($nouveau->hasRole('caissier'));
    }

    public function test_new_user_is_granted_their_role_default_permissions_directly(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->post('/utilisateurs', [
            'name' => 'Nouveau Caissier',
            'phone' => '73333333',
            'role' => 'caissier',
            'password' => 'MotDePasse1!',
            'password_confirmation' => 'MotDePasse1!',
        ]);

        $nouveau = User::where('phone', '73333333')->first();

        // caissier's config default includes chauffeurs.voir, granted as a DIRECT permission
        $this->assertTrue($nouveau->hasDirectPermission('chauffeurs.voir'));
        // ...and not something outside its default set
        $this->assertFalse($nouveau->hasDirectPermission('utilisateurs.creer'));
    }

    public function test_removing_a_permission_actually_revokes_access(): void
    {
        $caissier = $this->userWithRole('caissier');
        $this->assertTrue($caissier->can('chauffeurs.voir'));

        // strip that direct permission (as the "Assigner permissions" screen would)
        $caissier->syncPermissions([]);

        $this->assertFalse($caissier->fresh()->can('chauffeurs.voir'));
    }

    public function test_directeur_general_cannot_edit_superadmin(): void
    {
        $dg = $this->userWithRole('directeur_general');
        $superadmin = $this->superadmin();

        $this->actingAs($dg)->put("/utilisateurs/{$superadmin->id}", [
            'name' => 'Hacked Name',
            'phone' => $superadmin->phone,
            'role' => 'caissier',
        ])->assertForbidden();
    }

    public function test_directeur_general_cannot_deactivate_own_account(): void
    {
        $dg = $this->userWithRole('directeur_general');

        $this->actingAs($dg)->patch("/utilisateurs/{$dg->id}/desactiver");

        $this->assertTrue($dg->fresh()->actif);
    }

    public function test_superadmin_is_hidden_from_other_roles(): void
    {
        $dg = $this->userWithRole('directeur_general');
        $this->superadmin(['name' => 'Ghost Admin']);

        $response = $this->actingAs($dg)->get('/utilisateurs');

        $response->assertOk();
        $response->assertDontSee('Ghost Admin');
    }

    public function test_superadmin_sees_everyone_including_other_superadmins(): void
    {
        $superadminViewer = $this->superadmin();
        $this->superadmin(['name' => 'Second Admin']);

        $response = $this->actingAs($superadminViewer)->get('/utilisateurs');

        $response->assertOk();
        $response->assertSee('Second Admin');
    }

    public function test_superadmin_can_edit_another_superadmin_without_losing_the_role(): void
    {
        $superadminViewer = $this->superadmin();
        $target = $this->superadmin(['name' => 'Old Name']);

        $response = $this->actingAs($superadminViewer)->put("/utilisateurs/{$target->id}", [
            'name' => 'New Name',
            'phone' => $target->phone,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertSame('New Name', $target->fresh()->name);
        $this->assertTrue($target->fresh()->hasRole('superadmin'));
    }
}
