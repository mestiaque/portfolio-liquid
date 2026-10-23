{{-- One repeater item. Needs $fields, $prefix and $item. --}}
<div class="ps-item" data-item>
    <div class="ps-item__bar">
        <span class="ps-item__title" data-item-title>{{ collect($fields)->map(fn ($f) => data_get($item, $f['key']))->first(fn ($v) => is_string($v) && $v !== '') ?: 'New item' }}</span>
        <span class="ps-item__tools">
            <button type="button" class="btn btn-sm btn-light" data-move="-1" title="Move up" aria-label="Move up"><i class="fas fa-arrow-up"></i></button>
            <button type="button" class="btn btn-sm btn-light" data-move="1" title="Move down" aria-label="Move down"><i class="fas fa-arrow-down"></i></button>
            <button type="button" class="btn btn-sm btn-light text-danger" data-remove title="Remove" aria-label="Remove"><i class="fas fa-trash"></i></button>
        </span>
    </div>
    <div class="row g-2">
        @foreach ($fields as $field)
            @include('portfolio::settings._field', ['field' => $field, 'prefix' => $prefix, 'value' => data_get($item, $field['key'])])
        @endforeach
    </div>
</div>
