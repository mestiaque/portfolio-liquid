@php
    $facts = collect($about['facts'] ?? [])->filter(fn ($f) => filled($f['value'] ?? null));
@endphp
<section id="about" class="section" tabindex="-1" aria-labelledby="about-title">
    <div class="container">
        <header class="section-head reveal">
            <p class="eyebrow"><i class="bi bi-person-badge" aria-hidden="true"></i> {{ $labels['about_eyebrow'] }}</p>
            <h2 id="about-title" class="section-title" data-split-words>{{ $labels['about_title'] }}</h2>
        </header>

        {{-- Row 1: bio and education share equal height; row 2: quick facts as even tiles. --}}
        <div class="row g-4">
            <div class="col-lg-7">
                <article class="glass-card h-100 reveal">
                    <h3 class="card-title-sm">{{ $labels['about_profile'] }}</h3>
                    @foreach ($about['bio'] ?? [] as $paragraph)
                        <p class="lead-text">{{ $paragraph }}</p>
                    @endforeach
                </article>
            </div>

            <div class="col-lg-5">
                <article class="glass-card h-100 reveal">
                    <h3 class="card-title-sm">{{ $labels['about_education'] }}</h3>
                    @if (empty($education))
                        <p class="empty-note">Education details coming soon.</p>
                    @else
                        <ol class="timeline">
                            @foreach ($education as $edu)
                                @php
                                    $period = collect([$edu['start'] ?? null, $edu['end'] ?? null])->filter()->implode(' – ');
                                @endphp
                                <li class="timeline__item reveal">
                                    <span class="timeline__marker" aria-hidden="true"></span>
                                    <div class="timeline__card">
                                        @if ($period)
                                            <p class="timeline__date">{{ $period }}</p>
                                        @endif
                                        <h4 class="timeline__title">{{ $edu['degree'] }}</h4>
                                        <p class="timeline__meta">
                                            <i class="bi bi-bank" aria-hidden="true"></i> {{ $edu['institution'] }}
                                            @if (filled($edu['specialization'] ?? null))
                                                <span class="sep" aria-hidden="true">·</span> {{ $edu['specialization'] }}
                                            @endif
                                        </p>
                                        @if (filled($edu['description'] ?? null))
                                            <p class="timeline__text">{{ $edu['description'] }}</p>
                                        @endif
                                        @if (!empty($edu['credentials']))
                                            <ul class="chip-list" aria-label="Credentials">
                                                @foreach ($edu['credentials'] as $cred)
                                                    <li class="chip"><i class="bi bi-patch-check" aria-hidden="true"></i> {{ $cred }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </article>
            </div>
        </div>

        @if ($facts->isNotEmpty())
            <dl class="facts reveal">
                @foreach ($facts as $fact)
                    <div class="fact">
                        <dt><span class="fact__icon" aria-hidden="true"><i class="bi bi-{{ $fact['icon'] ?? 'dot' }}"></i></span>{{ $fact['label'] }}</dt>
                        <dd>{{ $fact['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
</section>
