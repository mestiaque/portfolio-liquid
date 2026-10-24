<?php

namespace ME\Portfolio\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use ME\Models\Setting;
use ME\Portfolio\Support\Theme;
use ME\Services\RoleHierarchy;
use ME\Http\Middleware\AuthorizationMiddleware;


/**
 * Admin page for the public portfolio's look: colour preset, cursor trail
 * and the profile photo used in the hero.
 */
class AppearanceController extends Controller
{
    public function __construct(private RoleHierarchy $hierarchy)
    {
        $this->middleware('authorization:appearance.edit')->only(['edit', 'update']);
    }

    public function edit()
    {
        return view('portfolio::appearance.edit', [
            'presets'  => Theme::presets(),
            'groups'   => config('portfolio_themes.groups', []),
            'cursors'  => Theme::cursors(),
            'themeId'  => Theme::currentId(),
            'cursor'   => Theme::cursor(),
            'photoUrl' => Theme::photoUrl(),
            'portrait' => Theme::portraitSettings(),
            'fallbackPhoto' => filled(config('portfolio.profile.photo')) ? url(config('portfolio.profile.photo')) : null,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme'        => ['required', Rule::in(array_keys(Theme::presets()))],
            'cursor'       => ['required', Rule::in(array_keys(Theme::cursors()))],
            'photo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photo' => ['nullable', 'boolean'],
            'portrait'            => ['nullable', 'array'],
            'portrait.color_mode' => ['nullable', Rule::in(['preset', 'accent', 'custom'])],
            'portrait.color'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'portrait.disc_mode'  => ['nullable', Rule::in(['preset', 'custom'])],
            'portrait.disc'       => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'portrait.zoom'       => ['nullable', 'numeric', 'between:1,3'],
            'portrait.x'          => ['nullable', 'numeric', 'between:-1,1'],
            'portrait.y'          => ['nullable', 'numeric', 'between:-1,1'],
            'portrait.brightness' => ['nullable', 'numeric', 'between:-0.5,0.5'],
            'portrait.contrast'   => ['nullable', 'numeric', 'between:0.4,2.5'],
            'portrait.grid'       => ['nullable', 'integer', 'between:24,80'],
            'portrait.dot'        => ['nullable', 'numeric', 'between:0.5,1.6'],
            'portrait.bg'         => ['nullable', Rule::in(['auto', 'keep'])],
            'portrait.invert'     => ['nullable', 'boolean'],
        ]);

        $keys = [Theme::THEME_KEY, Theme::CURSOR_KEY, Theme::PHOTO_KEY, Theme::PORTRAIT_KEY];
        $before = Setting::snapshot($keys);

        Setting::set(Theme::THEME_KEY, $validated['theme']);
        Setting::set(Theme::CURSOR_KEY, $validated['cursor']);

        // Portrait studio: keep only known keys, cast to the types the halftone script expects.
        $p = array_merge(Theme::PORTRAIT_DEFAULTS, array_filter($validated['portrait'] ?? [], fn ($v) => $v !== null));
        foreach (['zoom', 'x', 'y', 'brightness', 'contrast', 'dot'] as $k) {
            $p[$k] = round((float) $p[$k], 2);
        }
        $p['grid'] = (int) $p['grid'];
        $p['invert'] = $request->boolean('portrait.invert');
        Setting::set(Theme::PORTRAIT_KEY, array_intersect_key($p, Theme::PORTRAIT_DEFAULTS));

        if ($request->hasFile('photo')) {
            Setting::setImage(Theme::PHOTO_KEY, $request->file('photo'));
        } elseif ($request->boolean('remove_photo')) {
            Setting::removeImage(Theme::PHOTO_KEY);
        }

        if (function_exists('me_change_log')) {
            me_change_log('Portfolio appearance updated', 'portfolio.appearance')->record($before, Setting::snapshot($keys));
        }

        return redirect()->route(me_prefix() . '.appearance.edit')->with('success', 'Appearance updated.');
    }
}
