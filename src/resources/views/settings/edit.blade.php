@extends('me::master')
@section('title', 'Portfolio Settings')

@push('buttons')
<a href="{{ route('index') }}" target="_blank" rel="noopener" class="btn btn-sm btn-encodex-list">
    <i class="fas fa-external-link-alt"></i> View site
</a>
@endpush

@push('css')
<style>
    .ps-wrap { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 1rem; align-items: start; }
    @media (max-width: 991.98px) { .ps-wrap { grid-template-columns: 1fr; } }
    .ps-nav { position: sticky; top: 1rem; display: grid; gap: .25rem; padding: .5rem; }
    .ps-nav button { display: flex; align-items: center; gap: .6rem; width: 100%; padding: .55rem .75rem; border: 0; border-radius: 10px; background: none; color: #334155; font-size: .9rem; text-align: left; }
    .ps-nav button i { width: 18px; color: #64748b; text-align: center; }
    .ps-nav button:hover { background: #f1f5f9; }
    .ps-nav button.is-active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
    .ps-nav button.is-active i { color: #1d4ed8; }
    .ps-nav .ps-dot { width: 7px; height: 7px; margin-left: auto; border-radius: 50%; background: #22c55e; }
    .ps-pane[hidden] { display: none !important; }
    .ps-pane__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: 1rem; }
    .ps-pane__head h5 { margin: 0; font-weight: 700; }
    .ps-source { font-size: .75rem; padding: .2rem .55rem; border-radius: 999px; }
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
</style>
@endpush

@section('content')
{{-- Sync file: move everything set in the admin panel between this site and another (local ↔ server). --}}
<div class="card mb-3">
    <div class="card-body d-flex flex-wrap align-items-center gap-3">
        <div class="me-auto">
            <h6 class="mb-1 fw-bold"><i class="fas fa-sync-alt text-primary me-1"></i> Sync with another site</h6>
            <p class="small text-muted mb-0">
                One file with these settings, Appearance, projects and pictures. Download it here, import it on the server
                (or use <code>php artisan portfolio:export</code> / <code>portfolio:import</code>).
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.portfolio-settings.export') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-download me-1"></i> Download sync file</a>
            <a href="{{ route('admin.portfolio-settings.export', ['no_images' => 1]) }}" class="btn btn-sm btn-outline-secondary" title="Smaller file without the pictures">Without pictures</a>
        </div>
        <form action="{{ route('admin.portfolio-settings.import') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center gap-2"
              onsubmit="return confirm('Import this file? Settings are overwritten and the projects in the file replace the current projects.');">
            @csrf
            <input type="file" name="sync_file" accept="application/json,.json" class="form-control form-control-sm" style="max-width: 240px;" required>
            @foreach (\ME\Portfolio\Support\Sync::PARTS as $part)
                <div class="form-check form-check-inline m-0">
                    <input class="form-check-input" type="checkbox" name="parts[]" value="{{ $part }}" id="sync-{{ $part }}" checked>
                    <label class="form-check-label small" for="sync-{{ $part }}">{{ ucfirst($part) }}</label>
                </div>
            @endforeach
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i> Import</button>
        </form>
    </div>
    @error('sync_file') <div class="alert alert-danger m-3 mt-0 py-2">{{ $message }}</div> @enderror
</div>

<form action="{{ route('admin.portfolio-settings.update') }}" method="POST" data-ps-form>
    @csrf
    @method('PUT')
    <input type="hidden" name="tab" value="" data-ps-tab>

    <div class="ps-wrap">
        <nav class="card ps-nav" aria-label="Settings sections">
            @foreach ($schema as $section => $def)
                <button type="button" data-ps-open="{{ $section }}">
                    <i class="{{ $def['icon'] }}" aria-hidden="true"></i> {{ $def['title'] }}
                    @if (in_array($section, $saved, true)) <span class="ps-dot" title="Saved in the database"></span> @endif
                </button>
            @endforeach
        </nav>

        <div>
            @foreach ($schema as $section => $def)
                <section class="card ps-pane" id="ps-{{ $section }}" data-ps-pane="{{ $section }}" hidden>
                    <div class="card-body">
                        <div class="ps-pane__head">
                            <h5><i class="{{ $def['icon'] }} me-2 text-primary"></i>{{ $def['title'] }}</h5>
                            @if (in_array($section, $saved, true))
                                <span class="ps-source bg-success-subtle text-success-emphasis">Saved in the database</span>
                            @else
                                <span class="ps-source bg-secondary-subtle text-secondary-emphasis">From config/portfolio.php</span>
                            @endif
                        </div>
                        @if (filled($def['hint'] ?? null))
                            <p class="text-muted small">{{ $def['hint'] }}</p>
                        @endif

                        @if ($def['type'] === 'list')
                            @include('portfolio::settings._repeater', [
                                'fields' => $def['fields'], 'prefix' => 'content[' . $section . ']',
                                'items' => array_values((array) ($values[$section] ?? [])), 'label' => $def['title'], 'hint' => null,
                            ])
                        @else
                            <div class="row g-3">
                                @foreach ($def['fields'] as $field)
                                    @include('portfolio::settings._field', [
                                        'field' => $field, 'prefix' => 'content[' . $section . ']',
                                        'value' => data_get($values[$section] ?? [], $field['key']),
                                    ])
                                @endforeach
                            </div>
                        @endif

                        @if (in_array($section, $saved, true))
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="reset[{{ $section }}]" value="1" id="reset-{{ $section }}">
                                <label class="form-check-label small text-danger" for="reset-{{ $section }}">
                                    Reset this section to config/portfolio.php when saving
                                </label>
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach

            <div class="ps-savebar">
                <span class="small text-muted">Saving stores every section in the settings table; the website uses it straight away.</span>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save settings</button>
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

        /* Tabs: show one section at a time; remember it in the URL hash. */
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

        /* Repeaters: add from the template, remove, move up/down. */
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
                item.querySelector('input, textarea, select')?.focus();
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

        /* Item headers follow the first filled field, so collapsed lists stay readable. */
        form.addEventListener('input', (e) => {
            const item = e.target.closest('[data-item]');
            if (!item) return;
            const first = [...item.querySelectorAll('[data-item-title-source]')].map((el) => el.value.trim()).find(Boolean);
            item.querySelector('[data-item-title]').textContent = first || 'New item';
        });
    })();
</script>
@endpush
