@extends('me::master')
@section('title', 'CV & Resume')

@php
    $photoPath = (string) ($values['personal']['photo'] ?? '');
    $signaturePath = (string) ($values['declaration']['signature'] ?? '');
    $layoutTitles = collect($layout)->keyBy('key');
    // Tab title: the section's own English title from the layout, else the built-in one.
    $tabTitle = fn ($key) => $layoutTitles[$key]['title_en'] ?? \ME\Portfolio\Support\Cv\Sections::TITLES[$key][0];
@endphp

@push('buttons')
<a href="{{ route('cv.show') }}" target="_blank" rel="noopener" class="btn btn-sm btn-encodex-list"><i class="fas fa-external-link-alt"></i> CV</a>
<a href="{{ route('cv.show', ['lang' => 'bn']) }}" target="_blank" rel="noopener" class="btn btn-sm btn-encodex-list"><i class="fas fa-external-link-alt"></i> বাংলা CV</a>
<a href="{{ route('resume.show') }}" target="_blank" rel="noopener" class="btn btn-sm btn-encodex-list"><i class="fas fa-external-link-alt"></i> Resume</a>
@endpush

@push('css')
<style>
    .ps-wrap { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: 1rem; align-items: start; }
    @media (max-width: 991.98px) { .ps-wrap { grid-template-columns: 1fr; } }
    .ps-nav { position: sticky; top: 1rem; display: grid; gap: .15rem; padding: .5rem; max-height: calc(100vh - 2rem); overflow-y: auto; }
    .ps-nav__group { margin: .5rem .75rem .15rem; font-size: .7rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #94a3b8; }
    .ps-nav button { display: flex; align-items: center; gap: .6rem; width: 100%; padding: .5rem .75rem; border: 0; border-radius: 10px; background: none; color: #334155; font-size: .88rem; text-align: left; }
    .ps-nav button i { width: 18px; color: #64748b; text-align: center; }
    .ps-nav button:hover { background: #f1f5f9; }
    .ps-nav button.is-active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
    .ps-nav button.is-active i { color: #1d4ed8; }
    .ps-nav .ps-off { margin-left: auto; font-size: .68rem; color: #94a3b8; }
    .ps-pane[hidden] { display: none !important; }
    .ps-pane__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: 1rem; }
    .ps-pane__head h5 { margin: 0; font-weight: 700; }
    .ps-label { font-weight: 600; font-size: .85rem; color: #334155; }
    .ps-repeater { padding: .75rem; border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; }
    .ps-repeater__head { display: flex; flex-wrap: wrap; align-items: baseline; gap: .5rem; margin-bottom: .5rem; }
    .ps-repeater__items { display: grid; gap: .6rem; margin-bottom: .6rem; }
    .ps-item { padding: .65rem .75rem .8rem; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; }
    .ps-item__bar { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .4rem; }
    .ps-item__title { overflow: hidden; font-weight: 600; font-size: .85rem; color: #0f172a; white-space: nowrap; text-overflow: ellipsis; }
    .ps-item__tools { display: flex; gap: .25rem; flex: none; }
    .ps-item__tools .btn { padding: .15rem .45rem; }
    .ps-savebar { position: sticky; bottom: 0; z-index: 5; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .75rem; margin-top: 1rem; padding: .75rem 1rem; border: 1px solid #e2e8f0; border-radius: 12px; background: rgba(255, 255, 255, .95); box-shadow: 0 -8px 24px -16px rgba(15, 23, 42, .35); }
    .cv-layout { display: grid; gap: .4rem; }
    .cv-layout .ps-item { display: grid; grid-template-columns: auto minmax(0, 1fr) minmax(0, 1fr) auto auto auto; align-items: center; gap: .5rem .75rem; padding: .5rem .75rem; }
    .cv-layout .ps-item.is-off { background: #f8fafc; opacity: .75; }
    @media (max-width: 767.98px) { .cv-layout .ps-item { grid-template-columns: auto 1fr auto; } .cv-layout .cv-layout__title { grid-column: 2 / -1; } }
    .cv-file { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; padding: .75rem; border: 1px dashed #cbd5e1; border-radius: 12px; background: #f8fafc; }
    .cv-file img { width: 84px; height: 84px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; }
    .cv-file img.is-signature { width: 160px; height: 60px; object-fit: contain; }
</style>
@endpush

@section('content')
{{-- Fill from the portfolio: rebuilds the parts the portfolio already knows. --}}
<div class="card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="me-auto">
            <h6 class="mb-1 fw-bold"><i class="fas fa-magic text-primary me-1"></i> Fill from the portfolio</h6>
            <p class="small text-muted mb-0">
                Rebuilds personal details, career summary, employment, education and skills from Portfolio Settings.
                The Bangla name, permanent address, career objective and an uploaded CV photo are kept. Everything else here stays as it is.
            </p>
        </div>
        <form action="{{ route(me_prefix() . '.cv.refill') }}" method="POST"
              onsubmit="return confirm('Replace personal details, career summary, employment, education and skills with the portfolio data? Unsaved changes on this page are lost.');">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync-alt me-1"></i> Fill from portfolio</button>
        </form>
    </div>
</div>

<form action="{{ route(me_prefix() . '.cv.update') }}" method="POST" enctype="multipart/form-data" data-ps-form>
    @csrf
    @method('PUT')
    <input type="hidden" name="tab" value="" data-ps-tab>

    <div class="ps-wrap">
        <nav class="card ps-nav" aria-label="CV sections">
            <div class="ps-nav__group">CV</div>
            <button type="button" data-ps-open="access"><i class="fas fa-lock" aria-hidden="true"></i> Visibility &amp; preset</button>
            <button type="button" data-ps-open="layout"><i class="fas fa-sort" aria-hidden="true"></i> Sections &amp; order</button>
            <button type="button" data-ps-open="theme"><i class="fas fa-paint-brush" aria-hidden="true"></i> Look</button>
            <div class="ps-nav__group">Content</div>
            @foreach ($sections as $key => $def)
                <button type="button" data-ps-open="{{ $key }}">
                    <i class="{{ $def['icon'] }}" aria-hidden="true"></i> {{ $tabTitle($key) }}
                    @if ($key !== 'personal' && empty($layoutTitles[$key]['enabled'])) <span class="ps-off">off</span> @endif
                </button>
            @endforeach
            <button type="button" data-ps-open="custom"><i class="fas fa-plus-square" aria-hidden="true"></i> Custom sections</button>
        </nav>

        <div>
            {{-- Visibility & preset --}}
            <section class="card ps-pane" data-ps-pane="access" hidden>
                <div class="card-body">
                    <div class="ps-pane__head"><h5><i class="fas fa-lock me-2 text-primary"></i>Visibility &amp; preset</h5></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label ps-label" for="cv-visibility">Who can see /cv and /resume</label>
                            <select class="form-select form-select-sm" name="visibility" id="cv-visibility">
                                @foreach (['public' => 'Public: anyone, listed in search engines', 'unlisted' => 'Unlisted: anyone with the link, not in search engines', 'password' => 'Password: visitors enter a password', 'private' => 'Private: only signed-in users'] as $v => $label)
                                    <option value="{{ $v }}" @selected(old('visibility', $cv->visibility ?: 'public') === $v)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label ps-label" for="cv-password">Password</label>
                            <input type="password" class="form-control form-control-sm" name="password" id="cv-password" autocomplete="new-password"
                                   placeholder="{{ filled($cv->password) ? 'A password is set; type a new one to change it' : 'Needed for the Password option' }}">
                            <div class="form-text">At least 4 characters. Only used when visibility is "Password".</div>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="hidden" name="noindex" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" name="noindex" value="1" id="cv-noindex" @checked($cv->noindex)>
                                <label class="form-check-label" for="cv-noindex">Keep a public CV out of search engines too</label>
                            </div>
                        </div>

                        <div class="col-12"><hr class="my-1"></div>

                        <div class="col-md-6">
                            <label class="form-label ps-label" for="cv-preset">Profession preset</label>
                            <select class="form-select form-select-sm" name="preset" id="cv-preset">
                                @foreach ($presets as $id => $p)
                                    <option value="{{ $id }}" @selected($cv->preset === $id)>{{ $p['label'] }} · {{ $p['label_bn'] ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-md-4 pt-md-2">
                                <input class="form-check-input" type="checkbox" name="apply_preset" value="1" id="cv-apply-preset">
                                <label class="form-check-label" for="cv-apply-preset">Apply this preset when saving</label>
                            </div>
                            <div class="form-text">Sets the section order, which sections are on, their titles and objective/summary. Section content is never deleted; custom sections stay at the end.</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Sections & order --}}
            <section class="card ps-pane" data-ps-pane="layout" hidden>
                <div class="card-body">
                    <div class="ps-pane__head"><h5><i class="fas fa-sort me-2 text-primary"></i>Sections &amp; order</h5></div>
                    <p class="text-muted small">Turn sections on or off, rename them in English and Bangla, choose whether each shows on the web page and when printed, and move them up or down. A section with nothing in it is skipped automatically.</p>
                    <div class="d-none d-md-grid small text-muted fw-semibold px-2 mb-1" style="grid-template-columns: auto minmax(0,1fr) minmax(0,1fr) auto auto auto; gap: .75rem;">
                        <span>On</span><span>English title</span><span>Bangla title</span><span>Web</span><span>Print</span><span></span>
                    </div>
                    <div class="cv-layout" data-items>
                        @foreach ($layout as $i => $row)
                            @php
                                $key = $row['key'] ?? '';
                                $isCustom = str_starts_with($key, 'custom:');
                                $prefix = "layout[{$i}]";
                            @endphp
                            <div class="ps-item {{ !empty($row['enabled']) ? '' : 'is-off' }}" data-item>
                                <input type="hidden" name="{{ $prefix }}[key]" value="{{ $key }}">
                                <div class="form-check form-switch m-0">
                                    <input type="hidden" name="{{ $prefix }}[enabled]" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="{{ $prefix }}[enabled]" value="1" aria-label="Show {{ $row['title_en'] ?? $key }}"
                                           @checked(!empty($row['enabled'])) @disabled($key === 'personal') data-layout-toggle>
                                </div>
                                @if ($isCustom || $key === 'personal')
                                    <div class="cv-layout__title small">
                                        <strong>{{ $row['title_en'] ?? '' }}</strong> <span class="text-muted">{{ $row['title_bn'] ?? '' }}</span>
                                        <div class="text-muted" style="font-size: .72rem;">{{ $isCustom ? 'Custom section: rename it in Custom sections' : 'The header with name and contact details; always shown' }}</div>
                                    </div>
                                    <span class="d-none d-md-block"></span>
                                @else
                                    <input type="text" class="form-control form-control-sm cv-layout__title" name="{{ $prefix }}[title_en]" value="{{ $row['title_en'] ?? '' }}" aria-label="English title">
                                    <input type="text" class="form-control form-control-sm" name="{{ $prefix }}[title_bn]" value="{{ $row['title_bn'] ?? '' }}" aria-label="Bangla title">
                                @endif
                                <div class="form-check m-0" title="Show on the web page">
                                    <input type="hidden" name="{{ $prefix }}[web]" value="0">
                                    <input class="form-check-input" type="checkbox" name="{{ $prefix }}[web]" value="1" aria-label="Show on the web page" @checked($row['web'] ?? true)>
                                </div>
                                <div class="form-check m-0" title="Show when printed">
                                    <input type="hidden" name="{{ $prefix }}[print]" value="0">
                                    <input class="form-check-input" type="checkbox" name="{{ $prefix }}[print]" value="1" aria-label="Show when printed" @checked($row['print'] ?? true)>
                                </div>
                                <span class="ps-item__tools">
                                    <button type="button" class="btn btn-sm btn-light" data-move="-1" title="Move up" aria-label="Move up"><i class="fas fa-arrow-up"></i></button>
                                    <button type="button" class="btn btn-sm btn-light" data-move="1" title="Move down" aria-label="Move down"><i class="fas fa-arrow-down"></i></button>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Look --}}
            <section class="card ps-pane" data-ps-pane="theme" hidden>
                <div class="card-body">
                    <div class="ps-pane__head"><h5><i class="fas fa-paint-brush me-2 text-primary"></i>Look</h5></div>
                    <div class="row g-3">
                        @foreach (\ME\Portfolio\Support\Cv\Form::THEME_FIELDS as $key => $def)
                            @php [$label, $type] = $def; $options = $def[2] ?? []; $current = $theme[$key] ?? null; @endphp
                            <div class="col-md-6">
                                @if ($type === 'bool')
                                    <div class="form-check form-switch mt-md-2">
                                        <input type="hidden" name="theme[{{ $key }}]" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="theme[{{ $key }}]" value="1" id="theme-{{ $key }}" @checked($current)>
                                        <label class="form-check-label" for="theme-{{ $key }}">{{ $label }}</label>
                                    </div>
                                @else
                                    <label class="form-label ps-label" for="theme-{{ $key }}">{{ $label }}</label>
                                    @if ($type === 'color')
                                        <input type="color" class="form-control form-control-color" name="theme[{{ $key }}]" id="theme-{{ $key }}" value="{{ $current }}">
                                    @else
                                        <select class="form-select form-select-sm" name="theme[{{ $key }}]" id="theme-{{ $key }}">
                                            @foreach ($options as $v => $text)
                                                <option value="{{ $v }}" @selected((string) $current === (string) $v)>{{ $text }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Content sections --}}
            @foreach ($sections as $key => $def)
                <section class="card ps-pane" data-ps-pane="{{ $key }}" hidden>
                    <div class="card-body">
                        <div class="ps-pane__head">
                            <h5><i class="{{ $def['icon'] }} me-2 text-primary"></i>{{ $tabTitle($key) }}</h5>
                            @if ($key !== 'personal' && empty($layoutTitles[$key]['enabled']))
                                <span class="badge bg-secondary-subtle text-secondary-emphasis">Off: turn it on in Sections &amp; order</span>
                            @endif
                        </div>
                        @if (filled($def['hint'] ?? null))
                            <p class="text-muted small">{{ $def['hint'] }}</p>
                        @endif

                        @if ($key === 'personal')
                            <div class="cv-file mb-3">
                                @if (filled($photoPath))
                                    <img src="{{ url($photoPath) }}" alt="CV photo">
                                @else
                                    <span class="small text-muted">No CV photo: the portfolio's profile picture is used.</span>
                                @endif
                                <div>
                                    <label class="form-label ps-label" for="cv-photo">CV photo</label>
                                    <input type="file" class="form-control form-control-sm" name="photo" id="cv-photo" accept="image/jpeg,image/png,image/webp">
                                    @if (filled($photoPath))
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="cv-remove-photo">
                                            <label class="form-check-label small" for="cv-remove-photo">Remove this photo</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if ($def['type'] === 'list')
                            @include('portfolio::settings._repeater', [
                                'fields' => $def['fields'], 'prefix' => 'data[' . $key . ']',
                                'items' => array_values((array) ($values[$key] ?? [])), 'label' => $tabTitle($key), 'hint' => null,
                            ])
                        @else
                            <div class="row g-3">
                                @foreach ($def['fields'] as $field)
                                    @include('portfolio::settings._field', [
                                        'field' => $field, 'prefix' => 'data[' . $key . ']',
                                        'value' => data_get($values[$key] ?? [], $field['key']),
                                    ])
                                @endforeach
                            </div>
                        @endif

                        @if ($key === 'declaration')
                            <div class="cv-file mt-3">
                                @if (filled($signaturePath))
                                    <img src="{{ url($signaturePath) }}" alt="Signature" class="is-signature">
                                @endif
                                <div>
                                    <label class="form-label ps-label" for="cv-signature">Signature image</label>
                                    <input type="file" class="form-control form-control-sm" name="signature" id="cv-signature" accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text">A scan or photo of your signature, ideally on a white or transparent background.</div>
                                    @if (filled($signaturePath))
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_signature" value="1" id="cv-remove-signature">
                                            <label class="form-check-label small" for="cv-remove-signature">Remove the signature</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach

            {{-- Custom sections --}}
            <section class="card ps-pane" data-ps-pane="custom" hidden>
                <div class="card-body">
                    <div class="ps-pane__head"><h5><i class="fas fa-plus-square me-2 text-primary"></i>Custom sections</h5></div>
                    <p class="text-muted small">Anything the built-in sections don't cover, such as a driving licence or publications. New sections are added at the end; move them in Sections &amp; order after saving.</p>
                    @include('portfolio::settings._repeater', [
                        'fields' => \ME\Portfolio\Support\Cv\Form::customFields(), 'prefix' => 'custom',
                        'items' => $custom, 'label' => 'Custom sections', 'hint' => null,
                    ])
                </div>
            </section>

            <div class="ps-savebar">
                <span class="small text-muted">Saving updates /cv and /resume straight away.</span>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save CV</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('js')
<script>
    (() => {
        const form = document.querySelector('[data-ps-form]');
        if (!form) return;

        /* Tabs: one pane at a time; remembered in the URL hash and sent back after saving. */
        const buttons = form.querySelectorAll('[data-ps-open]');
        const panes = form.querySelectorAll('[data-ps-pane]');
        const open = (id) => {
            if (!form.querySelector(`[data-ps-pane="${id}"]`)) id = buttons[0].dataset.psOpen;
            buttons.forEach((b) => b.classList.toggle('is-active', b.dataset.psOpen === id));
            panes.forEach((p) => { p.hidden = p.dataset.psPane !== id; });
            form.querySelector('[data-ps-tab]').value = id;
            history.replaceState(null, '', '#' + id);
        };
        buttons.forEach((b) => b.addEventListener('click', () => open(b.dataset.psOpen)));
        open(location.hash.slice(1));

        /* Repeaters (add, remove) and the section order (move up/down). */
        let uid = Date.now();
        form.addEventListener('click', (e) => {
            const add = e.target.closest('[data-add]');
            if (add) {
                const rep = add.closest('[data-repeater]');
                const html = rep.querySelector(':scope > [data-template]').innerHTML.replaceAll('__I__', String(uid++));
                const holder = document.createElement('div');
                holder.innerHTML = html.trim();
                const item = holder.firstElementChild;
                rep.querySelector(':scope > [data-items]').appendChild(item);
                item.querySelector('input:not([type=hidden]), textarea, select')?.focus();
                return;
            }
            const item = e.target.closest('[data-item]');
            if (!item) return;
            if (e.target.closest('[data-remove]')) {
                if (confirm('Remove this item?')) item.remove();
            } else if (e.target.closest('[data-move]')) {
                const dir = Number(e.target.closest('[data-move]').dataset.move);
                const sibling = dir < 0 ? item.previousElementSibling : item.nextElementSibling;
                if (sibling) dir < 0 ? sibling.before(item) : sibling.after(item);
            }
        });

        form.addEventListener('change', (e) => {
            const toggle = e.target.closest('[data-layout-toggle]');
            if (toggle) toggle.closest('[data-item]').classList.toggle('is-off', !toggle.checked);
        });

        /* Item headers follow the first filled field. */
        form.addEventListener('input', (e) => {
            const item = e.target.closest('[data-item]');
            const title = item?.querySelector('[data-item-title]');
            if (!title) return;
            const first = [...item.querySelectorAll('[data-item-title-source]')].map((el) => el.value.trim()).find(Boolean);
            title.textContent = first || 'New item';
        });

        /* A password is needed for the Password option. */
        form.addEventListener('submit', (e) => {
            const vis = form.querySelector('#cv-visibility');
            const pwd = form.querySelector('#cv-password');
            if (vis.value === 'password' && !pwd.value && !pwd.placeholder.startsWith('A password is set')) {
                e.preventDefault();
                open('access');
                pwd.focus();
                alert('Type a password for the password-protected CV.');
            }
        });
    })();
</script>
@endpush
