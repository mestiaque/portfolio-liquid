{{-- {text, signature (URL), name, date, place} --}}
<p class="cv-text">{{ $data['text'] }}</p>
<div class="cv-sign cv-item">
    <div class="cv-sign__meta">
        @if (filled($data['date']))<p>{{ $cvView->word('date') }}: {{ $data['date'] }}</p>@endif
        @if (filled($data['place']))<p>{{ $cvView->word('place') }}: {{ $data['place'] }}</p>@endif
    </div>
    <div class="cv-sign__box">
        @if ($data['signature'])
            <img src="{{ $data['signature'] }}" alt="" class="cv-sign__img">
        @endif
        <p class="cv-sign__line">{{ $data['name'] }}</p>
    </div>
</div>
