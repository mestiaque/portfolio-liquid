@if (filled($data['objective']))
    <p class="cv-text">{!! nl2br(e($data['objective'])) !!}</p>
@endif
@if (filled($data['summary']))
    <p class="cv-text">{!! nl2br(e($data['summary'])) !!}</p>
@endif
