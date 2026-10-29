{{-- [{title, role, organization, year, description}] --}}
<ul class="cv-bullets">
    @foreach ($data as $x)
        <li class="cv-item">
            <strong>{{ $x['title'] ?? '' }}</strong>{{ collect([$x['role'] ?? '', $x['organization'] ?? ''])->filter()->map(fn ($v) => ', ' . $v)->implode('') }}{{ filled($x['year'] ?? '') ? ' (' . $x['year'] . ')' : '' }}@if (filled($x['description'] ?? '')) — {{ $x['description'] }}@endif
        </li>
    @endforeach
</ul>
