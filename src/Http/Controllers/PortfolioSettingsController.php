<?php

namespace ME\Portfolio\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use ME\Models\Setting;
use ME\Portfolio\Support\Content;
use ME\Portfolio\Support\Sync;
use ME\Services\RoleHierarchy;
use ME\Http\Middleware\AuthorizationMiddleware;


/**
 * Admin → Portfolio Settings: every text on the public page, saved per
 * section in the settings table (see Support\Content).
 */
class PortfolioSettingsController extends Controller
{

    public function __construct(private RoleHierarchy $hierarchy)
    {
        $this->middleware('authorization:portfolio.settings')->only(['edit', 'update']);
        $this->middleware('authorization:portfolio.exim')->only(['export', 'import']);
    }

    public function edit()
    {
        // Content::apply() already ran for this request, so config() holds the live values.
        return view('portfolio::settings.edit', [
            'schema' => Content::schema(),
            'values' => collect(Content::schema())->mapWithKeys(fn ($def, $section) => [$section => config("portfolio.{$section}", [])])->all(),
            'saved'  => array_keys(Content::saved()),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'content'   => ['nullable', 'array'],
            'reset'     => ['nullable', 'array'],
            'reset.*'   => ['in:1'],
        ]);

        $sections = array_keys(Content::schema());
        $keys = array_map(fn ($s) => Content::KEY_PREFIX . $s, $sections);
        $before = Setting::snapshot($keys);
        $reset = array_keys(array_filter((array) $request->input('reset', [])));

        foreach ($sections as $section) {
            $key = Content::KEY_PREFIX . $section;
            if (in_array($section, $reset, true)) {
                Setting::where('key', $key)->delete(); // back to config/portfolio.php
                continue;
            }
            if ($request->has("content.{$section}") || Content::schema()[$section]['type'] === 'list') {
                Setting::set($key, Content::normalize($section, $request->input("content.{$section}", [])));
            }
        }

        if (function_exists('me_change_log')) {
            me_change_log('Portfolio settings updated', 'portfolio.settings')->record($before, Setting::snapshot($keys));
        }

        return redirect()->to(route(me_prefix() . '.portfolio-settings.edit') . ($request->input('tab') ? '#' . $request->input('tab') : ''))
            ->with('success', 'Portfolio settings saved.');
    }

    /** Download the sync file (same content as `php artisan portfolio:export`). */
    public function export(Request $request)
    {
        $json = json_encode(Sync::export(!$request->boolean('no_images'), $request->boolean('with_contact')), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return response($json . "\n", 200, [
            'Content-Type'        => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="portfolio-sync-' . now()->format('Y-m-d') . '.json"',
        ]);
    }

    /** Apply an uploaded sync file. */
    public function import(Request $request)
    {
        $request->validate([
            'sync_file' => ['required', 'file', 'max:51200'],
            'parts'     => ['nullable', 'array'],
            'parts.*'   => ['in:' . implode(',', Sync::PARTS)],
        ]);

        $data = json_decode((string) file_get_contents($request->file('sync_file')->getRealPath()), true);
        if (!is_array($data)) {
            return back()->with('error', 'That file is not valid JSON.');
        }

        try {
            $summary = Sync::import($data, $request->input('parts') ?: null);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (function_exists('me_change_log')) {
            me_change_log('Portfolio sync file imported', 'portfolio.settings')->record([], ['imported' => implode(', ', $summary)]);
        }

        return redirect()->route(me_prefix() . '.portfolio-settings.edit')->with('success', 'Imported: ' . (implode(', ', $summary) ?: 'nothing'));
    }
}
