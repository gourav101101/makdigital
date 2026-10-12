<?php

namespace Tests\Feature;

use App\Models\ContentEntry;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_all_main_pages_render_with_metadata(): void
    {
        foreach (['/', '/work', '/services', '/insights', '/about', '/careers', '/contact', '/privacy-policy', '/terms-and-conditions'] as $url) {
            $this->get($url)->assertOk()->assertSee('rel="canonical"', false)->assertSee('property="og:title"', false);
        }
    }

    public function test_drafts_are_absent_from_public_pages_and_sitemap(): void
    {
        $entry = ContentEntry::factory()->create(['slug' => 'private-draft', 'title' => 'Confidential draft']);
        $this->get('/insights/private-draft')->assertNotFound();
        $this->get('/insights')->assertDontSee('Confidential draft');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('private-draft')->assertDontSee('/admin');
        $entry->update(['published' => true]);
        $this->get('/sitemap.xml')->assertSee('/insights/private-draft');
    }

    public function test_every_mapped_legacy_url_redirects_once_to_a_live_page(): void
    {
        foreach (config('legacy-redirects') as $source => $target) {
            $this->get('/'.$source)->assertStatus(301)->assertRedirect($target);
            $this->get(explode('#', $target)[0])->assertOk();
        }
    }

    public function test_unmapped_demo_pages_remain_not_found(): void
    {
        $this->get('/portfolios/mega-construction')->assertNotFound();
        $this->get('/teams/david-conway')->assertNotFound();
    }

    public function test_local_robots_prevents_indexing_and_sitemap_is_valid_xml(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
        $xml = simplexml_load_string($this->get('/sitemap.xml')->getContent());
        $this->assertNotFalse($xml);
        $this->assertGreaterThan(10, count($xml->url));
    }
}
