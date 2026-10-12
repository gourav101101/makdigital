<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function test_admin_requires_authorized_login(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_login_and_logout_manage_authenticated_session(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com', 'password' => Hash::make('a-test-password-123'), 'is_admin' => true]);
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'a-test-password-123'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->get('/admin')->assertOk()->assertSee('Good things start here.');
        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_regular_user_cannot_sign_in_as_admin(): void
    {
        User::factory()->create(['email' => 'regular@example.com', 'password' => Hash::make('password')]);
        $this->post('/admin/login', ['email' => 'regular@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_create_publish_and_unpublish_content(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $payload = ['kind' => 'article', 'slug' => 'test-story', 'title' => 'A useful story', 'body' => '<script>alert(1)</script>', 'published' => '0', 'position' => 10];
        $this->post('/admin/content', $payload)->assertSessionHasNoErrors()->assertRedirect();
        $entry = ContentEntry::where('slug', 'test-story')->firstOrFail();
        $this->get('/insights/test-story')->assertNotFound();
        $payload['published'] = '1';
        $this->put('/admin/content/'.$entry->id, $payload)->assertSessionHasNoErrors();
        $this->get('/insights/test-story')->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $payload['published'] = '0';
        $this->put('/admin/content/'.$entry->id, $payload)->assertSessionHasNoErrors();
        $this->get('/insights/test-story')->assertNotFound();
    }

    public function test_invalid_media_is_rejected_and_valid_image_is_reencoded(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $payload = ['kind' => 'project', 'slug' => 'media-test', 'title' => 'Media test', 'published' => '1', 'position' => 0];
        $this->post('/admin/content', $payload + ['image' => UploadedFile::fake()->create('danger.svg', 10, 'image/svg+xml')])->assertSessionHasErrors('image');
        $this->post('/admin/content', $payload + ['image' => UploadedFile::fake()->image('photo.jpg', 300, 200)])->assertSessionHasNoErrors();
        $entry = ContentEntry::where('slug', 'media-test')->firstOrFail();
        try {
            $this->assertStringEndsWith('.webp', $entry->image_path);
            $this->assertSame('image/webp', getimagesize(public_path($entry->image_path))['mime']);
            $this->get('/work/media-test')->assertOk();
        } finally {
            unlink(public_path($entry->image_path));
        }
    }

    public function test_admin_pages_render_and_settings_are_used_publicly(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach (['/admin/content', '/admin/content/create', '/admin/content/'.ContentEntry::first()->id.'/edit', '/admin/enquiries', '/admin/settings'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->put('/admin/settings', ['email' => 'hello@example.com', 'phone' => '+91 12345 67890', 'location' => 'Test City', 'instagram' => 'https://instagram.com/example', 'facebook' => 'https://facebook.com/example'])->assertSessionHasNoErrors();
        $this->get('/contact')->assertSee('hello@example.com')->assertSee('Test City');
        $this->put('/admin/settings', ['email' => 'hello@example.com', 'phone' => '+911234', 'location' => 'City', 'instagram' => 'javascript:alert(1)'])->assertSessionHasErrors('instagram');
    }

    public function test_existing_addresses_cannot_be_changed_accidentally(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $entry = ContentEntry::ofKind('article')->first();
        $this->put('/admin/content/'.$entry->id, ['kind' => 'article', 'slug' => 'changed-address', 'title' => 'Changed', 'published' => '1', 'position' => 0])->assertSessionHasErrors('slug');
    }
}
