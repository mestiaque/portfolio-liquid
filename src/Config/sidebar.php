<?php

return [
    [
        'title'      => 'Projects',
        'icon'       => 'fas fa-briefcase',
        'route'      => 'admin.projects.index',
        'for_active' => 'admin.projects',
        'icon_color' => 'icc-66',
        'permit'     => 'portfolio.projects',
        'sl'         => 100,
    ],
    [
        'title'      => 'Appearance',
        'icon'       => 'fas fa-palette',
        'route'      => 'admin.appearance.edit',
        'for_active' => 'admin.appearance',
        'icon_color' => 'icc-38',
        'permit'     => 'portfolio.appearance',
        'sl'         => 101,
    ],

];
