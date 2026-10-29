{{-- {items: [{name, level}], typing_bn_wpm, typing_en_wpm} --}}
@php $items = collect($data['items'] ?? [])->filter(fn ($i) => filled($i['name'] ?? '')); @endphp
<dl class="cv-pairs">
    @if ($items->isNotEmpty())
        <div class="cv-item">
            <dt>Software</dt>
            <dd>{{ $items->map(fn ($i) => $i['name'] . (filled($i['level'] ?? '') ? ' (' . $i['level'] . ')' : ''))->implode(', ') }}</dd>
        </div>
    @endif
    @if (filled($data['typing_bn_wpm'] ?? '') || filled($data['typing_en_wpm'] ?? ''))
        <div class="cv-item">
            <dt>{{ $cvView->word('typing') }}</dt>
            <dd>{{ collect([filled($data['typing_bn_wpm'] ?? '') ? 'Bangla ' . $data['typing_bn_wpm'] . ' WPM' : '', filled($data['typing_en_wpm'] ?? '') ? 'English ' . $data['typing_en_wpm'] . ' WPM' : ''])->filter()->implode(', ') }}</dd>
        </div>
    @endif
</dl>
