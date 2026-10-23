<?php
namespace ME\Portfolio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use ME\Portfolio\Models\Message;
use ME\Portfolio\Models\Project;
use ME\Services\TelegramBotService;

class PortfolioController extends Controller
{

    protected $telegramService;

    public function __construct(TelegramBotService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    public function index(Request $request)
    {
        $cfg = config('portfolio', []);

        $data = [
            'seo'               => $cfg['seo'] ?? [],
            'profile'           => $cfg['profile'] ?? [],
            'about'             => $cfg['about'] ?? [],
            'education'         => $cfg['education'] ?? [],
            'skillCategories'   => $cfg['skill_categories'] ?? [],
            'experience'        => $cfg['experience'] ?? [],
            'projectCategories' => $cfg['project_categories'] ?? [],
            'projects'          => array_map(function ($project) {
                // Resolve public/-relative paths once, for both the cards and the modal JSON.
                $project['image'] = filled($project['image'] ?? null) ? url($project['image']) : '';
                return $project;
            }, $this->projects($cfg)),
            'contact'           => $cfg['contact'] ?? [],
            'contactForm'       => $cfg['contact_form'] ?? [],
            'canonical'         => $request->url(),
        ];

        return view('portfolio::index', $data);
    }

    /**
     * Projects managed in Admin → Projects (active ones, in their set order). Until at least one
     * is active, or before the table exists, the page keeps showing config('portfolio.projects').
     */
    protected function projects(array $cfg): array
    {
        try {
            $rows = Project::where('is_active', true)->orderBy('order')->orderByDesc('created_at')->get();
        } catch (\Throwable $e) {
            $rows = collect();
        }

        return $rows->isNotEmpty()
            ? $rows->map->toPortfolioArray()->all()
            : array_values($cfg['projects'] ?? []);
    }

    /**
     * Serves the package's bundled CSS/JS/images from public/portfolio/.
     * The route constraint limits the characters and extensions; realpath
     * keeps the request inside the assets directory.
     */
    public function asset(string $path)
    {
        $base = realpath(__DIR__ . '/../../public/portfolio');
        $file = realpath($base . '/' . $path);

        if (!$base || !$file || !str_starts_with($file, $base . DIRECTORY_SEPARATOR) || !is_file($file)) {
            abort(404);
        }

        $types = [
            'css'  => 'text/css; charset=UTF-8',
            'js'   => 'text/javascript; charset=UTF-8',
            'svg'  => 'image/svg+xml',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'ico'  => 'image/x-icon',
        ];
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Views append ?v=<mtime>, so a long cache is safe.
        return response()->file($file, [
            'Content-Type'           => $types[$ext] ?? 'application/octet-stream',
            'Cache-Control'          => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function storeMessage(Request $request)
    {
        // Honeypot: real visitors never see or fill this field.
        if (filled($request->input('website'))) {
            return response()->json(['message' => 'Message sent successfully!']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'device_info' => json_encode($request->device_info ?? $request->header('User-Agent')),
        ]);

        $telegramMessage = "📨 New Message Received\n\n"
            . "👤 Name: " . $message->name . "\n"
            . "📧 Email: " . $message->email . "\n"
            . "📝 Subject: " . $message->subject . "\n"
            . "💬 Message:\n" . $message->message;

        // The message is already stored, so a Telegram outage must not make
        // the visitor think sending failed.
        try {
            $this->telegramService->sendMessage($telegramMessage);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['message' => 'Message sent successfully!']);
    }

    public function sitemap(Request $request)
    {
        $excludedPrefixes = ['admin', 'api', 'login', 'logout', 'register', 'password', 'checkout', 'cart'];

        $urls = [];

        foreach (RouteFacade::getRoutes() as $route) {
            $methods = $route->methods();

            if (!in_array('GET', $methods, true)) {
                continue;
            }

            $uri = trim($route->uri(), '/');

            if ($uri === '' || $uri === '/') {
                $path = '';
            } else {
                $path = $uri;
            }

            if (Str::contains($path, ['{', '}'])) {
                continue;
            }

            $firstSegment = Str::before($path, '/');
            if ($firstSegment !== '' && in_array($firstSegment, $excludedPrefixes, true)) {
                continue;
            }

            $middleware = $route->gatherMiddleware();
            if (in_array('auth', $middleware, true)) {
                continue;
            }

            $loc = $path === '' ? url('/') : url('/' . $path);
            $urls[$loc] = [
                'loc' => $loc,
                'lastmod' => now()->toDateString(),
                'changefreq' => $path === '' ? 'daily' : 'weekly',
                'priority' => $path === '' ? '1.0' : '0.8',
            ];
        }

        if (!isset($urls[url('/')])) {
            $urls[url('/')] = [
                'loc' => url('/'),
                'lastmod' => now()->toDateString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ];
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $entry['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $entry['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $entry['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>";

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(Request $request)
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /logout',
            'Disallow: /register',
            'Disallow: /password',
            'Disallow: /checkout',
            'Disallow: /cart',
            'Disallow: /api/',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines) . "\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
