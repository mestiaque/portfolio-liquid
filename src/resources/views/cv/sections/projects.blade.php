{{-- [{title, role, description, technologies[], url, year}] --}}
@foreach ($data as $pr)
    <div class="cv-item">
        <div class="cv-item__head">
            <div>
                <h3 class="cv-item__title">{{ $pr['title'] }}</h3>
                @if (filled($pr['role'] ?? ''))<p class="cv-item__sub">{{ $pr['role'] }}</p>@endif
            </div>
            @if (filled($pr['year'] ?? ''))<p class="cv-item__date">{{ $pr['year'] }}</p>@endif
        </div>
        @if (filled($pr['description'] ?? ''))
            <p class="cv-text">{{ $pr['description'] }}</p>
        @endif
        @if (array_filter((array) ($pr['technologies'] ?? [])) || filled($pr['url'] ?? ''))
            <p class="cv-text cv-muted">
                {{ implode(', ', array_filter((array) ($pr['technologies'] ?? []))) }}
                @if (filled($pr['url'] ?? ''))@if (array_filter((array) ($pr['technologies'] ?? []))) · @endif<a href="{{ $pr['url'] }}">{{ \ME\Portfolio\Support\Cv\Presenter::shortUrl($pr['url']) }}</a>@endif
            </p>
        @endif
    </div>
@endforeach
