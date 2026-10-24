<?php

return [
    [
        'title'      => 'Projects',
        'icon'       => 'fas fa-briefcase',
        'route'      => me_prefix().'.projects.index',
        'for_active' => me_prefix().'.projects',
        'icon_color' => 'icc-66',
        'permit'     => 'project.view',
        'sl'         => 100,
    ],
    [
        'title'      => 'Appearance',
        'icon'       => 'fas fa-palette',
        'route'      => me_prefix().'.appearance.edit',
        'for_active' => me_prefix().'.appearance',
        'icon_color' => 'icc-38',
        'permit'     => 'appearance.edit',
        'sl'         => 101,
    ],
    [
        'title'      => 'Portfolio Settings',
        'icon'       => 'fas fa-sliders-h',
        'route'      => me_prefix().'.portfolio-settings.edit',
        'for_active' => me_prefix().'.portfolio-settings',
        'icon_color' => 'icc-67',
        'permit'     => 'portfolio.settings',
        'sl'         => 102,
    ],

];
