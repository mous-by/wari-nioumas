<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedRolesAndPermissions();
    }

    public function test_signature_page_is_reachable(): void
    {
        $user = $this->userWithRole('directeur_general');

        $this->actingAs($user)->get('/configuration/signature')
            ->assertOk()
            ->assertViewIs('configuration.signature');
    }

    public function test_drawn_signature_is_saved(): void
    {
        Storage::fake('public');
        $user = $this->userWithRole('directeur_general');

        // 1x1 px PNG transparent en base64
        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

        $this->actingAs($user)->put('/configuration/signature', ['signature_data' => $png])
            ->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->signature);
        Storage::disk('public')->assertExists($user->signature);
    }

    public function test_uploaded_cachet_is_saved(): void
    {
        Storage::fake('public');
        $user = $this->userWithRole('directeur_general');

        $this->actingAs($user)->put('/configuration/signature', [
            'cachet_file' => UploadedFile::fake()->image('cachet.png', 200, 200),
        ])->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->cachet);
        Storage::disk('public')->assertExists($user->cachet);
    }
}
