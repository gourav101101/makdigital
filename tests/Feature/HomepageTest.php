<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomepageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_homepage_renders_real_content_and_a_protected_enquiry_form(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('You visualize.')->assertSee('Stoneplus Surfaces')->assertSee('data-globe', false)->assertSee('name="_token"', false)->assertSee(route('enquiries.store'))->assertDontSee('250+')->assertDontSee('bitcoin tumbler');
    }

    public function test_all_curated_project_and_service_links_resolve(): void
    {
        foreach (config('site.projects') as $slug => $project) {
            $this->get(route('work.show', $slug))->assertOk()->assertSee($project['name']);
        }
        foreach (config('site.services') as $slug => $service) {
            $this->get(route('services.show', $slug))->assertOk()->assertSee($service['title']);
        }
        $this->get('/work/not-a-project')->assertNotFound();
        $this->get('/services/not-a-service')->assertNotFound();
    }
}
