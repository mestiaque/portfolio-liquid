{{-- One schema field. Needs $field, $prefix (input name prefix) and $value (stored value). --}}
@php
    $name = \ME\Portfolio\Support\Content::inputName($prefix, $field['key']);
    $id = 'f' . substr(md5($name), 0, 10);
    $type = $field['type'];
    $shown = \ME\Portfolio\Support\Content::display($field, $value);
    $wide = in_array($type, ['textarea', 'lines', 'paragraphs', 'pairs', 'repeater'], true);
@endphp
@if ($type === 'repeater')
    <div class="col-12">
        @include('portfolio::settings._repeater', [
            'fields' => $field['fields'], 'prefix' => $name, 'items' => is_array($value) ? array_values($value) : [],
            'label' => $field['label'], 'hint' => $field['hint'] ?? null,
        ])
    </div>
@else
    <div class="{{ $wide ? 'col-12' : 'col-md-6' }}">
        @if ($type === 'bool')
            <div class="form-check form-switch mt-md-4 pt-md-2">
                <input type="hidden" name="{{ $name }}" value="0">
                <input class="form-check-input" type="checkbox" role="switch" name="{{ $name }}" value="1" id="{{ $id }}" @checked($shown)>
                <label class="form-check-label" for="{{ $id }}">{{ $field['label'] }}</label>
            </div>
        @else
            <label class="form-label ps-label" for="{{ $id }}">
                {{ $field['label'] }}
                @if (in_array($type, ['lines', 'csv', 'paragraphs', 'pairs'], true))
                    <small class="text-muted fw-normal">({{ ['lines' => 'one per line', 'csv' => 'comma separated', 'paragraphs' => 'blank line between paragraphs', 'pairs' => 'Name | icon per line'][$type] }})</small>
                @endif
            </label>
            @if ($type === 'select')
                <select class="form-select form-select-sm" name="{{ $name }}" id="{{ $id }}">
                    @foreach ($field['options'] as $optValue => $optLabel)
                        <option value="{{ $optValue }}" @selected((string) $shown === (string) $optValue)>{{ $optLabel }}</option>
                    @endforeach
                </select>
            @elseif ($wide)
                <textarea class="form-control form-control-sm" name="{{ $name }}" id="{{ $id }}" rows="{{ $field['rows'] ?? ($type === 'textarea' ? 3 : 4) }}"
                          placeholder="{{ $field['placeholder'] ?? '' }}" data-item-title-source>{{ $shown }}</textarea>
            @else
                <input type="{{ $type === 'number' ? 'number' : 'text' }}" class="form-control form-control-sm" name="{{ $name }}" id="{{ $id }}"
                       value="{{ $shown }}" placeholder="{{ $field['placeholder'] ?? '' }}" @if ($type === 'number') step="any" @endif data-item-title-source>
            @endif
            @if (filled($field['hint'] ?? null))
                <div class="form-text">{{ $field['hint'] }}</div>
            @endif
        @endif
    </div>
@endif
