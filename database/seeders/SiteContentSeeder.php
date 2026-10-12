<?php

namespace Database\Seeders;

use App\Models\ContentEntry;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('site.projects') as $slug => $project) {
            ContentEntry::firstOrCreate(['kind' => 'project', 'slug' => $slug], [
                'title' => $project['name'], 'summary' => $project['headline'], 'body' => $project['description'],
                'category' => $project['category'], 'image_path' => 'images/'.$project['image'].'-1280.webp',
                'image_alt' => $project['name'].' website', 'details' => ['color' => $project['color'], 'note' => $project['note']],
                'published' => true, 'position' => array_search($slug, array_keys(config('site.projects'))),
                'source_url' => 'https://mdarena.in/',
            ]);
        }
        foreach (config('site.services') as $slug => $service) {
            ContentEntry::firstOrCreate(['kind' => 'service', 'slug' => $slug], [
                'title' => $service['title'], 'summary' => $service['description'], 'category' => $service['label'],
                'body' => "Every business starts from a different place. We begin by understanding your audience, your existing digital presence and what you want to achieve.\n\nTogether, we define a practical scope, the right priorities and how we will review progress. You stay involved throughout, from the first conversation through delivery and ongoing improvement.",
                'details' => ['items' => $service['items'], 'faqs' => [
                    ['question' => 'Where do we start?', 'answer' => 'Start with a conversation about your business and priorities. We will recommend an appropriate next step and agree a scope before work begins.'],
                    ['question' => 'Can you work with an existing website or campaign?', 'answer' => 'Yes. We can review your existing digital presence and identify what to improve, retain or rebuild.'],
                    ['question' => 'How long will the project take?', 'answer' => 'The timeline depends on the scope, content and technical requirements. We agree milestones after understanding your needs.'],
                ]], 'published' => true, 'position' => (int) $service['number'], 'source_url' => 'https://mdarena.in/services/',
            ]);
        }
        $articles = [
            ['brand-building-agency', 'A stronger brand starts with a clear direction.', 'Brand strategy',
                'Your logo is one part of the story. A recognizable brand connects positioning, identity and every customer interaction.',
                "Start with what your business stands for.\n\nA brand strategy gives your business a clear direction: who you serve, what you offer and why it matters. It helps connect the choices you make across your website, content, social media and customer conversations.\n\nMake the experience recognizable.\n\nColor, typography and visual identity help people recognize a business. The words you use and the way you respond to customers are equally important. Consistency helps the experience feel connected across different channels.\n\nBuild from a practical foundation.\n\nDefine your audience and priorities. Review your existing website and communication. Identify where the experience is unclear or inconsistent, then choose a manageable set of improvements.\n\nKeep learning.\n\nBrand building is an ongoing process. Review how people discover and interact with your business, and adjust your communication as your business and audience evolve."],
            ['ppc-services', 'Paid campaigns work better with a clear destination.', 'Digital marketing',
                'A useful landing page and a focused message help connect campaign attention to the next step.',
                "Begin with a specific objective.\n\nA paid campaign needs a clear purpose. That might be introducing a service, encouraging an enquiry or helping people discover a product. Your objective guides the audience, message and destination you choose.\n\nMatch the promise to the page.\n\nWhen someone clicks an advertisement, the landing page should continue the same conversation. Keep the main message clear, make the next step easy to find and include information that helps visitors decide.\n\nMake room for testing.\n\nDifferent messages and audiences can behave differently. Establish a sensible budget and review campaign data regularly. Use what you learn to refine the creative, targeting and page experience.\n\nLook beyond the click.\n\nTraffic is only part of the picture. Review whether the campaign is leading to useful business conversations or other relevant actions, and account for how enquiries are handled after submission."],
        ];
        foreach ($articles as $index => [$slug, $title, $category, $summary, $body]) {
            ContentEntry::firstOrCreate(['kind' => 'article', 'slug' => $slug], ['title' => $title, 'category' => $category, 'summary' => $summary, 'body' => $body, 'published' => true, 'position' => $index, 'source_url' => 'https://mdarena.in/'.$slug.'/']);
        }
        foreach ([
            ['samarth-meena', 'Samarth Meena', 'Our experience working with MAK Digital Arena was overall positive. They delivered quality work that met the project objectives and adhered to deadlines.'],
            ['dr-sachin-arya', 'Dr. Sachin Arya', 'Excellent team , highly appreciated your team work'],
        ] as [$slug, $name, $quote]) {
            ContentEntry::firstOrCreate(['kind' => 'testimonial', 'slug' => $slug], ['title' => $name, 'body' => $quote, 'summary' => 'Client feedback', 'published' => true, 'source_url' => 'https://mdarena.in/']);
        }
        $pages = [
            'about' => ['A little curiosity. A bigger perspective.', 'Independent minds, working together.', "MAK Digital Arena is a digital transformation and business consulting company based in Indore. We bring young, creative perspectives together with hands-on industry experience to help businesses build a purposeful online presence.\n\nOur work connects strategy, web development, search, content and digital marketing. We begin with your business goals and focus on practical solutions that fit the way you work.\n\nOur mission is to deliver useful business solutions at an affordable price and build long-term relationships through shared growth.\n\nIntegrity, commitment, innovation and quality guide how we collaborate. We listen carefully, communicate clearly and take responsibility for the details."],
            'careers' => ['Bring your curiosity.', 'Good work starts with good people.', "We are interested in thoughtful designers, developers, writers and digital marketers who care about the work they create.\n\nTell us about your experience, the work you enjoy and what you would like to explore next. Share a portfolio or a few examples when you write to us.\n\nThis is an invitation to introduce yourself, not a listing for a specific open role. We will contact you if there is a suitable opportunity."],
        ];
        foreach ($pages as $slug => [$title, $summary, $body]) {
            ContentEntry::firstOrCreate(['kind' => 'page', 'slug' => $slug], compact('title', 'summary', 'body') + ['published' => true, 'source_url' => 'https://mdarena.in/']);
        }
        $archivePath = storage_path('app/legacy/mdarena/content.json');
        if (is_file($archivePath)) {
            $archive = collect(json_decode(file_get_contents($archivePath), true, flags: JSON_THROW_ON_ERROR));
            foreach (['privacy-policy' => 'Privacy Policy', 'terms-and-conditions' => 'Terms and Conditions'] as $slug => $title) {
                $source = $archive->firstWhere('url', 'https://mdarena.in/'.$slug.'/');
                if (! $source) {
                    continue;
                }
                $body = $source['text'];
                $start = $slug === 'privacy-policy' ? strpos($body, "Privacy Policy\nLast updated:") : strpos($body, 'Acceptance of Terms');
                if ($start !== false) {
                    $body = substr($body, $start);
                }
                foreach (["\nConnect with\n", "\nYou Visualize We Digitalize"] as $marker) {
                    if (($end = strpos($body, $marker)) !== false) {
                        $body = substr($body, 0, $end);
                    }
                }
                ContentEntry::firstOrCreate(['kind' => 'page', 'slug' => $slug], [
                    'title' => $title, 'summary' => 'Information from MAK Digital Arena’s existing website.',
                    'body' => $body, 'published' => true, 'source_url' => $source['url'],
                    'details' => ['review_required' => 'Migrated legacy policy. Review outdated addresses, cookies and service descriptions before production launch.'],
                ]);
            }
        }
        foreach (config('site.contact') as $key => $value) {
            SiteSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
