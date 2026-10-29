{{-- [{title, issuer, year, description}] --}}
<ul class="cv-bullets">
    @foreach ($data as $a)
        <li class="cv-item">
            <strong>{{ $a['title'] ?? '' }}</strong>{{ filled($a['issuer'] ?? '') ? ', ' . $a['issuer'] : '' }}{{ filled($a['year'] ?? '') ? ' (' . $a['year'] . ')' : '' }}@if (filled($a['description'] ?? '')) — {{ $a['description'] }}@endif
        </li>
    @endforeach
</ul>
