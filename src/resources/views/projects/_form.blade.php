{{-- Shared create/edit form. Expects $project (a new or saved Project) and $categories (id => label). --}}
@php
    $listText = fn ($items, string $glue) => is_array($items) ? implode($glue, $items) : '';
    $pickedCategories = old('categories', $project->categories ?? []);
@endphp
<div class="row">
    <div class="col-lg-8">
        <div class="form-group mb-3">
            <label for="title">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $project->title) }}" required maxlength="255">
            @error('title') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="form-group mb-3">
            <label for="summary">Short summary <small class="text-muted">(shown on the card)</small></label>
            <input type="text" class="form-control form-control-sm @error('summary') is-invalid @enderror" id="summary" name="summary" value="{{ old('summary', $project->summary) }}" maxlength="500"
                   placeholder="One sentence, e.g. Inventory, purchasing and reporting modules for a growing business.">
            @error('summary') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="form-group mb-3">
            <label for="description">Description <small class="text-muted">(shown in the details popup)</small></label>
            <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $project->description) }}</textarea>
            @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="problem">Problem</label>
                <textarea class="form-control form-control-sm @error('problem') is-invalid @enderror" id="problem" name="problem" rows="3" placeholder="What the client needed">{{ old('problem', $project->problem) }}</textarea>
                @error('problem') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 form-group mb-3">
                <label for="solution">Solution</label>
                <textarea class="form-control form-control-sm @error('solution') is-invalid @enderror" id="solution" name="solution" rows="3" placeholder="What you built">{{ old('solution', $project->solution) }}</textarea>
                @error('solution') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="features">Key features <small class="text-muted">(one per line)</small></label>
            <textarea class="form-control form-control-sm @error('features') is-invalid @enderror" id="features" name="features" rows="4" placeholder="Role-based access&#10;Stock alerts&#10;PDF invoices">{{ old('features', $listText($project->features, "\n")) }}</textarea>
            @error('features') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="form-group mb-3">
            <label for="challenges">Challenges</label>
            <textarea class="form-control form-control-sm @error('challenges') is-invalid @enderror" id="challenges" name="challenges" rows="2">{{ old('challenges', $project->challenges) }}</textarea>
            @error('challenges') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="row">
            <div class="col-md-6 form-group mb-3">
                <label for="url">Live demo URL</label>
                <input type="url" class="form-control form-control-sm @error('url') is-invalid @enderror" id="url" name="url" value="{{ old('url', $project->url) }}" placeholder="https://example.com">
                @error('url') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6 form-group mb-3">
                <label for="github_url">Source code URL</label>
                <input type="url" class="form-control form-control-sm @error('github_url') is-invalid @enderror" id="github_url" name="github_url" value="{{ old('github_url', $project->github_url) }}" placeholder="https://github.com/username/repo">
                @error('github_url') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="technologies">Technologies <small class="text-muted">(comma separated)</small></label>
            <input type="text" class="form-control form-control-sm @error('technologies') is-invalid @enderror" id="technologies" name="technologies" value="{{ old('technologies', $listText($project->technologies, ', ')) }}" placeholder="Laravel, Vue.js, MySQL">
            @error('technologies') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group mb-3">
            <label for="image">Screenshot</label>
            @if ($project->image)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $project->image) }}" alt="{{ $project->title }}" class="img-thumbnail" style="max-width: 100%;">
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_image">
                    <label class="form-check-label small" for="remove_image">Remove screenshot (show the icon instead)</label>
                </div>
            @endif
            <input type="file" class="form-control form-control-sm @error('image') is-invalid @enderror" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
            @error('image') <span class="invalid-feedback">{{ $message }}</span> @enderror
            <small class="form-text text-muted">16:9 works best. Max 4MB: jpg, png, webp, gif.</small>
        </div>

        <div class="form-group mb-3">
            <label for="icon">Icon <small class="text-muted">(when there is no screenshot)</small></label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-{{ old('icon', $project->icon) ?: 'window' }}" data-icon-preview></i></span>
                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', $project->icon) }}" placeholder="window" data-icon-input>
                @error('icon') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <small class="form-text text-muted">A <a href="https://icons.getbootstrap.com" target="_blank" rel="noopener">Bootstrap Icons</a> name, e.g. cart3, building-gear, box-seam.</small>
        </div>

        <div class="form-group mb-3">
            <label class="d-block">Categories <small class="text-muted">(filter buttons)</small></label>
            @forelse ($categories as $id => $label)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" value="{{ $id }}" id="cat-{{ $id }}" @checked(in_array($id, $pickedCategories, true))>
                    <label class="form-check-label" for="cat-{{ $id }}">{{ $label }}</label>
                </div>
            @empty
                <p class="small text-muted mb-0">No categories in config/portfolio.php (project_categories).</p>
            @endforelse
            @error('categories.*') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="form-group mb-3">
            <label for="order">Order <small class="text-muted">(lower shows first)</small></label>
            <input type="number" class="form-control form-control-sm @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order', $project->order ?? 0) }}" min="0">
            @error('order') <span class="invalid-feedback">{{ $message }}</span> @enderror
        </div>

        <div class="form-check form-switch mb-3">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" @checked(old('is_active', $project->is_active ?? true))>
            <label class="form-check-label" for="is_active">Show on the website</label>
        </div>
    </div>
</div>

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush

@push('js')
<script>
    // Live icon preview next to the icon name.
    document.querySelector('[data-icon-input]')?.addEventListener('input', (e) => {
        const name = e.target.value.trim().replace(/[^a-z0-9-]/g, '') || 'window';
        document.querySelector('[data-icon-preview]').className = 'bi bi-' + name;
    });
</script>
@endpush
