@extends('me::printMaster2')
@section('title', trim($cvView->name() . ' — CV', ' —'))

@push('css')
    <meta name="description" content="{{ trim(($cvView->personal()['designation'] ?? '') . ' · ' . ($cvView->personal()['name_en'] ?? ''), ' ·') }}">
    <meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">
    <link rel="canonical" href="{{ route('cv.show') }}">
    @include('portfolio::cv._assets')
    <script type="application/ld+json">{!! json_encode($cvView->jsonLd(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@section('contents')
    {{-- Beside PrintMaster 2's Print / Close bar; hidden when printing. --}}
    <a class="cv-lang" href="{{ route('cv.show', ['lang' => $cvView->lang === 'bn' ? 'en' : 'bn']) }}" lang="{{ $cvView->lang === 'bn' ? 'en' : 'bn' }}">{{ $cvView->lang === 'bn' ? 'English' : 'বাংলা' }}</a>
    @include($template)
@endsection

@push('js')
    @php $fitFile = dirname((new \ReflectionClass(\ME\Portfolio\PortfolioServiceProvider::class))->getFileName()) . '/public/portfolio/js/fit-pages.js'; @endphp
    <script src="{{ route('portfolio.asset', ['path' => 'js/fit-pages.js']) . (is_file($fitFile) ? '?v=' . filemtime($fitFile) : '') }}"></script>
@endpush
