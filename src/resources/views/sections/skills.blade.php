<section id="skills" class="section" tabindex="-1" aria-labelledby="skills-title">
    <div class="container">
        <header class="section-head reveal">
            <p class="eyebrow"><i class="bi bi-stack" aria-hidden="true"></i> Skills</p>
            <h2 id="skills-title" class="section-title" data-split-words>Tools I build with</h2>
            <p class="section-sub">Grouped by area. No made-up percentages, just the things I use in production.</p>
        </header>

        @php
            $allSkills = collect($skillCategories)->flatMap(fn ($c) => $c['skills'] ?? [])->unique('name')->values();
        @endphp
        @if ($allSkills->count() > 4)
            {{-- Decorative loop of every skill; the grouped list below is the accessible version. --}}
            <div class="marquee reveal" aria-hidden="true">
                @foreach ([0, 1] as $copy)
                    <ul class="marquee__track">
                        @foreach ($allSkills as $skill)
                            <li class="marquee__item"><i class="bi bi-{{ $skill['icon'] ?? 'code' }}"></i> {{ $skill['name'] }}</li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        @endif

        <div class="filter-bar reveal" role="group" aria-label="Filter skills by category" data-filter-group="skills">
            <button type="button" class="filter-btn is-active" data-filter="all" aria-pressed="true">All</button>
            @foreach ($skillCategories as $cat)
                <button type="button" class="filter-btn" data-filter="{{ $cat['id'] }}" aria-pressed="false">
                    <i class="bi bi-{{ $cat['icon'] ?? 'dot' }}" aria-hidden="true"></i> {{ $cat['label'] }}
                </button>
            @endforeach
        </div>

        <div class="skills-grid" data-filter-target="skills">
            @foreach ($skillCategories as $cat)
                <div class="skill-group reveal" data-category="{{ $cat['id'] }}">
                    <h3 class="skill-group__title">
                        <i class="bi bi-{{ $cat['icon'] ?? 'dot' }}" aria-hidden="true"></i> {{ $cat['label'] }}
                    </h3>
                    @if (empty($cat['skills']))
                        <p class="empty-note">Nothing listed here yet.</p>
                    @else
                        <ul class="skill-list">
                            @foreach ($cat['skills'] as $skill)
                                <li class="skill-pill">
                                    <i class="bi bi-{{ $skill['icon'] ?? 'code' }}" aria-hidden="true"></i>
                                    <span>{{ $skill['name'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="empty-state" data-empty-for="skills" hidden>
            <i class="bi bi-inbox" aria-hidden="true"></i> No skills listed in this category yet.
        </p>
        <p class="visually-hidden" aria-live="polite" data-filter-status="skills"></p>
    </div>
</section>
