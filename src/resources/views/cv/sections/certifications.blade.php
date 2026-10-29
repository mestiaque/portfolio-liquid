{{-- [{name, institute, location, from, to, credential_id, credential_url}] --}}
@foreach ($data as $c)
    <div class="cv-item">
        <div class="cv-item__head">
            <div>
                <h3 class="cv-item__title">{{ $c['name'] ?? '' }}</h3>
                <p class="cv-item__sub">{{ collect([$c['institute'] ?? '', $c['location'] ?? ''])->filter()->implode(', ') }}</p>
            </div>
            {{-- A certificate isn't ongoing: with no end date, show just when it was earned. --}}
            <p class="cv-item__date">{{ filled($c['to'] ?? '') ? $cvView->period($c['from'] ?? '', $c['to']) : $cvView->date($c['from'] ?? '') }}</p>
        </div>
        @if (filled($c['credential_id'] ?? '') || filled($c['credential_url'] ?? ''))
            <p class="cv-text cv-muted">
                @if (filled($c['credential_id'] ?? ''))Credential ID: {{ $c['credential_id'] }}@endif
                @if (filled($c['credential_url'] ?? '')) · <a href="{{ $c['credential_url'] }}">{{ \ME\Portfolio\Support\Cv\Presenter::shortUrl($c['credential_url']) }}</a>@endif
            </p>
        @endif
    </div>
@endforeach
