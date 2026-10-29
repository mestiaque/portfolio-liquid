{{-- [{organization (e.g. BMDC, IEB), number, year}] --}}
<dl class="cv-pairs">
    @foreach ($data as $r)
        <div class="cv-item">
            <dt>{{ $r['organization'] ?? '' }}</dt>
            <dd>{{ $r['number'] ?? '' }}@if (filled($r['year'] ?? '')) ({{ $r['year'] }})@endif</dd>
        </div>
    @endforeach
</dl>
