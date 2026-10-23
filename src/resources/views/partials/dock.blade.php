@php
    $dockItems = [
        ['id' => 'home',     'label' => $labels['dock_home'],     'icon' => 'house'],
        ['id' => 'about',    'label' => $labels['dock_about'],    'icon' => 'person'],
        ['id' => 'skills',   'label' => $labels['dock_skills'],   'icon' => 'stack'],
        ['id' => 'jobs',     'label' => $labels['dock_jobs'],     'icon' => 'briefcase'],
        ['id' => 'projects', 'label' => $labels['dock_projects'], 'icon' => 'grid-1x2'],
        ['id' => 'contact',  'label' => $labels['dock_contact'],  'icon' => 'envelope'],
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
