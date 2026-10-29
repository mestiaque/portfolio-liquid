@extends('me::printMaster2')
@section('title', trim($resume->name() . ' — Resume', ' —'))

@push('css')
    @php
        $cssFile = dirname((new \ReflectionClass(\ME\Portfolio\PortfolioServiceProvider::class))->getFileName()) . '/public/portfolio/css/resume.css';
    @endphp
    <meta name="description" content="{{ trim(($resume->personal()['designation'] ?? '') . ' · ' . ($resume->personal()['name_en'] ?? ''), ' ·') }}">
    <meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">
    <link rel="canonical" href="{{ route('resume.show') }}">
    <link rel="stylesheet" href="{{ route('portfolio.asset', ['path' => 'css/resume.css']) . (is_file($cssFile) ? '?v=' . filemtime($cssFile) : '') }}">
    <script type="application/ld+json">{!! json_encode($resume->jsonLd(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('contents')
    @include('portfolio::resume.body')
@endsection

@push('js')
    @php $fitFile = dirname((new \ReflectionClass(\ME\Portfolio\PortfolioServiceProvider::class))->getFileName()) . '/public/portfolio/js/fit-pages.js'; @endphp
    <script src="{{ route('portfolio.asset', ['path' => 'js/fit-pages.js']) . (is_file($fitFile) ? '?v=' . filemtime($fitFile) : '') }}"></script>
@endpush
