{{-- {id, type: text | bullets | table | timeline, content, items} --}}
@switch($data['type'] ?? 'text')
    @case('bullets')
        <ul class="cv-bullets">
            @foreach ((array) ($data['items'] ?? []) as $line)@if (filled($line))<li>{{ $line }}</li>@endif @endforeach
        </ul>
        @break
    @case('table')
        <dl class="cv-pairs">
            @foreach ((array) ($data['items'] ?? []) as $row)
                <div class="cv-item"><dt>{{ $row['key'] ?? '' }}</dt><dd>{{ $row['value'] ?? '' }}</dd></div>
            @endforeach
        </dl>
        @break
    @case('timeline')
        @foreach ((array) ($data['items'] ?? []) as $row)
            <div class="cv-item">
                <div class="cv-item__head">
                    <h3 class="cv-item__title">{{ $row['title'] ?? '' }}</h3>
                    <p class="cv-item__date">{{ $row['period'] ?? '' }}</p>
                </div>
                @if (filled($row['description'] ?? ''))<p class="cv-text">{{ $row['description'] }}</p>@endif
            </div>
        @endforeach
        @break
    @default
        <p class="cv-text">{!! nl2br(e($data['content'] ?? '')) !!}</p>
@endswitch
