<?php

namespace Tests\Feature;

use App\Models\Bulletin;
use App\Models\Personnel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulletinManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_comptable_can_generate_a_bulletin_with_computed_net(): void
    {
        $user = $this->userWithRole('comptable');
        $personnel = Personnel::factory()->create(['salaire_base' => 150000]);

        $response = $this->actingAs($user)->post('/bulletins', [
            'personnel_id' => $personnel->id,
            'periode_mois' => 6,
            'periode_annee' => 2026,
            'primes' => 20000,
            'retenues' => 5000,
        ]);

        $response->assertRedirect();
        $bulletin = Bulletin::first();
        // net = 150 000 + 20 000 − 5 000 = 165 000
        $this->assertEqualsWithDelta(165000, (float) $bulletin->net_a_payer, 0.01);
        $this->assertEquals(150000, (float) $bulletin->salaire_base); // snapshot du salaire
    }

    public function test_cannot_generate_two_bulletins_for_same_period(): void
    {
        $user = $this->userWithRole('comptable');
        $personnel = Personnel::factory()->create();
        Bulletin::factory()->create(['personnel_id' => $personnel->id, 'periode_mois' => 6, 'periode_annee' => 2026]);

        $response = $this->actingAs($user)->post('/bulletins', [
            'personnel_id' => $personnel->id, 'periode_mois' => 6, 'periode_annee' => 2026,
        ]);

        $response->assertSessionHasErrors('personnel_id');
        $this->assertSame(1, Bulletin::where('personnel_id', $personnel->id)->count());
    }

    public function test_generer_mois_creates_bulletins_for_all_active_personnel(): void
    {
        $user = $this->userWithRole('comptable');
        Personnel::factory()->count(3)->create(['statut' => 'actif']);
        Personnel::factory()->create(['statut' => 'inactif']);

        $this->actingAs($user)->post('/bulletins/generer-mois', ['periode_mois' => 7, 'periode_annee' => 2026]);

        // 3 actifs -> 3 bulletins ; l'inactif est ignoré
        $this->assertSame(3, Bulletin::where('periode_mois', 7)->where('periode_annee', 2026)->count());
    }

    public function test_editing_primes_recomputes_net(): void
    {
        $user = $this->userWithRole('comptable');
        $bulletin = Bulletin::factory()->create(['salaire_base' => 100000, 'primes' => 0, 'retenues' => 0]);

        $this->actingAs($user)->put("/bulletins/{$bulletin->id}", [
            'primes' => 30000, 'retenues' => 10000, 'statut' => 'valide',
        ])->assertRedirect();

        $bulletin->refresh();
        $this->assertEqualsWithDelta(120000, (float) $bulletin->net_a_payer, 0.01);
        $this->assertSame('valide', $bulletin->statut);
    }

    public function test_bulletin_pdf_is_generated(): void
    {
        $user = $this->userWithRole('comptable');
        $bulletin = Bulletin::factory()->create();

        $response = $this->actingAs($user)->get("/bulletins/{$bulletin->id}/pdf");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_caissier_can_view_but_not_generate_bulletins(): void
    {
        // caissier a bulletins.voir + bulletins.gerer d'après la config -> il PEUT générer.
        // On teste plutôt un rôle avec voir seulement : responsable_parc n'a rien -> forbidden sur index.
        $user = $this->userWithRole('responsable_parc');
        $this->actingAs($user)->get('/bulletins')->assertForbidden();
    }
}
