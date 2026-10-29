<?php

namespace ME\Portfolio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use ME\Http\Controllers\Controller;
use ME\Portfolio\Models\Cv;
use ME\Portfolio\Support\Cv\Form;
use ME\Portfolio\Support\Cv\PortfolioImport;

/**
 * Admin → CV: edit the one CV row behind /cv and /resume — every section, the
 * section layout, custom sections, the look and who may see it.
 */
class CvAdminController extends Controller
{
    /** Uploaded CV photo and signature live here on the public disk (served as storage/cv/…). */
    protected const UPLOAD_DIR = 'cv';

    public function __construct()
    {
        $this->middleware('authorization:portfolio.cv');
    }

    public function edit()
    {
        $cv = Cv::current();

        return view('portfolio::cv-admin.edit', [
            'cv'       => $cv,
            'sections' => Form::sections(),
            'values'   => Form::values($cv),
            'custom'   => Form::customValues($cv),
            'layout'   => (array) $cv->sections,
            'theme'    => $cv->themeSettings(),
            'presets'  => config('portfolio_cv_presets', []),
        ]);
    }

    public function update(Request $request)
    {
        $presets = array_keys(config('portfolio_cv_presets', []));
        $request->validate([
            'data'             => ['nullable', 'array'],
            'custom'           => ['nullable', 'array'],
            'layout'           => ['nullable', 'array'],
            'theme'            => ['nullable', 'array'],
            'visibility'       => ['required', 'in:public,unlisted,password,private'],
            'password'         => ['nullable', 'string', 'min:4', 'max:255'],
            'noindex'          => ['nullable', 'boolean'],
            'preset'           => ['nullable', 'in:' . implode(',', $presets)],
            'apply_preset'     => ['nullable', 'boolean'],
            'photo'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_photo'     => ['nullable', 'boolean'],
            'signature'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_signature' => ['nullable', 'boolean'],
        ]);

        $cv = Cv::current();
        $before = $this->snapshot($cv);

        if ($request->input('visibility') === 'password' && !filled($request->input('password')) && !filled($cv->password)) {
            return back()->withInput()->with('error', 'Set a password for a password-protected CV.');
        }

        foreach (array_keys(Form::sections()) as $key) {
            $cv->{$key} = Form::normalize($key, $request->input("data.{$key}"), $cv->{$key});
        }

        // Files are not form fields: keep them unless replaced or removed.
        $personal = $cv->personal;
        $personal['photo'] = $this->file($request, 'photo', (string) ($cv->getOriginal('personal')['photo'] ?? ''));
        $cv->personal = $personal;
        $declaration = $cv->declaration;
        $declaration['signature'] = $this->file($request, 'signature', (string) ($cv->getOriginal('declaration')['signature'] ?? ''));
        $cv->declaration = $declaration;

        [$customSections, $customTitles] = Form::custom($request->input('custom'));
        $cv->custom_sections = $customSections;
        $cv->sections = Form::layout($request->input('layout'), $customTitles);
        $cv->theme = Form::theme($request->input('theme'));

        $cv->visibility = $request->input('visibility');
        $cv->noindex = $request->boolean('noindex');
        if (filled($request->input('password'))) {
            $cv->password = Hash::make($request->input('password'));
        }

        $cv->save();

        $preset = $request->input('preset');
        if ($preset && $request->boolean('apply_preset')) {
            $cv->applyPreset($preset);   // reorders sections and sets titles; data is untouched
        }

        $this->log('CV updated', $before, $cv);

        return redirect()->to(route(me_prefix() . '.cv.edit') . ($request->input('tab') ? '#' . $request->input('tab') : ''))
            ->with('success', $preset && $request->boolean('apply_preset') ? 'CV saved and the preset applied.' : 'CV saved.');
    }

    /**
     * Fill from the portfolio: personal details, career summary, employment, education and skills
     * are rebuilt from Portfolio Settings. Fields the portfolio doesn't hold are kept.
     */
    public function refill()
    {
        $cv = Cv::current();
        $before = $this->snapshot($cv);
        $fresh = PortfolioImport::data();

        $old = (array) $cv->personal;
        foreach (['name_bn', 'permanent_address', 'photo_show', 'whatsapp'] as $keep) {
            if (filled($old[$keep] ?? null) || is_bool($old[$keep] ?? null)) {
                $fresh['personal'][$keep] = $old[$keep];
            }
        }
        if (str_starts_with((string) ($old['photo'] ?? ''), 'storage/' . static::UPLOAD_DIR . '/')) {
            $fresh['personal']['photo'] = $old['photo'];   // keep a photo uploaded for the CV
        }
        $career = (array) $cv->career;
        $fresh['career']['objective'] = $career['objective'] ?? '';
        $fresh['career']['show'] = $career['show'] ?? $fresh['career']['show'];

        $cv->forceFill($fresh)->save();
        $this->log('CV filled from the portfolio', $before, $cv);

        return redirect()->route(me_prefix() . '.cv.edit')
            ->with('success', 'Personal details, career summary, employment, education and skills were filled from the portfolio.');
    }

    /* ------------------------------------------------------------------ */

    /** New upload → stored path; remove ticked → ''; otherwise the current path. */
    protected function file(Request $request, string $field, string $current): string
    {
        $upload = $request->file($field);
        if ($upload instanceof UploadedFile || $request->boolean("remove_{$field}")) {
            $this->deleteUpload($current);
        }
        if ($upload instanceof UploadedFile) {
            return 'storage/' . Storage::disk('public')->putFile(static::UPLOAD_DIR, $upload);
        }

        return $request->boolean("remove_{$field}") ? '' : $current;
    }

    protected function deleteUpload(string $path): void
    {
        $prefix = 'storage/' . static::UPLOAD_DIR . '/';
        if (str_starts_with($path, $prefix)) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        }
    }

    protected function snapshot(Cv $cv): array
    {
        return collect($cv->getAttributes())->except(['password', 'created_at', 'updated_at'])
            ->map(fn ($v) => is_string($v) ? $v : json_encode($v))->all();
    }

    protected function log(string $message, array $before, Cv $cv): void
    {
        if (function_exists('me_change_log')) {
            me_change_log($message, 'portfolio.cv')->record($before, $this->snapshot($cv->fresh()));
        }
    }
}
