@php
    $dockItems = [
        ['id' => 'home',     'label' => 'Home',     'icon' => 'house'],
        ['id' => 'about',    'label' => 'About',    'icon' => 'person'],
        ['id' => 'skills',   'label' => 'Skills',   'icon' => 'stack'],
        ['id' => 'jobs',     'label' => 'Jobs',     'icon' => 'briefcase'],
        ['id' => 'projects', 'label' => 'Projects', 'icon' => 'grid-1x2'],
        ['id' => 'contact',  'label' => 'Contact',  'icon' => 'envelope'],
    ];
@endphp
<nav class="dock" aria-label="Sections">
    {{-- Floating glass bubble behind the active icon; main.js slides it into place. --}}
    <span class="dock__bubble" aria-hidden="true"></span>
    <ul class="dock__list">
        @foreach ($dockItems as $item)
            <li class="dock__item">
                <a class="dock__link {{ $loop->first ? 'is-active' : '' }}" href="#{{ $item['id'] }}"
                   data-dock="{{ $item['id'] }}" @if ($loop->first) aria-current="location" @endif>
                    <i class="bi bi-{{ $item['icon'] }} dock__icon" aria-hidden="true"></i>
                    <span class="dock__label">{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>
