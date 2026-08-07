<?php

namespace Tests\Feature;

use App\Models\Depense;
use App\Models\User;
use App\Models\Versement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }

    public function test_directeur_general_cannot_delete_a_user_by_default(): void
    {
        // le DG n'a pas utilisateurs.supprimer par défaut
        $dg = $this->userWithRole('directeur_general');
        $cible = $this->userWithRole('comptable');

        $this->actingAs($dg)->delete("/utilisateurs/{$cible->id}")->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $cible->id]);
    }

    public function test_directeur_general_can_delete_once_granted_the_permission(): void
    {
        $dg = $this->userWithRole('directeur_general');
        $dg->givePermissionTo('utilisateurs.supprimer'); // le superadmin la lui a donnée
        $cible = $this->userWithRole('comptable');

        $this->actingAs($dg)->delete("/utilisateurs/{$cible->id}")->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $cible->id]);
    }

    public function test_superadmin_can_delete_a_user(): void
    {
        $admin = $this->superadmin();
        $cible = $this->userWithRole('caissier');

        $this->actingAs($admin)->delete("/utilisateurs/{$cible->id}")->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $cible->id]);
    }

    public function test_deleting_a_user_cascades_their_records(): void
    {
        $admin = $this->superadmin();
        $cible = $this->userWithRole('caissier');

        $versement = Versement::factory()->create(['user_id' => $cible->id]);
        $depense = Depense::factory()->create(['user_id' => $cible->id]);
        // un enregistrement d'un AUTRE utilisateur ne doit pas être touché
        $autre = Versement::factory()->create(['user_id' => $admin->id]);

        $this->actingAs($admin)->delete("/utilisateurs/{$cible->id}")->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $cible->id]);
        $this->assertDatabaseMissing('versements', ['id' => $versement->id]);
        $this->assertDatabaseMissing('depenses', ['id' => $depense->id]);
        $this->assertDatabaseHas('versements', ['id' => $autre->id]); // préservé
    }

    public function test_cannot_delete_a_superadmin_account(): void
    {
        $admin = $this->superadmin();
        $autreAdmin = $this->superadmin();

        $this->actingAs($admin)->delete("/utilisateurs/{$autreAdmin->id}")->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $autreAdmin->id]); // toujours là
    }

    public function test_cannot_delete_own_account(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->delete("/utilisateurs/{$admin->id}")->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
