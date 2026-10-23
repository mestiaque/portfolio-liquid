{{-- A list of items. Needs $fields, $prefix, $items, $label and optional $hint. New items come from the <template>. --}}
<div class="ps-repeater" data-repeater>
    <div class="ps-repeater__head">
        <span class="ps-label">{{ $label }}</span>
        @if (filled($hint ?? null)) <small class="text-muted">{{ $hint }}</small> @endif
    </div>
    <div class="ps-repeater__items" data-items>
        @foreach ($items as $i => $item)
            @include('portfolio::settings._item', ['fields' => $fields, 'prefix' => $prefix . '[' . $i . ']', 'item' => is_array($item) ? $item : []])
        @endforeach
    </div>
    <template data-template>
        @include('portfolio::settings._item', ['fields' => $fields, 'prefix' => $prefix . '[__I__]', 'item' => []])
    </template>
    <button type="button" class="btn btn-sm btn-outline-primary" data-add><i class="fas fa-plus me-1"></i> Add</button>
</div>
