<?php

namespace ME\Portfolio\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ME\Models\Setting;
use ME\Portfolio\Models\Project;

/**
 * One JSON file that carries everything set in the admin panel, so a local
 * site and the server can be kept in step:
 *
 *   php artisan portfolio:export            → portfolio-sync.json
 *   php artisan portfolio:import            ← portfolio-sync.json
 *
 * (or the Download / Import buttons on Admin → Portfolio Settings).
 *
 * Parts: "settings" (Portfolio Settings sections + Appearance), "projects"
 * (rows and screenshots) and "photo" (the profile picture). Importing makes
 * the target match the file: the projects in the file replace the existing ones.
 * Contact methods (email, phone, WhatsApp, …) are left out unless asked for, and
 * a file without them keeps the target's own.
 */
class Sync
{
    public const FORMAT = 'portfolio-sync';
    public const VERSION = 1;
    public const PARTS = ['settings', 'projects', 'photo'];

    /**
     * Default file: portfolio-sync.json in the package root, so it travels with the package's git
     * repository (locally the package is symlinked, on the server it arrives with composer).
     * A copy in the app root wins on import, for one-off files dropped there by hand.
     */
    public static function defaultPath(bool $forImport = false): string
    {
        $appFile = base_path('portfolio-sync.json');
        if ($forImport && is_file($appFile)) {
            return $appFile;
        }

        return dirname(__DIR__, 2) . '/portfolio-sync.json';
    }

    /** Setting keys that belong to the portfolio. */
    public static function settingKeys(): array
    {
        $content = array_map(fn ($s) => Content::KEY_PREFIX . $s, array_keys(Content::schema()));

        return array_merge($content, [Theme::THEME_KEY, Theme::CURSOR_KEY, Theme::PORTRAIT_KEY]);
    }

    /** Contact section key; its 'methods' (email, phone, WhatsApp, …) stay out of the file unless asked. */
    public const CONTACT_KEY = Content::KEY_PREFIX . 'contact';

    public static function export(bool $withImages = true, bool $withContact = false): array
    {
        $settings = [];
        foreach (Setting::whereIn('key', static::settingKeys())->get(['key', 'value']) as $row) {
            $decoded = json_decode((string) $row->value, true);
            // JSON settings are stored decoded, so the file stays readable and diff-friendly.
            $settings[$row->key] = is_array($decoded) ? $decoded : $row->value;
        }
        // The file is meant to live in git: personal contact details are left out by default.
        if (!$withContact && isset($settings[static::CONTACT_KEY]) && is_array($settings[static::CONTACT_KEY])) {
            unset($settings[static::CONTACT_KEY]['methods']);
        }

        $projects = Project::orderBy('order')->orderBy('id')->get()->map(function (Project $p) use ($withImages) {
            $row = collect($p->getAttributes())->except(['id', 'created_at', 'updated_at'])->all();
            foreach (['technologies', 'categories', 'features'] as $json) {
                $row[$json] = $p->{$json}; // decoded arrays (or null), not JSON strings
            }
            $row['is_active'] = (bool) $p->is_active;
            $row['image_file'] = $withImages ? static::encodeFile('public', $p->image) : null;

            return $row;
        })->all();

        $photo = null;
        if ($withImages && ($media = Setting::image(Theme::PHOTO_KEY)) && $media->exists()) {
            $photo = static::encodeFile($media->disk, $media->path, $media->original_name);
        }

        return [
            'format'      => static::FORMAT,
            'version'     => static::VERSION,
            'exported_at' => now()->toIso8601String(),
            'source'      => config('app.url'),
            'settings'    => $settings,
            'projects'    => $projects,
            'photo'       => $photo,
        ];
    }

    /**
     * Apply a sync file. $parts limits what is imported (default: everything in the file).
     * Returns a short summary for the caller to show.
     */
    public static function import(array $data, ?array $parts = null): array
    {
        if (($data['format'] ?? null) !== static::FORMAT) {
            throw new \InvalidArgumentException('This is not a portfolio sync file.');
        }
        $parts = $parts ?: static::PARTS;
        $summary = [];

        if (in_array('settings', $parts, true) && is_array($data['settings'] ?? null)) {
            $allowed = static::settingKeys();
            $count = 0;
            foreach ($data['settings'] as $key => $value) {
                if (!in_array($key, $allowed, true)) {
                    continue;
                }
                // A file without contact methods keeps the ones already on this site.
                if ($key === static::CONTACT_KEY && is_array($value) && !array_key_exists('methods', $value)) {
                    $current = json_decode((string) Setting::get($key), true);
                    $methods = $current['methods'] ?? config('portfolio.contact.methods');
                    if ($methods !== null) {
                        $value['methods'] = $methods;
                    }
                }
                Setting::set($key, $value); // arrays are JSON-encoded by Setting::set
                $count++;
            }
            // A Portfolio Settings section missing from the file was never saved (or was reset) at the
            // source, so it goes back to config/portfolio.php here too.
            Setting::where('key', 'like', Content::KEY_PREFIX . '%')
                ->whereNotIn('key', array_keys($data['settings']))->delete();
            $summary[] = "{$count} settings";
        }

        if (in_array('projects', $parts, true) && is_array($data['projects'] ?? null)) {
            $fillable = (new Project())->getFillable();
            DB::transaction(function () use ($data, $fillable) {
                foreach (Project::all() as $old) {
                    if ($old->image && Storage::disk('public')->exists($old->image)) {
                        Storage::disk('public')->delete($old->image);
                    }
                    $old->delete();
                }
                foreach ($data['projects'] as $row) {
                    $attrs = array_intersect_key((array) $row, array_flip($fillable));
                    $attrs['image'] = static::decodeFile('public', 'projects', $row['image_file'] ?? null);
                    Project::create($attrs);
                }
            });
            $summary[] = count($data['projects']) . ' projects';
        }

        if (in_array('photo', $parts, true) && array_key_exists('photo', $data)) {
            if (is_array($data['photo'])) {
                $base = tempnam(sys_get_temp_dir(), 'pf');
                $tmp = $base . '.' . static::extension($data['photo']['name'] ?? 'photo.png');
                file_put_contents($tmp, base64_decode($data['photo']['data']));
                try {
                    Setting::setImage(Theme::PHOTO_KEY, $tmp);
                } finally {
                    @unlink($tmp);
                    @unlink($base);
                }
                $summary[] = 'profile picture';
            }
        }

        return $summary;
    }

    /* ------------------------------------------------------------------ */

    protected static function encodeFile(string $disk, ?string $path, ?string $name = null): ?array
    {
        if (!$path || !Storage::disk($disk)->exists($path)) {
            return null;
        }

        return [
            'name' => $name ?: basename($path),
            'data' => base64_encode(Storage::disk($disk)->get($path)),
        ];
    }

    /** Write an embedded file to a disk; returns its path there (null when there is none). */
    protected static function decodeFile(string $disk, string $dir, ?array $file): ?string
    {
        if (!$file || empty($file['data'])) {
            return null;
        }
        $path = $dir . '/' . Str::uuid() . '.' . static::extension($file['name'] ?? '');
        Storage::disk($disk)->put($path, base64_decode($file['data']));

        return $path;
    }

    protected static function extension(string $name): string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $ext : 'png';
    }
}
