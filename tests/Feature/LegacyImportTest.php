<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_is_repeatable_and_updates_existing_content(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mak-test-');
        try {
            $page = ['url' => 'https://mdarena.in/', 'title' => 'MAK', 'text' => 'Original'];
            file_put_contents($path, json_encode([$page]));
            $this->artisan('legacy:import', ['--path' => $path])->assertSuccessful();
            $page['text'] = 'Updated';
            file_put_contents($path, json_encode([$page]));
            $this->artisan('legacy:import', ['--path' => $path])->assertSuccessful();
            $this->assertDatabaseCount('legacy_pages', 1);
            $this->assertDatabaseHas('legacy_pages', ['content' => 'Updated']);
        } finally {
            unlink($path);
        }
    }

    public function test_invalid_archive_rolls_back_all_pages(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mak-test-');
        try {
            file_put_contents($path, json_encode([
                ['url' => 'https://mdarena.in/', 'title' => 'MAK', 'text' => 'Valid'],
                ['title' => 'Missing URL'],
            ]));
            $this->artisan('legacy:import', ['--path' => $path])->assertFailed();
            $this->assertDatabaseCount('legacy_pages', 0);
        } finally {
            unlink($path);
        }
    }
}
