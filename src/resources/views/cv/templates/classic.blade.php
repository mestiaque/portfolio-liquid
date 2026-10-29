{{-- Classic BD: one column, section titles with a rule under them, photo top-right, education as a table. --}}
@php
    $p = $cvView->personal();
    $photo = $cvView->photo();
    $links = array_filter([
        'LinkedIn' => $p['linkedin'] ?? '',
        'GitHub'   => $p['github'] ?? '',
        'Portfolio' => $p['website'] ?? '',
    ]);
@endphp
{{-- fit-pages.js shrinks the font (not below 9pt) if the CV would run past two pages. --}}
<article class="cv cv--classic {{ $cvView->theme['compact'] ? 'cv--compact' : '' }}" lang="{{ $cvView->lang }}"
         data-fit-pages="2" data-fit-var="--cv-size" data-fit-min="9"
         data-fit-margin="{{ (int) $cvView->theme['margin'] }}"
         data-fit-paper-w="{{ $cvView->theme['paper'] === 'Letter' ? 215.9 : 210 }}" data-fit-paper-h="{{ $cvView->theme['paper'] === 'Letter' ? 279.4 : 297 }}">
    <header class="cv-head">
        <div class="cv-head__main">
            <h1 class="cv-name">{{ $cvView->name() }}</h1>
            @if (filled($p['designation'] ?? ''))
                <p class="cv-headline">{{ $p['designation'] }}</p>
            @endif

            <ul class="cv-contact">
                @foreach ((array) ($p['phones'] ?? []) as $phone)
                    <li><span class="cv-contact__k">{{ $cvView->word('phone') }}:</span> <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a></li>
                @endforeach
                @if (filled($p['email'] ?? ''))
                    <li><span class="cv-contact__k">{{ $cvView->word('email') }}:</span> <a href="mailto:{{ $p['email'] }}">{{ $p['email'] }}</a></li>
                @endif
                @if (filled($p['whatsapp'] ?? ''))
                    <li><span class="cv-contact__k">WhatsApp:</span> {{ $p['whatsapp'] }}</li>
                @endif
                @foreach ($links as $label => $url)
                    <li><span class="cv-contact__k">{{ $label }}:</span> <a href="{{ $url }}">{{ \ME\Portfolio\Support\Cv\Presenter::shortUrl($url) }}</a></li>
                @endforeach
            </ul>
        </div>
        @if ($photo)
            <img class="cv-photo" src="{{ $photo }}" alt="Photo of {{ $p['name_en'] ?? '' }}" width="113" height="140">
        @endif
    </header>

    @foreach ($cvView->sections() as $section)
        <section class="cv-section cv-section--{{ $section['key'] }}">
            <h2 class="cv-title">{{ $section['title'] }}</h2>
            @include('portfolio::cv.sections.' . $section['key'], ['data' => $section['data']])
        </section>
    @endforeach
</article>
