@extends('me::master')
@section('title', 'Portfolio Appearance')

@push('buttons')
<a href="{{ route('index') }}" target="_blank" rel="noopener" class="btn btn-sm btn-encodex-list">
    <i class="fas fa-external-link-alt"></i> View site
</a>
@endpush

@push('css')
<style>
    .pa-group-title { margin: 1.25rem 0 .75rem; font-size: .8rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #64748b; }
    .pa-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: .85rem; }
    .pa-card { position: relative; display: block; margin: 0; cursor: pointer; }
    .pa-card input { position: absolute; opacity: 0; pointer-events: none; }
    .pa-card__body {
        height: 100%;
        padding: .7rem;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .pa-card:hover .pa-card__body { transform: translateY(-2px); box-shadow: 0 10px 24px -14px rgba(15, 23, 42, .35); }
    .pa-card input:checked + .pa-card__body { border-color: var(--pa-accent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--pa-accent) 18%, transparent); }
    .pa-card input:focus-visible + .pa-card__body { outline: 2px solid var(--pa-accent); outline-offset: 2px; }
    .pa-check { position: absolute; top: .55rem; right: .55rem; display: none; width: 22px; height: 22px; border-radius: 50%; background: var(--pa-accent); color: #fff; font-size: .7rem; place-items: center; }
    .pa-card input:checked + .pa-card__body .pa-check { display: grid; }
    /* A tiny mock of the page: background, glows, a heading bar and a button in the preset colours. */
    .pa-mock { position: relative; height: 92px; overflow: hidden; border-radius: 10px; background: var(--pa-bg); }
    .pa-mock::before, .pa-mock::after { content: ""; position: absolute; border-radius: 50%; filter: blur(18px); }
    .pa-mock::before { width: 90px; height: 90px; right: -20px; top: -30px; background: var(--pa-t1); opacity: .8; }
    .pa-mock::after { width: 80px; height: 80px; left: -20px; bottom: -40px; background: var(--pa-t2); opacity: .7; }
    .pa-mock__line { position: absolute; left: 12px; height: 7px; border-radius: 4px; z-index: 1; }
    .pa-mock__line--1 { top: 16px; width: 58%; background: var(--pa-heading); }
    .pa-mock__line--2 { top: 30px; width: 36%; background: var(--pa-accent); }
    .pa-mock__line--3 { top: 44px; width: 46%; height: 5px; background: var(--pa-heading); opacity: .25; }
    .pa-mock__btn { position: absolute; left: 12px; bottom: 12px; z-index: 1; width: 52px; height: 16px; border-radius: 999px; background: var(--pa-grad); }
    .pa-mock__dot { position: absolute; right: 14px; bottom: 12px; z-index: 1; width: 38px; height: 38px; border-radius: 50%; background: var(--pa-disc); box-shadow: 0 0 0 3px color-mix(in srgb, var(--pa-portrait) 55%, transparent); }
    .pa-mock__dot::after { content: ""; position: absolute; inset: 11px; border-radius: 50%; background: radial-gradient(circle, var(--pa-portrait) 1.5px, transparent 2px) 0 0 / 5px 5px; }
    .pa-name { margin: .55rem 0 0; font-weight: 700; color: #0f172a; line-height: 1.25; }
    .pa-name small { display: block; color: #64748b; font-weight: 500; font-size: .78rem; }
    .pa-note { margin: .3rem 0 0; color: #64748b; font-size: .75rem; line-height: 1.35; }
    .pa-swatches { display: flex; gap: 4px; margin-top: .45rem; }
    .pa-swatches span { width: 16px; height: 16px; border-radius: 50%; border: 1px solid rgba(15, 23, 42, .12); }

    .pa-cursors { display: grid; gap: .5rem; }
    .pa-cursor { display: flex; align-items: center; gap: .6rem; margin: 0; padding: .55rem .75rem; border: 1px solid #e2e8f0; border-radius: 10px; cursor: pointer; }
    .pa-cursor:has(input:checked) { border-color: #2563eb; background: #eff6ff; }
    .pa-cursor small { color: #64748b; }
    .pa-preview { display: grid; place-items: center; height: 150px; margin-top: .75rem; border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; color: #64748b; font-size: .85rem; text-align: center; }

    .pa-stage { display: grid; place-items: center; padding: 1rem; border-radius: 16px; border: 1px solid #e2e8f0; transition: background-color .25s; }
    .pa-subtitle { margin: 0 0 .4rem; font-size: .78rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #64748b; }
    .pa-choice { display: flex; flex-wrap: wrap; align-items: center; gap: .35rem .9rem; margin-bottom: .9rem; }
    .pa-choice label { display: inline-flex; align-items: center; gap: .35rem; margin: 0; font-size: .875rem; cursor: pointer; }
    .pa-choice input[type="color"] { width: 38px; height: 28px; padding: 0 2px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; cursor: pointer; }
    .pa-range { display: grid; grid-template-columns: 150px minmax(0, 1fr) 46px; align-items: center; gap: .6rem; margin-bottom: .35rem; font-size: .85rem; }
    .pa-range output { font-variant-numeric: tabular-nums; color: #475569; text-align: right; }
    .pa-range input { width: 100%; }
    @media (max-width: 575.98px) { .pa-range { grid-template-columns: 1fr 46px; } .pa-range span { grid-column: 1 / -1; } }
</style>
@endpush

@section('content')
<form action="{{ route('admin.appearance.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    @php
        $studioPhoto = $photoUrl ?: $fallbackPhoto;
        $resolved = \ME\Portfolio\Support\Theme::portrait($presets[$themeId] ?? [], $portrait);
        $studioPresets = collect($presets)->map(fn ($p) => [
            'accent' => $p['accent'], 'bg' => $p['bg'], 'dark' => ($p['mode'] ?? 'light') === 'dark',
            'color' => $p['portrait']['color'] ?? $p['accent'], 'disc' => $p['portrait']['disc'] ?? '#0f172a',
        ]);
        // name => [label, min, max, step]
        $sliders = [
            'zoom'       => ['Zoom', 1, 3, 0.05],
            'x'          => ['Move left / right', -1, 1, 0.02],
            'y'          => ['Move up / down', -1, 1, 0.02],
            'brightness' => ['Brightness', -0.5, 0.5, 0.02],
            'contrast'   => ['Contrast', 0.4, 2.5, 0.05],
            'grid'       => ['Detail (dots per row)', 24, 80, 2],
            'dot'        => ['Dot size', 0.5, 1.6, 0.05],
        ];
    @endphp
    <div class="card mb-3" data-studio>
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle me-1"></i> Portrait studio</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-studio-reset><i class="fas fa-undo me-1"></i> Reset adjustments</button>
        </div>
        <div class="card-body">
            @if ($errors->has('portrait.*') || $errors->has('photo'))
                <div class="alert alert-danger py-2">{{ $errors->first('photo') ?: collect($errors->get('portrait.*'))->flatten()->first() }}</div>
            @endif
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="pa-stage" data-studio-stage style="background-color: {{ $presets[$themeId]['bg'] ?? '#eef2fa' }}">
                        @if ($studioPhoto)
                            <x-portfolio::halftone-portrait class="pa-studio-portrait" :src="$studioPhoto" :color="$resolved['color']" :disc="$resolved['disc']"
                                :adjust="$resolved" :tone="($presets[$themeId]['mode'] ?? 'light') === 'dark' ? 'dark' : 'light'" size="340" label="Portrait preview" />
                        @else
                            <p class="text-muted my-5">Upload a picture to see the portrait.</p>
                        @endif
                    </div>
                    <p class="small text-muted mt-2 mb-2">
                        Live preview on the selected preset's background. {{ $photoUrl ? 'Using your uploaded picture.' : 'Using the picture from config/portfolio.php.' }}
                    </p>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                           class="form-control form-control-sm @error('photo') is-invalid @enderror" data-photo-input>
                    @if ($photoUrl)
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="pa-remove-photo" data-remove-photo>
                            <label class="form-check-label small" for="pa-remove-photo">Remove uploaded picture (go back to the config picture)</label>
                        </div>
                    @endif
                </div>

                <div class="col-lg-7">
                    <p class="pa-subtitle">Dot colour</p>
                    <div class="pa-choice">
                        @foreach (['preset' => 'Preset colour', 'accent' => 'Match accent', 'custom' => 'Custom'] as $mode => $label)
                            <label><input type="radio" name="portrait[color_mode]" value="{{ $mode }}" @checked(old('portrait.color_mode', $portrait['color_mode']) === $mode)> {{ $label }}</label>
                        @endforeach
                        <input type="color" name="portrait[color]" value="{{ old('portrait.color', $portrait['color']) }}" aria-label="Custom dot colour">
                    </div>

                    <p class="pa-subtitle">Disc behind the dots <small class="text-muted text-lowercase fw-normal">(light presets)</small></p>
                    <div class="pa-choice">
                        @foreach (['preset' => 'Preset colour', 'custom' => 'Custom'] as $mode => $label)
                            <label><input type="radio" name="portrait[disc_mode]" value="{{ $mode }}" @checked(old('portrait.disc_mode', $portrait['disc_mode']) === $mode)> {{ $label }}</label>
                        @endforeach
                        <input type="color" name="portrait[disc]" value="{{ old('portrait.disc', $portrait['disc']) }}" aria-label="Custom disc colour">
                    </div>

                    <p class="pa-subtitle">Image</p>
                    @foreach ($sliders as $key => [$label, $min, $max, $step])
                        <label class="pa-range">
                            <span>{{ $label }}</span>
                            <input type="range" name="portrait[{{ $key }}]" min="{{ $min }}" max="{{ $max }}" step="{{ $step }}"
                                   value="{{ old('portrait.' . $key, $portrait[$key]) }}" data-default="{{ \ME\Portfolio\Support\Theme::PORTRAIT_DEFAULTS[$key] }}">
                            <output>{{ old('portrait.' . $key, $portrait[$key]) }}</output>
                        </label>
                    @endforeach

                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <div class="form-check">
                            <input type="hidden" name="portrait[bg]" value="keep">
                            <input class="form-check-input" type="checkbox" name="portrait[bg]" value="auto" id="pa-bg" @checked(old('portrait.bg', $portrait['bg']) === 'auto')>
                            <label class="form-check-label small" for="pa-bg">Remove plain white background</label>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="portrait[invert]" value="0">
                            <input class="form-check-input" type="checkbox" name="portrait[invert]" value="1" id="pa-invert" @checked((bool) old('portrait.invert', $portrait['invert']))>
                            <label class="form-check-label small" for="pa-invert">Invert (dark areas get the big dots)</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header py-2">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-palette me-1"></i> Colour preset</h6>
                </div>
                <div class="card-body pt-1">
                    @error('theme') <div class="alert alert-danger py-2 mt-2">{{ $message }}</div> @enderror
                    @foreach ($groups as $groupId => $groupLabel)
                        @php $inGroup = collect($presets)->where('group', $groupId); @endphp
                        @continue($inGroup->isEmpty())
                        <p class="pa-group-title">{{ $groupLabel }}</p>
                        <div class="pa-grid">
                            @foreach ($inGroup as $id => $p)
                                @php
                                    $dark = ($p['mode'] ?? 'light') === 'dark';
                                    $style = collect([
                                        '--pa-bg' => $p['bg'],
                                        '--pa-accent' => $p['accent'],
                                        '--pa-accent-2' => $p['accent_2'],
                                        '--pa-t1' => $p['tints'][0] ?? $p['accent'],
                                        '--pa-t2' => $p['tints'][1] ?? $p['accent_2'],
                                        '--pa-heading' => $dark ? '#e2e8f0' : '#0b1222',
                                        '--pa-portrait' => $p['portrait']['color'] ?? $p['accent'],
                                        '--pa-disc' => $p['portrait']['disc'] ?? '#0f172a',
                                        '--pa-grad' => $p['gradient'] ?? "linear-gradient(120deg, {$p['accent']}, {$p['accent_2']})",
                                    ])->map(fn ($v, $k) => "$k: $v")->implode('; ');
                                @endphp
                                <label class="pa-card" style="{{ $style }}">
                                    <input type="radio" name="theme" value="{{ $id }}" @checked(old('theme', $themeId) === $id)>
                                    <span class="pa-card__body d-block">
                                        <span class="pa-check"><i class="fas fa-check"></i></span>
                                        <span class="pa-mock d-block" aria-hidden="true">
                                            <span class="pa-mock__line pa-mock__line--1"></span>
                                            <span class="pa-mock__line pa-mock__line--2"></span>
                                            <span class="pa-mock__line pa-mock__line--3"></span>
                                            <span class="pa-mock__btn"></span>
                                            <span class="pa-mock__dot"></span>
                                        </span>
                                        <span class="pa-name d-block">{{ $p['name'] }} <small>{{ $p['name_en'] }}</small></span>
                                        <span class="pa-swatches" aria-hidden="true">
                                            @foreach ([$p['accent'], $p['accent_2'], ...($p['tints'] ?? [])] as $c)
                                                <span style="background: {{ $c }}"></span>
                                            @endforeach
                                        </span>
                                        @if (filled($p['note'] ?? null))
                                            <span class="pa-note d-block">{{ $p['note'] }}</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header py-2">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-mouse-pointer me-1"></i> Cursor trail</h6>
                </div>
                <div class="card-body">
                    @error('cursor') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror
                    <div class="pa-cursors">
                        @foreach ($cursors as $id => $label)
                            @php [$title, $hint] = array_pad(explode(' · ', $label, 2), 2, ''); @endphp
                            <label class="pa-cursor">
                                <input type="radio" name="cursor" value="{{ $id }}" @checked(old('cursor', $cursor) === $id)>
                                <span><b>{{ $title }}</b> <small>{{ $hint }}</small></span>
                            </label>
                        @endforeach
                    </div>
                    <div class="pa-preview" data-cursor-preview>Move your mouse anywhere on this page<br>to preview the selected trail.</div>
                    <p class="small text-muted mt-2 mb-0">Shown only on devices with a mouse, and turned off for visitors who prefer reduced motion.</p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3"><i class="fas fa-save me-1"></i> Save appearance</button>
        </div>
    </div>
</form>
@endsection

@push('js')
<script src="{{ route('portfolio.asset', ['path' => 'js/cursor-trail.js']) }}?v={{ @filemtime(dirname((new \ReflectionClass(\ME\Portfolio\PortfolioServiceProvider::class))->getFileName()) . '/public/portfolio/js/cursor-trail.js') }}"></script>
<script>
    (() => {
        // Live cursor preview: switch the trail as soon as a radio is picked.
        const radios = document.querySelectorAll('input[name="cursor"]');
        const accent = () => getComputedStyle(document.querySelector('.pa-card input:checked')?.closest('.pa-card') || document.body).getPropertyValue('--pa-accent').trim() || '#2563eb';
        const apply = () => {
            const picked = document.querySelector('input[name="cursor"]:checked');
            window.PortfolioCursor?.start(picked ? picked.value : 'none', { color: accent() });
        };
        radios.forEach((r) => r.addEventListener('change', apply));
        document.querySelectorAll('input[name="theme"]').forEach((r) => r.addEventListener('change', apply));
        apply();

        // Portrait studio: mirror every control onto the preview canvas and redraw.
        const studio = document.querySelector('[data-studio]');
        const canvas = studio?.querySelector('.pa-studio-portrait canvas');
        const stage = studio?.querySelector('[data-studio-stage]');
        const presets = @json($studioPresets);
        const fallbackSrc = canvas?.dataset.src || '';
        const field = (name) => studio.querySelector(`[name="portrait[${name}]"]:not([type="hidden"])`);
        const picked = (name) => studio.querySelector(`[name="portrait[${name}]"]:checked`)?.value;

        const updateStudio = () => {
            if (!canvas) return;
            const theme = presets[document.querySelector('input[name="theme"]:checked')?.value] || Object.values(presets)[0];
            const colorMode = picked('color_mode') || 'preset';
            const ds = canvas.dataset;
            ds.color = colorMode === 'custom' ? field('color').value : (colorMode === 'accent' ? theme.accent : theme.color);
            ds.disc = picked('disc_mode') === 'custom' ? field('disc').value : theme.disc;
            ds.tone = theme.dark ? 'dark' : 'light';
            studio.querySelectorAll('input[type="range"]').forEach((r) => {
                const key = r.name.match(/\[(\w+)\]/)[1];
                ds[key] = r.value;
                r.nextElementSibling.textContent = r.value;
            });
            ds.bg = field('bg').checked ? 'auto' : 'keep';
            ds.invert = field('invert').checked ? '1' : '0';
            stage.style.backgroundColor = theme.bg;
            canvas.halftone?.refresh();
        };
        studio?.addEventListener('input', updateStudio);
        studio?.addEventListener('change', updateStudio);
        document.querySelectorAll('input[name="theme"]').forEach((r) => r.addEventListener('change', updateStudio));
        // The halftone script is deferred, so its refresh() exists only after load.
        window.addEventListener('load', updateStudio);

        // A newly chosen file is previewed straight away; "remove" previews the config picture.
        const input = studio?.querySelector('[data-photo-input]');
        input?.addEventListener('change', () => {
            const file = input.files && input.files[0];
            if (!file || !canvas) return;
            canvas.dataset.src = URL.createObjectURL(file);
            canvas.halftone?.refresh();
        });
        studio?.querySelector('[data-remove-photo]')?.addEventListener('change', (e) => {
            if (!canvas) return;
            canvas.dataset.src = e.target.checked ? @json($fallbackPhoto ?? '') : fallbackSrc;
            canvas.halftone?.refresh();
        });

        studio?.querySelector('[data-studio-reset]')?.addEventListener('click', () => {
            studio.querySelectorAll('input[type="range"]').forEach((r) => { r.value = r.dataset.default; });
            field('bg').checked = true;
            field('invert').checked = false;
            updateStudio();
        });
    })();
</script>
@endpush
