<?php

namespace Tests\Feature;

use App\Models\DigitalSignageVideo;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DigitalSignageVideoTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(string $role = 'superadmin'): User
    {
        return User::factory()->create([
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    public function test_administrator_can_upload_order_disable_and_delete_a_video(): void
    {
        Storage::fake('public');

        $this->actingAs($this->administrator())
            ->post(route('signage-videos.store'), [
                'title' => 'Campagne EUNITAL',
                'video' => UploadedFile::fake()->create('campagne.mp4', 102400, 'video/mp4'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $video = DigitalSignageVideo::firstOrFail();
        Storage::disk('public')->assertExists($video->file_path);
        $this->assertSame(1, $video->sort_order);
        $this->assertTrue($video->is_active);

        $this->put(route('signage-videos.update', $video), [
            'title' => 'Campagne mise à jour',
            'sort_order' => 3,
            'is_active' => 0,
        ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('digital_signage_videos', [
            'id' => $video->id,
            'title' => 'Campagne mise à jour',
            'sort_order' => 3,
            'is_active' => false,
        ]);

        $this->delete(route('signage-videos.destroy', $video))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('digital_signage_videos', ['id' => $video->id]);
        Storage::disk('public')->assertMissing($video->file_path);
    }

    public function test_video_upload_rejects_files_over_100_megabytes(): void
    {
        Storage::fake('public');

        $this->actingAs($this->administrator())
            ->from(route('signage-videos.index'))
            ->post(route('signage-videos.store'), [
                'title' => 'Vidéo trop volumineuse',
                'video' => UploadedFile::fake()->create('trop-lourde.mp4', 102401, 'video/mp4'),
            ])
            ->assertRedirect(route('signage-videos.index'))
            ->assertSessionHasErrors('video');

        $this->assertDatabaseCount('digital_signage_videos', 0);
    }

    public function test_only_administrators_can_manage_signage_videos(): void
    {
        $this->actingAs($this->administrator('author'))
            ->get(route('signage-videos.index'))
            ->assertForbidden();
    }

    public function test_video_previews_stay_inactive_until_the_preview_button_is_clicked(): void
    {
        DigitalSignageVideo::create([
            'title' => 'Aperçu différé',
            'file_path' => 'digital-signage/videos/preview.mp4',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $html = $this->actingAs($this->administrator())
            ->get(route('signage-videos.index'))
            ->assertOk()
            ->assertSee('Prévisualiser')
            ->assertSee('preload="none"', false)
            ->assertSee('aria-expanded="false"', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<video\b[^>]*id="video-preview-1"[^>]*hidden[^>]*>/',
            $html
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<video\b[^>]*\ssrc="/',
            $html
        );
        $this->assertStringContainsString('data-preview-src=', $html);
    }

    public function test_frontend_plays_only_active_videos_in_order(): void
    {
        Storage::fake('public');

        Settings::create([
            'website_name' => 'EUNITAL',
            'about' => 'Le Hub digital.',
            'adress' => 'Kinshasa',
            'email' => 'contact@example.com',
            'phone' => '+243000000000',
        ]);

        DigitalSignageVideo::create([
            'title' => 'Vidéo seconde',
            'file_path' => 'digital-signage/videos/seconde.mp4',
            'sort_order' => 2,
            'is_active' => true,
        ]);
        DigitalSignageVideo::create([
            'title' => 'Vidéo première',
            'file_path' => 'digital-signage/videos/premiere.webm',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        DigitalSignageVideo::create([
            'title' => 'Vidéo désactivée',
            'file_path' => 'digital-signage/videos/desactivee.mp4',
            'sort_order' => 0,
            'is_active' => false,
        ]);

        $html = $this->get(route('home'))->assertOk()->getContent();
        $firstPosition = strpos($html, 'premiere.webm');
        $secondPosition = strpos($html, 'seconde.mp4');

        $this->assertNotFalse($firstPosition);
        $this->assertNotFalse($secondPosition);
        $this->assertLessThan($secondPosition, $firstPosition);
        $this->assertStringNotContainsString('desactivee.mp4', $html);
        $this->assertStringContainsString('frontoffice-signage-player', $html);
    }
}
