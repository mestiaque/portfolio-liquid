{{-- {upon_request, items: [{name, designation, organization, address, mobile, email, relation}]} --}}
@if (!empty($data['upon_request']) || empty($data['items']))
    <p class="cv-text">{{ $cvView->word('upon_request') }}</p>
@else
    <div class="cv-refs">
        @foreach ($data['items'] as $r)
            <div class="cv-item cv-ref">
                <h3 class="cv-item__title">{{ $r['name'] ?? '' }}</h3>
                @foreach (['designation', 'organization', 'address'] as $k)
                    @if (filled($r[$k] ?? ''))<p>{{ $r[$k] }}</p>@endif
                @endforeach
                @if (filled($r['mobile'] ?? ''))<p>{{ $cvView->word('phone') }}: {{ $r['mobile'] }}</p>@endif
                @if (filled($r['email'] ?? ''))<p>{{ $cvView->word('email') }}: {{ $r['email'] }}</p>@endif
                @if (filled($r['relation'] ?? ''))<p class="cv-muted">Relation: {{ $r['relation'] }}</p>@endif
            </div>
        @endforeach
    </div>
@endif
