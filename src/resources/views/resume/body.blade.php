{{--
    ATS-friendly resume. Keep it parser-safe: one column, standard <h2> headings, plain text,
    lists for bullets. No tables, images, icons or columns; URLs are written out in full.
--}}
@php $p = $resume->personal(); @endphp
{{-- fit-pages.js shrinks the font (not below 9pt) if the content would run past one A4 page. --}}
<article class="resume" lang="en" data-fit-pages="1" data-fit-margin="12" data-fit-var="--rs-size" data-fit-min="9">
    <header class="resume-head">
        <h1 class="resume-name">{{ $resume->name() }}</h1>
        @if (filled($p['designation'] ?? ''))
            <p class="resume-headline">{{ $p['designation'] }}</p>
        @endif
        @if ($contact = $resume->contact())
            <p class="resume-contact">{{ implode(' | ', $contact) }}</p>
        @endif
    </header>

    @foreach ($resume->sections() as $section)
        <section class="resume-section">
            <h2>{{ $section['title'] }}</h2>

            @switch($section['key'])
                @case('summary')
                    @foreach (preg_split('/\n\s*\n/', $section['data']) as $para)
                        <p>{{ trim($para) }}</p>
                    @endforeach
                    @break

                @case('skills')
                    @foreach ($section['data'] as $group => $items)
                        <p><strong>{{ $group }}:</strong> {{ $items }}</p>
                    @endforeach
                    @break

                @case('experience')
                    @foreach ($section['data'] as $job)
                        <div class="resume-item">
                            <p class="resume-item__head">
                                <span><strong>{{ $job['designation'] }}</strong>{{ filled($job['organization'] ?? '') ? ', ' . $job['organization'] : '' }}{{ filled($job['location'] ?? '') ? ', ' . $job['location'] : '' }}</span>
                                <span class="resume-date">{{ $resume->period($job['start'] ?? '', $job['end'] ?? '') }}</span>
                            </p>
                            @if ($bullets = $resume->bullets($job))
                                <ul>
                                    @foreach ($bullets as $line)<li>{{ $line }}</li>@endforeach
                                </ul>
                            @endif
                            @if (array_filter((array) ($job['technologies'] ?? [])))
                                <p><strong>Technologies:</strong> {{ implode(', ', array_filter((array) $job['technologies'])) }}</p>
                            @endif
                        </div>
                    @endforeach
                    @break

                @case('projects')
                    {{-- One compact bullet per project, so every project fits on the one page. --}}
                    <ul class="resume-projects">
                        @foreach ($section['data'] as $pr)
                            @php $tech = implode(', ', array_filter((array) ($pr['technologies'] ?? []))); @endphp
                            <li>
                                <strong>{{ $pr['title'] }}</strong>{{ filled($pr['year'] ?? '') ? ' (' . $pr['year'] . ')' : '' }}{{ filled($pr['description'] ?? '') ? ': ' . $pr['description'] : '' }}{{ $tech !== '' ? ' — ' . $tech : '' }}{{ filled($pr['url'] ?? '') ? ' | ' . \ME\Portfolio\Support\Cv\Presenter::shortUrl($pr['url']) : '' }}
                            </li>
                        @endforeach
                    </ul>
                    @break

                @case('education')
                    @foreach ($section['data'] as $edu)
                        <div class="resume-item">
                            <p class="resume-item__head">
                                <span><strong>{{ $edu['exam'] }}</strong>{{ filled($edu['concentration'] ?? '') ? ' in ' . $edu['concentration'] : '' }}{{ filled($edu['institution'] ?? '') ? ', ' . $edu['institution'] : '' }}{{ ($result = \ME\Portfolio\Support\Cv\Presenter::result($edu)) ? ' — ' . $result : '' }}</span>
                                @if (filled($edu['passing_year'] ?? ''))<span class="resume-date">{{ $edu['passing_year'] }}</span>@endif
                            </p>
                        </div>
                    @endforeach
                    @break

                @case('certifications')
                    <ul>
                        @foreach ($section['data'] as $c)
                            <li>{{ $c['name'] }}{{ filled($c['institute'] ?? '') ? ', ' . $c['institute'] : '' }}{{ ($d = filled($c['to'] ?? '') ? $resume->period($c['from'] ?? '', $c['to']) : $resume->date($c['from'] ?? '')) ? ' (' . $d . ')' : '' }}{{ filled($c['credential_url'] ?? '') ? ' — ' . $c['credential_url'] : '' }}</li>
                        @endforeach
                    </ul>
                    @break

                @case('training')
                    <ul>
                        @foreach ($section['data'] as $t)
                            <li>{{ $t['title'] }}{{ filled($t['institute'] ?? '') ? ', ' . $t['institute'] : '' }}{{ filled($t['year'] ?? '') ? ' (' . $t['year'] . ')' : '' }}</li>
                        @endforeach
                    </ul>
                    @break

                @case('awards')
                    <ul>
                        @foreach ($section['data'] as $a)
                            <li>{{ $a['title'] }}{{ filled($a['issuer'] ?? '') ? ', ' . $a['issuer'] : '' }}{{ filled($a['year'] ?? '') ? ' (' . $a['year'] . ')' : '' }}</li>
                        @endforeach
                    </ul>
                    @break

                @case('languages')
                    <p>{{ $section['data'] }}</p>
                    @break
            @endswitch
        </section>
    @endforeach
</article>
