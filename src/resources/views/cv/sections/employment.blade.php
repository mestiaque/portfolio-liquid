{{-- [{organization, designation, department, location, start, end (empty = continuing), summary, responsibilities[], achievements[]}] --}}
@foreach ($data as $job)
    <div class="cv-item">
        <div class="cv-item__head">
            <div>
                <h3 class="cv-item__title">{{ $job['designation'] ?? '' }}</h3>
                <p class="cv-item__sub">
                    {{ collect([$job['department'] ?? '', $job['organization'] ?? ''])->filter()->implode(', ') }}@if (filled($job['location'] ?? '')) — {{ $job['location'] }}@endif
                </p>
            </div>
            <p class="cv-item__date">
                {{ $cvView->period($job['start'] ?? '', $job['end'] ?? '') }}
                @if ($d = $cvView->duration($job['start'] ?? '', $job['end'] ?? ''))<span class="cv-item__dur">({{ $d }})</span>@endif
            </p>
        </div>
        @if (filled($job['summary'] ?? ''))
            <p class="cv-text">{{ $job['summary'] }}</p>
        @endif
        @if (array_filter((array) ($job['responsibilities'] ?? [])))
            <p class="cv-label">{{ $cvView->word('responsibilities') }}</p>
            <ul class="cv-bullets">
                @foreach (array_filter((array) $job['responsibilities']) as $line)<li>{{ $line }}</li>@endforeach
            </ul>
        @endif
        @if (array_filter((array) ($job['achievements'] ?? [])))
            <p class="cv-label">{{ $cvView->word('achievements') }}</p>
            <ul class="cv-bullets">
                @foreach (array_filter((array) $job['achievements']) as $line)<li>{{ $line }}</li>@endforeach
            </ul>
        @endif
    </div>
@endforeach
