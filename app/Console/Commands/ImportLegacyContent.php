<?php

namespace App\Console\Commands;

use App\Models\LegacyPage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLegacyContent extends Command
{
    protected $signature = 'legacy:import {--path= : Absolute path to an archived content.json}';

    protected $description = 'Import saved public website content without publishing it';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/legacy/mdarena/content.json');
        if (! is_file($path)) {
            $this->error('Archive missing. Run php scripts/archive-legacy.php first.');

            return self::FAILURE;
        }
        try {
            $pages = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($pages) || ! array_is_list($pages) || $pages === []) {
                throw new \RuntimeException('Archive must contain a nonempty list of pages.');
            }
            DB::transaction(function () use ($pages) {
                foreach ($pages as $page) {
                    if (! is_array($page) || ! isset($page['url'], $page['title'], $page['text']) || ! filter_var($page['url'], FILTER_VALIDATE_URL)) {
                        throw new \RuntimeException('Invalid page in archive. No changes imported.');
                    }
                    $metadata = $page;
                    unset($metadata['text']);
                    LegacyPage::updateOrCreate(['source_url' => $page['url']], [
                        'title' => $page['title'], 'content' => $page['text'],
                        'metadata' => $metadata, 'archived_at' => $page['archived_at'] ?? null,
                    ]);
                }
            });
        } catch (\Throwable $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
        $this->info(count($pages).' archived pages imported.');

        return self::SUCCESS;
    }
}
