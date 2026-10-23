@php
    // Compiled views live in storage/, so locate the package via its provider.
    $pkgPublic = dirname((new \ReflectionClass(\ME\Portfolio\PortfolioServiceProvider::class))->getFileName()) . '/public/portfolio';
    $assetUrl = function (string $path) use ($pkgPublic) {
        $file = $pkgPublic . '/' . $path;
        $version = is_file($file) ? filemtime($file) : null;
        return route('portfolio.asset', ['path' => $path]) . ($version ? '?v=' . $version : '');
    };
    $mediaUrl = fn (?string $path) => filled($path) ? url($path) : null;

    // Colour preset and cursor trail picked in Admin → Appearance.
    $theme     = \ME\Portfolio\Support\Theme::current();
    $themeDark = \ME\Portfolio\Support\Theme::isDark($theme);
    $cursor    = \ME\Portfolio\Support\Theme::cursor();

    $name      = $profile['name'] ?? 'Your Name';
    $pageTitle = $seo['title'] ?? $name;
    $ogImage   = $mediaUrl($seo['og_image'] ?? null);
    $siteUrl   = $seo['site_url'] ?? url('/');
    // Icon uploaded in the admin settings (metheme's get_image) wins; otherwise the bundled logo.
    $favicon   = (function_exists('get_image') ? get_image('app_ico') : null) ?: $assetUrl('images/favicon.ico');

    $structuredData = [
        array_filter([
            '@context'    => 'https://schema.org',
            '@type'       => 'Person',
            'name'        => $name,
            'jobTitle'    => $profile['designation'] ?? null,
            'url'         => $siteUrl,
            'image'       => \ME\Portfolio\Support\Theme::photoUrl() ?: $mediaUrl($profile['photo'] ?? null),
            'description' => $seo['description'] ?? null,
            'sameAs'      => collect($contact['methods'] ?? [])
                ->pluck('href')
                ->filter(fn ($h) => is_string($h) && str_starts_with($h, 'https://'))
                ->values()->all(),
        ]),
        array_filter([
            '@context'      => 'https://schema.org',
            '@type'         => 'WebSite',
            'name'          => $seo['author'] ?? $name,
            'alternateName' => $seo['alternate_name'] ?? null,
            'url'           => $siteUrl,
        ]),
    ];
@endphp
<!doctype html>
<html lang="en" class="no-js" data-bs-theme="{{ $themeDark ? 'dark' : 'light' }}" data-mode="{{ $themeDark ? 'dark' : 'light' }}" data-theme="{{ \ME\Portfolio\Support\Theme::currentId() }}" @if (filled($theme['gradient'] ?? null)) data-gradient @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $seo['description'] ?? '' }}">
    @if (filled($seo['keywords'] ?? null))
        <meta name="keywords" content="{{ $seo['keywords'] }}">
    @endif
    <meta name="author" content="{{ $seo['author'] ?? $name }}">
    <meta name="robots" content="{{ $seo['robots'] ?? 'index, follow' }}">
    <meta name="theme-color" content="{{ $theme['bg'] ?? ($seo['theme_color'] ?? '#eef2fa') }}">
    <meta name="color-scheme" content="{{ $themeDark ? 'dark' : 'light' }}">
    <link rel="canonical" href="{{ $canonical }}">

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $seo['description'] ?? '' }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:site_name" content="{{ $seo['site_name'] ?? $name }}">
    <meta property="og:locale" content="{{ $seo['locale'] ?? 'en_US' }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="{{ $name }}">
    @endif
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? '' }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
    @if (filled($seo['twitter_handle'] ?? null))
        <meta name="twitter:site" content="{{ $seo['twitter_handle'] }}">
        <meta name="twitter:creator" content="{{ $seo['twitter_handle'] }}">
    @endif

    <link rel="icon" href="{{ $favicon }}">
    <link rel="apple-touch-icon" href="{{ $assetUrl('images/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ $assetUrl('css/style.css') }}">
    <style>{!! \ME\Portfolio\Support\Theme::css($theme) !!}</style>

    <script>
        document.documentElement.classList.replace('no-js', 'js');
        // Motion classes only when the visitor allows motion; motion.js removes 'motion-pending' once it takes over.
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) document.documentElement.classList.add('motion', 'motion-pending');
    </script>

    {{-- Built in the @php block above: inline '@context' would be compiled as Blade's @context directive. --}}
    @foreach ($structuredData as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
    @endforeach
</head>
<body data-cursor="{{ $cursor }}">
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="scroll-progress" aria-hidden="true"><span></span></div>

    <div class="ambient" aria-hidden="true">
        <span class="ambient__orb ambient__orb--a"></span>
        <span class="ambient__orb ambient__orb--b"></span>
        <span class="ambient__orb ambient__orb--c"></span>
        <span class="ambient__orb ambient__orb--d"></span>
        <span class="ambient__grid"></span>
    </div>

    <main id="main">
        @include('portfolio::sections.hero')
        @include('portfolio::sections.about')
        @include('portfolio::sections.skills')
        @include('portfolio::sections.experience')
        @include('portfolio::sections.projects')
        @include('portfolio::sections.contact')
    </main>

    <footer class="copyright">
        <div class="container">
            <p class="copyright__bar glass">
                &copy; <span data-current-year>{{ date('Y') }}</span> {{ $name }}. All rights reserved.
            </p>
        </div>
    </footer>

    @include('portfolio::partials.dock')
    @include('portfolio::partials.project-modal')

    <div class="toast-container position-fixed toast-stack" aria-live="polite" aria-atomic="true">
        <div id="appToast" class="toast glass-toast" role="status" data-bs-delay="2200">
            <div class="d-flex align-items-center gap-2 px-3 py-2">
                <i class="bi bi-check2-circle toast-icon" aria-hidden="true"></i>
                <span class="toast-text"></span>
            </div>
        </div>
    </div>

    <script type="application/json" id="portfolio-projects">@json($projects)</script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ $assetUrl('js/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/animejs@4.5.0/dist/bundles/anime.umd.min.js"
            integrity="sha384-InMmvD3VoYcY7hGjSC80aLb2bNNE4CzpX+Eq6FVDlmB0IKgDvmfPw4UY8L/M++iG" crossorigin="anonymous" defer></script>
    <script src="{{ $assetUrl('js/motion.js') }}" defer></script>
    @if ($cursor !== 'none')
        <script src="{{ $assetUrl('js/cursor-trail.js') }}" defer></script>
    @endif
</body>
</html>
