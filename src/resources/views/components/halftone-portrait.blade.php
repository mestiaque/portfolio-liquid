@props([
    'src',                  // same-origin image (the pixels are read to place the dots)
    'color' => '#ff4b4b',   // dot and tick colour; the matching ring segment lights up
    'size'  => 560,         // max width in px; the canvas is square and shrinks to fit
    'ring'  => true,        // false draws the portrait dots only
    'label' => null,        // accessible name; defaults to "Portrait"
    'tone'  => 'dark',      // page background: 'dark' or 'light' (sets the neutral ring strokes)
])
@php
    $script = 'js/halftone-portrait.js';
    $file = dirname((new \ReflectionClass(\ME\Portfolio\PortfolioServiceProvider::class))->getFileName()) . '/public/portfolio/' . $script;
    $scriptUrl = route('portfolio.asset', ['path' => $script]) . (is_file($file) ? '?v=' . filemtime($file) : '');
    $srcUrl = filled($src) ? url($src) : '';
@endphp
{{-- Halftone portrait dial: the photo redrawn as dots inside a colour ring (public/portfolio/js/halftone-portrait.js). --}}
<figure {{ $attributes->merge(['class' => 'halftone']) }} style="margin: 0 auto; width: 100%; max-width: {{ (int) $size }}px;">
    <canvas data-halftone
            data-src="{{ $srcUrl }}"
            data-color="{{ $color }}"
            data-ring="{{ $ring ? '1' : '0' }}"
            data-tone="{{ $tone === 'light' ? 'light' : 'dark' }}"
            role="img"
            aria-label="{{ $label ?? 'Portrait' }}"
            style="display: block; width: 100%; aspect-ratio: 1; cursor: crosshair;"></canvas>
</figure>
@once
    <script src="{{ $scriptUrl }}" defer></script>
@endonce
