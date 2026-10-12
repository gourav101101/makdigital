<?php

namespace App\Services;

use App\Models\ContentEntry;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;

class SiteContent
{
    public function entries(string $kind): Collection
    {
        return ContentEntry::ofKind($kind)->published()->orderBy('position')->orderBy('id')->get();
    }

    public function projects(): array
    {
        return $this->entries('project')->mapWithKeys(fn (ContentEntry $entry) => [$entry->slug => [
            'name' => $entry->title, 'category' => $entry->category, 'image_url' => $entry->imageUrl(),
            'image_alt' => $entry->image_alt ?: $entry->title, 'color' => $entry->details['color'] ?? 'sand',
            'headline' => $entry->summary, 'description' => $entry->body, 'note' => $entry->details['note'] ?? '',
            'source' => $entry->source_url,
            'image_small' => $entry->image_path && str_ends_with($entry->image_path, '-1280.webp') ? asset(str_replace('-1280.webp', '-768.webp', $entry->image_path)) : null,
        ]])->all();
    }

    public function services(): array
    {
        return $this->entries('service')->mapWithKeys(fn (ContentEntry $entry, int $index) => [$entry->slug => [
            'title' => $entry->title, 'label' => $entry->category ?: $entry->title, 'number' => sprintf('%02d', $index + 1),
            'description' => $entry->summary, 'body' => $entry->body, 'items' => $entry->details['items'] ?? [],
            'faqs' => $entry->details['faqs'] ?? [],
        ]])->all();
    }

    public function settings(): array
    {
        return array_replace(config('site.contact'), SiteSetting::query()->pluck('value', 'key')->all());
    }
}
