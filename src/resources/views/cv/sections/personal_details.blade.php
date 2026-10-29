{{-- [{label, value, wide}]; every row shows (blank when there is no value), two per line, addresses full width --}}
<dl class="cv-details">
    @foreach ($data as $row)
        <div class="cv-details__row {{ $row['wide'] ? 'cv-details__row--wide' : '' }}">
            <dt>{{ $row['label'] }}</dt>
            <dd>{{ $row['value'] }}</dd>
        </div>
    @endforeach
</dl>
