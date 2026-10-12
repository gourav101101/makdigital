<?php

return [
    'contact' => [
        'email' => 'info@mdarena.in',
        'phone' => '+91 98264 70100',
        'location' => 'Indore, India',
        'instagram' => 'https://www.instagram.com/makdigitalarena.ind/',
        'facebook' => 'https://www.facebook.com/profile.php?id=61564574799659',
    ],
    'enquiry_notifications' => env('ENQUIRY_NOTIFICATIONS', false),
    'enquiry_recipient' => env('ENQUIRY_RECIPIENT'),
    'projects' => [
        'stoneplus-surfaces' => [
            'name' => 'Stoneplus Surfaces',
            'category' => 'Architecture & interiors',
            'image' => 'stoneplus',
            'color' => 'sand',
            'headline' => 'Natural materials. A distinctive digital presence.',
            'description' => 'A look at the online presence of Stoneplus Surfaces, a natural stone distributor serving the east coast of the United States.',
            'note' => 'Rich product photography and an extensive material collection give visitors a way to explore the world of natural stone.',
        ],
        'hansline' => [
            'name' => 'Hansline',
            'category' => 'Automotive',
            'image' => 'hansline',
            'color' => 'lavender',
            'headline' => 'Putting a world of parts within reach.',
            'description' => 'An online presence for Hansline, a business supplying two-wheeler parts and accessories.',
            'note' => 'A colorful product-led website introduces the business and helps visitors explore its range of automotive components.',
        ],
        'kpl' => [
            'name' => 'Keshayona · KPL',
            'category' => 'Construction & engineering',
            'image' => 'kpl',
            'color' => 'mint',
            'headline' => 'A digital home for real-world expertise.',
            'description' => 'A website presentation for KPL, a multidisciplinary construction company working across construction and allied services.',
            'note' => 'A clear company introduction, service information and project imagery bring its work together in one place.',
        ],
    ],
    'services' => [
        'web-and-app-development' => [
            'title' => 'Build your presence.', 'label' => 'Websites & digital products', 'number' => '01',
            'description' => 'Your next chapter deserves a better digital home. We bring design and development together to make websites, stores and applications people enjoy using.',
            'items' => ['Website design & development', 'E-commerce solutions', 'Mobile applications'],
        ],
        'seo-and-content' => [
            'title' => 'Be found. Be remembered.', 'label' => 'SEO & content', 'number' => '02',
            'description' => 'Connect what your audience is looking for with what your business does best. Build a useful, discoverable presence through search and thoughtful content.',
            'items' => ['Search engine optimization', 'Content strategy', 'Content creation'],
        ],
        'digital-marketing' => [
            'title' => 'Turn attention into action.', 'label' => 'Marketing & social media', 'number' => '03',
            'description' => 'Reach the right people with the right story. Bring your brand to life through social media, creative campaigns and purposeful paid advertising.',
            'items' => ['Social media management', 'Pay-per-click campaigns', 'Digital marketing strategy'],
        ],
        'business-consulting' => [
            'title' => 'Find your next move.', 'label' => 'Brand & business strategy', 'number' => '04',
            'description' => 'Great execution starts with a clear direction. We help you explore opportunities, define priorities and connect your digital plans to your business goals.',
            'items' => ['Business consultation', 'Brand positioning', 'Market & opportunity research'],
        ],
    ],
];
