<section id="jobs" class="section" tabindex="-1" aria-labelledby="jobs-title">
    <div class="container">
        <header class="section-head reveal">
            <p class="eyebrow"><i class="bi bi-briefcase" aria-hidden="true"></i> Jobs</p>
            <h2 id="jobs-title" class="section-title">Work experience</h2>
        </header>

        @if (empty($experience))
            <p class="empty-state">Work history coming soon.</p>
        @else
            <ol class="timeline timeline--jobs">
                @foreach ($experience as $job)
                    @php
                        $end = $job['end'] ?? null;
                        $isCurrent = blank($end);
                        $period = collect([$job['start'] ?? null, $isCurrent ? 'Present' : $end])->filter()->implode(' – ');
                    @endphp
                    <li class="timeline__item reveal">
                        <span class="timeline__marker {{ $isCurrent ? 'is-current' : '' }}" aria-hidden="true"></span>
                        <article class="glass-card job">
                            <div class="job__head">
                                <div>
                                    <h3 class="job__title">{{ $job['title'] }}</h3>
                                    <p class="job__company">
                                        <i class="bi bi-building" aria-hidden="true"></i> {{ $job['company'] }}
                                    </p>
                                </div>
                                <ul class="job__meta" aria-label="Role details">
                                    @if ($period)
                                        <li class="{{ $isCurrent ? 'is-current' : '' }}">
                                            <i class="bi bi-calendar3" aria-hidden="true"></i> {{ $period }}
                                        </li>
                                    @endif
                                    @if (filled($job['employment_type'] ?? null))
                                        <li><i class="bi bi-clock" aria-hidden="true"></i> {{ $job['employment_type'] }}</li>
                                    @endif
                                    @if (filled($job['location'] ?? null))
                                        <li><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $job['location'] }}</li>
                                    @endif
                                </ul>
                            </div>

                            @if (filled($job['company_summary'] ?? null))
                                <p class="job__company-summary">{{ $job['company_summary'] }}</p>
                            @endif
                            @if (filled($job['summary'] ?? null))
                                <p class="job__summary">{{ $job['summary'] }}</p>
                            @endif

                            @if (!empty($job['responsibilities']))
                                <h4 class="job__label">Responsibilities</h4>
                                <ul class="tick-list">
                                    @foreach ($job['responsibilities'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (!empty($job['achievements']))
                                <h4 class="job__label">Highlights</h4>
                                <ul class="tick-list tick-list--star">
                                    @foreach ($job['achievements'] as $item)
                                        <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (!empty($job['technologies']))
                                <ul class="chip-list" aria-label="Technologies used">
                                    @foreach ($job['technologies'] as $tech)
                                        <li class="chip">{{ $tech }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </article>
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</section>
