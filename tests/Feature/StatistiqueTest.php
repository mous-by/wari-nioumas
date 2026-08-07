<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatistiqueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_responsable_parc_cannot_view_statistiques(): void
    {
        // responsable_parc n'a pas rapports.voir
        $user = $this->userWithRole('responsable_parc');

        $this->actingAs($user)->get('/statistiques')->assertForbidden();
    }

    public function test_gestionnaire_can_view_statistiques(): void
    {
        $user = $this->userWithRole('gestionnaire');

        $response = $this->actingAs($user)->get('/statistiques');

        $response->assertOk()->assertViewIs('statistiques.index');
        // 12 mois d'évolution attendus
        $response->assertViewHas('labels', fn ($labels) => count($labels) === 12);
    }
}
