@php
    $photo    = $mediaUrl($profile['photo'] ?? null);
    $cover    = $mediaUrl($profile['cover_image'] ?? null);
    $cvUrl    = $mediaUrl($profile['cv_url'] ?? null);
    // First + last name, skipping single-letter initials: "M. Jane Q. Doe" → "JD".
    $words    = collect(preg_split('/\s+/', trim(preg_replace('/[^\pL\s]/u', ' ', $name))))
        ->filter(fn ($w) => mb_strlen($w) > 1)->values();
    $initials = mb_strtoupper(collect([$words->first(), $words->count() > 1 ? $words->last() : null])
        ->filter()->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    $avail    = $profile['availability'] ?? [];
    $roles    = array_values(array_filter($profile['designations'] ?? [$profile['designation'] ?? '']));
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
        <span class="hero__code">&lt;?php return new Solution($problem); ?&gt;</span>
    </div>

    <div class="container hero__inner">
        {{-- With a full cover photo the portrait is already in the background, so the avatar is skipped. --}}
        @unless ($cover)
        @if ($photo)
            {{-- Halftone portrait dial: the photo redrawn as dots inside a colour ring. --}}
            <div class="hero__portrait" data-enter="1">
                <x-portfolio::halftone-portrait :src="$photo" color="#4b8bff" tone="light" size="480" :label="'Portrait of ' . $name" />
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

        <h1 id="hero-name" class="hero__name" data-enter="2">{{ $name }}</h1>
        @if (!empty($roles))
            <p class="hero__role" data-enter="3">
                {{-- Screen readers get the full list once; the typed text is visual only. --}}
                <span class="visually-hidden">{{ implode(', ', $roles) }}</span>
                <span class="hero__typed" aria-hidden="true" data-roles='@json($roles)'>{{ $roles[0] }}</span><span class="hero__caret" aria-hidden="true"></span>
            </p>
        @endif
        @if (filled($profile['tagline'] ?? null))
            <p class="hero__tagline" data-enter="3">{{ $profile['tagline'] }}</p>
        @endif

        <div class="hero__cta" data-enter="4">
            <a href="#projects" class="btn {{ $cover ? 'btn-glass' : 'btn-glass-primary' }}">
                <i class="bi bi-grid-1x2" aria-hidden="true"></i> View Projects
            </a>
            <a href="#contact" class="btn btn-glass">
                <i class="bi bi-send" aria-hidden="true"></i> Hire Me
            </a>
            @if ($cvUrl)
                <a href="{{ $cvUrl }}" class="btn btn-glass" download>
                    <i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> CV
                </a>
            @endif
        </div>
        </div>
    </div>

    <a href="#about" class="hero__scroll" aria-label="Scroll to About section">
        <i class="bi bi-chevron-double-down" aria-hidden="true"></i>
    </a>
</section>
