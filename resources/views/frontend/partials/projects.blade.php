    <div class="project-grid">@foreach($projects as $slug => $project)
        <article class="project-card {{ $loop->first ? 'project-featured' : '' }}" data-reveal>
            <a class="project-image project-{{ $project['color'] }}" href="{{ route('work.show', $slug) }}" aria-label="Explore {{ $project['name'] }}"><div class="project-window"><div class="window-bar" aria-hidden="true"><span></span><span></span><span></span><i>{{ $project['name'] }}</i></div><img src="{{ $project['image_url'] }}" @if($project['image_small']) srcset="{{ $project['image_small'] }} 768w, {{ $project['image_url'] }} 1280w" sizes="(max-width: 800px) 92vw, 80vw" @endif width="1280" height="877" loading="lazy" alt="{{ $project['image_alt'] }}"></div><span class="project-open"><x-icon /></span><span class="project-image-label">MAK / DIGITAL EXPERIENCES</span></a>
            <div class="project-meta"><div><a href="{{ route('work.show', $slug) }}"><h3>{{ $project['name'] }}</h3></a><p>{{ $project['category'] }}</p></div><span class="tag">Website showcase</span></div>
        </article>
    @endforeach</div>
