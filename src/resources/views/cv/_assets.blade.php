@php
    // Shared by the web and print views: the stylesheet, fonts and the theme as CSS variables.
    $theme = $cvView->theme;
    $cssFile = dirname((new \ReflectionClass(\ME\Portfolio\PortfolioServiceProvider::class))->getFileName()) . '/public/portfolio/css/cv.css';
    $cssUrl = route('portfolio.asset', ['path' => 'css/cv.css']) . (is_file($cssFile) ? '?v=' . filemtime($cssFile) : '');
    $fonts = [
        'inter'  => ["'Inter'", 'Inter:wght@400;500;600;700'],
        'roboto' => ["'Roboto'", 'Roboto:wght@400;500;700'],
        'times'  => ["'Times New Roman', Times", null],
    ];
    [$family, $googleFont] = $fonts[$theme['font']] ?? $fonts['inter'];
    $googleFamilies = collect([$googleFont, 'Hind+Siliguri:wght@400;500;600;700'])->filter()->map(fn ($f) => 'family=' . $f)->implode('&');
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?{{ $googleFamilies }}&display=swap">
<link rel="stylesheet" href="{{ $cssUrl }}">
<style>
    .cv {
        --cv-accent: {{ $theme['accent'] }};
        --cv-font: {!! $family !!}, 'Hind Siliguri', 'Noto Sans Bengali', system-ui, sans-serif;
        --cv-size: {{ (float) $theme['font_size'] }}pt;
        --cv-line: {{ (float) $theme['line_height'] }};
        --cv-gap: {{ (float) $theme['spacing'] * ($theme['compact'] ? .6 : 1) }}pt;
    }
    @page {
        size: {{ $theme['paper'] === 'Letter' ? 'letter' : 'A4' }};
        margin: {{ (int) $theme['margin'] }}mm;
        @if ($theme['page_numbers'])
            @bottom-right { content: "Page " counter(page) " of " counter(pages); font: 8pt sans-serif; color: #666; }
        @endif
    }
</style>
