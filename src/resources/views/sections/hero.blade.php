@php
    // A picture uploaded in Admin → Appearance wins over the config path.
    $photo    = \ME\Portfolio\Support\Theme::photoUrl() ?: $mediaUrl($profile['photo'] ?? null);
    $cover    = $mediaUrl($profile['cover_image'] ?? null);
    $cvUrl    = $mediaUrl($profile['cv_url'] ?? null);
    // First + last name, skipping single-letter initials: "M. Jane Q. Doe" → "JD".
    $words    = collect(preg_split('/\s+/', trim(preg_replace('/[^\pL\s]/u', ' ', $name))))
        ->filter(fn ($w) => mb_strlen($w) > 1)->values();
    $initials = mb_strtoupper(collect([$words->first(), $words->count() > 1 ? $words->last() : null])
        ->filter()->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    $avail    = $profile['availability'] ?? [];
    $roles    = array_values(array_filter($profile['designations'] ?? [$profile['designation'] ?? '']));

    // Hero stats: set profile.stats to override; otherwise derived from real config data, never invented.
    $stats = $profile['stats'] ?? null;
    if ($stats === null) {
        $expFact = collect($about['facts'] ?? [])->firstWhere('label', 'Experience')['value'] ?? '';
        preg_match('/^(\d+)(\+?)/', (string) $expFact, $m);
        $stats = [
            ['value' => $m[1] ?? 0, 'suffix' => $m[2] ?? '', 'label' => 'Years experience'],
            ['value' => count($experience ?? []), 'suffix' => '', 'label' => 'Companies'],
            ['value' => collect($skillCategories ?? [])->sum(fn ($c) => count($c['skills'] ?? [])), 'suffix' => '+', 'label' => 'Technologies'],
        ];
    }
    $stats = array_values(array_filter($stats, fn ($s) => (int) ($s['value'] ?? 0) > 0));
@endphp
<section id="home" class="hero {{ $cover ? 'hero--cover' : '' }}" tabindex="-1" aria-labelledby="hero-name">
    <div class="hero__cover" aria-hidden="true">
        @if ($cover)
            <img src="{{ $cover }}" alt="" class="hero__cover-img" width="1920" height="1080" fetchpriority="high">
            <span class="hero__mist"></span>
        @endif
        <svg class="hero__lines" viewBox="0 0 1200 600" preserveAspectRatio="xMidYMid slice">
            <defs>
                <linearGradient id="heroLine" x1="0" x2="1">
                    <stop offset="0" stop-color="currentColor" stop-opacity="0"/>
                    <stop offset=".5" stop-color="currentColor" stop-opacity=".55"/>
                    <stop offset="1" stop-color="currentColor" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <path d="M-50 420 C 250 300, 450 520, 700 380 S 1100 260, 1260 330" stroke="url(#heroLine)" fill="none"/>
            <path d="M-50 470 C 300 360, 500 560, 760 430 S 1120 320, 1260 390" stroke="url(#heroLine)" fill="none"/>
            <path d="M-50 140 C 200 220, 420 80, 640 160 S 1000 240, 1260 120" stroke="url(#heroLine)" fill="none"/>
        </svg>
        <span class="hero__code"><span class="hero__code-dot"></span><span class="hero__code-text" data-code="&lt;?php return new Solution($problem); ?&gt;">&lt;?php return new Solution($problem); ?&gt;</span></span>
    </div>

    <div class="container hero__inner">
        {{-- With a full cover photo the portrait is already in the background, so the avatar is skipped. --}}
        @unless ($cover)
        @if ($photo)
            {{-- Halftone portrait dial: the photo redrawn as dots inside a colour ring. --}}
            <div class="hero__portrait" data-enter="1">
                @php $portrait = \ME\Portfolio\Support\Theme::portrait($theme); @endphp
                <x-portfolio::halftone-portrait :src="$photo" :color="$portrait['color']" :disc="$portrait['disc']" :adjust="$portrait"
                    :tone="$themeDark ? 'dark' : 'light'" size="620" :label="'Portrait of ' . $name" />
            </div>
        @else
            <div class="hero__avatar" data-enter="1">
                <span class="hero__initials" aria-hidden="true"><span>{{ $initials }}</span></span>
            </div>
        @endif
        @endunless

        <div class="hero__content">
        @if (filled($avail['label'] ?? null))
            <p class="status-pill" data-enter="2">
                <span class="status-pill__dot {{ ($avail['available'] ?? false) ? 'is-on' : '' }}" aria-hidden="true"></span>
                {{ $avail['label'] }}
            </p>
        @endif

        <h1 id="hero-name" class="hero__name" data-enter="2" data-split>{{ $name }}</h1>
        @if (!empty($roles))
            <p class="hero__role" data-enter="3">
                {{-- Screen readers get the full list once; the rotating text is visual only. --}}
                <span class="visually-hidden">{{ implode(', ', $roles) }}</span>
                <span class="hero__typed" aria-hidden="true" data-roles='@json($roles)'><span class="hero__typed-word">{{ $roles[0] }}</span></span>
            </p>
        @endif
        @if (filled($profile['tagline'] ?? null))
            <p class="hero__tagline" data-enter="3">{{ $profile['tagline'] }}</p>
        @endif

        <div class="hero__cta" data-enter="4">
            <a href="#projects" class="btn {{ $cover ? 'btn-glass' : 'btn-glass-primary btn-shine' }}" data-magnetic>
                <i class="bi bi-grid-1x2" aria-hidden="true"></i> {{ $labels['hero_projects_btn'] }}
            </a>
            <a href="#contact" class="btn btn-glass" data-magnetic>
                <i class="bi bi-send" aria-hidden="true"></i> {{ $labels['hero_hire_btn'] }}
            </a>
            @if ($cvUrl)
                <a href="{{ $cvUrl }}" class="btn btn-glass" download>
                    <i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> {{ $labels['hero_cv_btn'] }}
                </a>
            @endif
        </div>

        @if (!empty($stats))
            <dl class="hero__stats" data-enter="4">
                @foreach ($stats as $stat)
                    <div class="hero__stat">
                        <dt>{{ $stat['label'] }}</dt>
                        <dd><span data-count="{{ (int) $stat['value'] }}">{{ (int) $stat['value'] }}</span>{{ $stat['suffix'] ?? '' }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
        </div>
    </div>

    <a href="#about" class="hero__scroll" aria-label="Scroll to About section">
        <i class="bi bi-chevron-double-down" aria-hidden="true"></i>
    </a>
</section>
