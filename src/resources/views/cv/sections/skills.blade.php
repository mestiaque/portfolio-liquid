{{-- [{group, items: [{name, level}]}]; a level shows as text, never as a bar, so it prints cleanly --}}
<dl class="cv-pairs">
    @foreach ($data as $group)
        @php $items = collect($group['items'] ?? [])->filter(fn ($i) => filled($i['name'] ?? '')); @endphp
        @if ($items->isNotEmpty())
            <div class="cv-item">
                <dt>{{ $group['group'] ?? '' }}</dt>
                <dd>{{ $items->map(fn ($i) => $i['name'] . (filled($i['level'] ?? '') ? ' (' . $i['level'] . ')' : ''))->implode(', ') }}</dd>
            </div>
        @endif
    @endforeach
</dl>
