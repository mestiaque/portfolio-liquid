{{-- [{title, topic, institute, country, location, year, duration}] --}}
@foreach ($data as $t)
    <div class="cv-item">
        <div class="cv-item__head">
            <div>
                <h3 class="cv-item__title">{{ $t['title'] ?? '' }}</h3>
                <p class="cv-item__sub">{{ collect([$t['institute'] ?? '', $t['location'] ?? '', $t['country'] ?? ''])->filter()->implode(', ') }}</p>
            </div>
            <p class="cv-item__date">{{ $t['year'] ?? '' }}@if (filled($t['duration'] ?? '')) <span class="cv-item__dur">({{ $t['duration'] }})</span>@endif</p>
        </div>
        @if (filled($t['topic'] ?? ''))
            <p class="cv-text">{{ $t['topic'] }}</p>
        @endif
    </div>
@endforeach
