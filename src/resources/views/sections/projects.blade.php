@php
    $catLabels = collect($projectCategories)->pluck('label', 'id');
@endphp
<section id="projects" class="section" tabindex="-1" aria-labelledby="projects-title">
    <div class="container">
        <header class="section-head reveal">
            <p class="eyebrow"><i class="bi bi-grid-1x2" aria-hidden="true"></i> Projects</p>
            <h2 id="projects-title" class="section-title">Selected work</h2>
        </header>

        <div class="filter-bar reveal" role="group" aria-label="Filter projects by category" data-filter-group="projects">
            <button type="button" class="filter-btn is-active" data-filter="all" aria-pressed="true">All Projects</button>
            @foreach ($projectCategories as $cat)
                <button type="button" class="filter-btn" data-filter="{{ $cat['id'] }}" aria-pressed="false">{{ $cat['label'] }}</button>
            @endforeach
        </div>

        <div class="row g-4" data-filter-target="projects">
            @foreach ($projects as $i => $project)
                @php
                    $img = $mediaUrl($project['image'] ?? null);
                    $cats = $project['categories'] ?? [];
                @endphp
                <div class="col-md-6 col-xl-4 reveal" data-category="{{ implode(' ', $cats) }}">
                    <article class="project-card glass-card" aria-labelledby="project-title-{{ $i }}">
                        <div class="project-card__cover">
                            @if ($img)
                                <img src="{{ $img }}" alt="Screenshot of {{ $project['title'] }}" loading="lazy" decoding="async" width="640" height="360"
                                     data-fallback-icon="{{ $project['icon'] ?? 'window' }}">
                            @else
                                <span class="cover-placeholder" aria-hidden="true"><i class="bi bi-{{ $project['icon'] ?? 'window' }}"></i></span>
                            @endif
                            @if (!empty($project['sample']))
                                <span class="sample-badge" title="Illustrative example, not real client work">Sample</span>
                            @endif
                        </div>

                        <div class="project-card__body">
                            @if (!empty($cats))
                                <ul class="tag-list" aria-label="Categories">
                                    @foreach ($cats as $c)
                                        <li>{{ $catLabels[$c] ?? $c }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <h3 id="project-title-{{ $i }}" class="project-card__title">{{ $project['title'] }}</h3>
                            <p class="project-card__text">{{ $project['summary'] ?? '' }}</p>

                            @if (!empty($project['stack']))
                                <ul class="chip-list chip-list--sm" aria-label="Technology stack">
                                    @foreach ($project['stack'] as $tech)
                                        <li class="chip">{{ $tech }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            <div class="project-card__actions">
                                <button type="button" class="btn btn-glass-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#projectModal" data-project="{{ $i }}">
                                    View details <span class="visually-hidden">for {{ $project['title'] }}</span>
                                </button>
                                @if (filled($project['demo_url'] ?? null))
                                    <a href="{{ $project['demo_url'] }}" class="icon-link-btn" target="_blank" rel="noopener" aria-label="Live demo of {{ $project['title'] }} (opens in new tab)">
                                        <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                    </a>
                                @endif
                                @if (filled($project['source_url'] ?? null))
                                    <a href="{{ $project['source_url'] }}" class="icon-link-btn" target="_blank" rel="noopener" aria-label="Source code of {{ $project['title'] }} (opens in new tab)">
                                        <i class="bi bi-github" aria-hidden="true"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>

        <p class="empty-state" data-empty-for="projects" @if (!empty($projects)) hidden @endif>
            <i class="bi bi-inbox" aria-hidden="true"></i> No projects in this category yet.
        </p>
        <p class="visually-hidden" aria-live="polite" data-filter-status="projects"></p>
    </div>
</section>
