# Portfolio Package

A Laravel package that renders a single-page developer portfolio: a liquid-glass UI with a macOS/Ubuntu-style floating dock, built with Bootstrap 5.3, Bootstrap Icons, vanilla JS and anime.js. No build step.

Everything on the page can be managed from the admin panel (built on [metheme](https://github.com/mestiaque/metheme)): every text, 24 colour presets, 27 cursor trails, the halftone portrait, and the projects. A single sync file moves it all from your machine to the server.

## What to do

### First time on a server

1. **Web server.** Point the domain at the host app's `public/` folder. `deploy/mestiaque.es.conf` is a working nginx example.
2. **Install.** In the host app: `composer require mestiaque/portfolio mestiaque/metheme`.
3. **`.env`** in the host app:
   ```dotenv
   APP_URL=https://mestiaque.com
   PORTFOLIO_CONTACT_MODE=production
   TELEGRAM_BOT_TOKEN=...        # contact-form messages are sent to Telegram
   TELEGRAM_CHAT_ID=...
   ```
4. **Database.** `php artisan migrate`. This creates the `projects`, settings and `cv` tables. The host app must already have a `messages` table (`name`, `email`, `subject`, `message`, `device_info`), because this package doesn't create one.
5. **Uploads.** `php artisan storage:link`, so the profile picture, project screenshots and the site icon uploaded in the admin can be served.
6. **Content.** `php artisan portfolio:sync` imports `portfolio-sync.json`, so the page matches your machine.
7. **CV.** `php artisan portfolio:cv-init` creates the CV for `/cv` and `/resume` from the portfolio data.
8. **Caches.** `php artisan optimize:clear`.
9. **Admin.** Log in and check Admin → Appearance and Admin → Portfolio Settings. Set the site icon in metheme's settings (`app_ico`). Without it, the bundled "E" logo is used.
10. **Old published copies.** If `config/portfolio.php` or `resources/views/vendor/portfolio/` exist in the host app and you haven't customised them, delete them. A published `config/portfolio.php` replaces each top-level section of the package config (for example all of `seo`), so new keys added to the package never reach the page.

### Every update

On your machine:

```bash
php artisan portfolio:export     # only if you changed anything in the admin
git add -A && git commit -m "..." && git push
```

On the server:

```bash
composer update mestiaque/portfolio mestiaque/metheme   # not `composer install` (see "Syncing with the server")
php artisan migrate
php artisan optimize:clear
php artisan portfolio:sync
```

### After a deploy, check

- `/`, `/cv`, `/resume`, `/robots.txt` and `/sitemap.xml` all open.
- Send a test message from the contact form; it should arrive in Telegram.
- Paste the site URL into the [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/) and click "Scrape again", so the new share image (`og-image.jpg`) shows on Facebook, LinkedIn and WhatsApp.
- Test the home page with [Google's Rich Results Test](https://search.google.com/test/rich-results). It should find a Person and a WebSite.

### Still open

- If the two **sample projects** (Multi-vendor Marketplace, Inventory & ERP Suite) are still on the page, replace or delete them in Admin → Projects.
- Fill in the empty profile fields (languages, employment types, job locations, responsibilities) in Admin → Portfolio Settings. Empty fields are hidden, so nothing looks broken meanwhile.
- WhatsApp is left out of the contact list until the number is confirmed. Add it in Admin → Portfolio Settings → Contact.

## Installation

```bash
composer require mestiaque/portfolio
```

The service provider (`ME\Portfolio\PortfolioServiceProvider`) is auto-discovered. It registers:

| Route | Purpose |
| --- | --- |
| `GET /` | The portfolio page (`portfolio::index`) |
| `GET /portfolio-assets/{path}` | CSS/JS/images bundled in `src/public/portfolio`, served from the package |
| `POST /api/messages-store` | Contact form endpoint (throttled to 5/min per IP) |
| `GET /sitemap.xml`, `GET /robots.txt` | SEO helpers |
| `GET /cv`, `GET /resume` | Public CV (Bangladeshi format, `?lang=bn` for Bangla section titles) and ATS-friendly resume, both from the `cv` table |
| `POST /cv/unlock` | Password form for a password-protected CV (throttled to 10/min) |
| `/admin/projects` | Admin CRUD for the `projects` table (auth required) |
| `/admin/appearance` | Colour preset, cursor trail, portrait studio and profile picture |
| `/admin/portfolio-settings` | Every text on the page, plus the sync file download / import |
| `/admin/cv` | Admin → CV & Resume: everything on `/cv` and `/resume` |

Run the migrations once after installing or updating (`php artisan migrate`); the `projects` table gets extra columns for the card and modal fields.
The host app must provide the `activityLog` middleware alias, `ME\Services\TelegramBotService`, and a `messages` table (`name`, `email`, `subject`, `message`, `device_info`).

## Running locally

From the host Laravel app:

```bash
php artisan serve
# open http://127.0.0.1:8000
```

## Admin panel

Four pages appear in the metheme sidebar. Super admins and developers see them; give other roles the permissions below on the Roles page.

| Page | Permission | What it does |
| --- | --- | --- |
| Projects | `portfolio.projects` | Add, edit, order and hide projects: title, summary, description, problem, solution, features, challenges, links, technologies, categories, screenshot or icon |
| Appearance | `portfolio.appearance` | Colour preset, cursor trail, portrait studio and profile picture |
| Portfolio Settings | `portfolio.settings` | Every text on the page, and the sync file |
| CV & Resume | `portfolio.cv` | The CV and resume: every section, section order and titles, custom sections, the look, and who can see them |

### Projects

Once at least one project is active in Admin → Projects, the page shows those (in their order) instead of `config('portfolio.projects')`. With none active, it falls back to the config file. Categories are the ids from Portfolio Settings → Project categories.

### Appearance

- **Colour presets** (`src/Config/themes.php`): the six seasons of Bangladesh (গ্রীষ্ম, বর্ষা, শরৎ, হেমন্ত, শীত, বসন্ত), Pohela Boishakh, Lal-Sobuj (independence and victory days), Ekushe, Eid-ul-Fitr, Eid-ul-Adha, Shok (mourning, greyscale portrait), a dark preset and classic palettes such as Liquid Blue, Sundarban, Jamdani and Rongdhonu (rainbow). A preset only sets colours, as CSS variables, so the whole page follows it. Optional keys per preset: `gradient`, `glass` (dark presets), `on_accent`, `mono`.
- **Cursor trails** (`src/public/portfolio/js/cursor-trail.js`, canvas, no library): circle, sparkle, ants, ladybug, butterfly, fireflies, kite, bubbles, comet, hilsa, caterpillar, bee, krishnochura petals, rain, dragonfly, snow, confetti, paper boat, atom, sky lanterns, fireworks, moon, leaves, camel, Bangladesh flag, snowfall and balloons. They run only with a mouse and never with reduced motion. The admin page previews the selected trail live.
- **Portrait studio**: a live preview of the hero portrait. Dot colour (preset, accent or custom), disc colour, zoom, focus, brightness, contrast, detail, dot size, plain-white-backdrop removal and invert. The profile picture uploaded here replaces `profile.photo`.

### CV & Resume

Admin → CV & Resume (`/admin/cv`) edits the one CV row behind `/cv` (Bangladeshi format, `?lang=bn` for Bangla titles) and `/resume` (ATS-friendly). The buttons at the top open both.

- **Visibility & preset.** Public, unlisted (anyone with the link, not in search engines), password, or private (signed-in users only). A profession preset (IT, engineer, doctor, banker, teacher, RMG, NGO, government, sales, fresher) sets the section order, titles and objective/summary when "Apply this preset" is ticked. It never deletes section content.
- **Sections & order.** Turn each section on or off, rename it in English and Bangla, choose web and/or print, and move it up or down. Empty sections are skipped automatically.
- **Look.** Accent colour, font, size, spacing, date format, paper, margin, icons and page numbers.
- **Content.** One tab per section: personal identity (with a CV photo upload), career objective/summary, employment, education, training, certifications, registrations, skills, computer skills, languages, projects, achievements, extra-curricular, personal details, references and the declaration (with a signature upload). Lists have Add, move up/down and remove. Skill lists are one per line, as `Laravel` or `Laravel | Expert`.
- **Personal details.** Every row prints, empty if blank. NID and passport stay hidden until you tick them, because `/cv` can be public.
- **Custom sections.** Anything else: a paragraph, a bullet list, `Label | value` rows or `Title | period | description` rows.
- **Fill from portfolio.** Rebuilds personal details, career summary, employment, education and skills from Portfolio Settings. It keeps the Bangla name, permanent address, career objective and an uploaded CV photo.

The CV is created from the portfolio data the first time `/cv` or `/admin/cv` is opened (or by `php artisan portfolio:cv-init`). After that it's separate: later changes in Portfolio Settings only reach it through "Fill from portfolio". Uploaded photos and signatures are stored in `storage/app/public/cv` (`php artisan storage:link` is needed). The CV is **not** in the sync file, because it holds personal data such as phone numbers and NID; edit it on each site.

### Portfolio Settings

Every text on the page, in ten sections: Hero & profile, About, Education, Skills, Experience, Project categories, Contact, Contact form, Labels & buttons (headings, button text, dock names, footer) and SEO & sharing. Lists have Add, move up/down and remove.

Each section is stored as one JSON row in the `settings` table (`portfolio_content_{section}`) and laid over `config/portfolio.php` on every web request. A section that was never saved still comes from the config file; tick "Reset this section" and save to go back to it. The first save stores every section, so edit the page here from then on, not the config file.

The form is generated from the schema in `src/Support/Content.php`. To add a field, add one line there and use the key in the view.

## Syncing with the server

`portfolio-sync.json` carries everything set in the admin panel: Portfolio Settings, Appearance, projects (with screenshots) and the profile picture. It lives in the package root, so it travels with this git repository.

Contact methods (email, phone, WhatsApp…) are **left out** of the file, and importing a file without them keeps the target site's own. Pass `--with-contact` to include them.

**On your machine**

```bash
php artisan portfolio:export          # writes portfolio-sync.json in the package
git add portfolio-sync.json && git commit -m "Update portfolio content" && git push
```

**On the server**

```bash
composer update mestiaque/portfolio mestiaque/metheme   # not `composer install`: see below
php artisan migrate
php artisan optimize:clear
php artisan portfolio:sync                               # or let the first visit do it
```

Both packages are `dev-master`, and `composer.lock` pins each to an exact commit. `composer install` installs that pinned commit, so a server deployed with an old lock file keeps old code however often you push. Use `composer update mestiaque/portfolio mestiaque/metheme` on the server, or run it locally and commit the app's updated `composer.lock`.

The first request after the file changes imports it once by itself (later requests only compare its size and timestamp). For deploy scripts, `php artisan portfolio:sync` does the same check right away. Importing makes the server match the file: **the projects in the file replace the server's projects**.

| Command | |
| --- | --- |
| `portfolio:export [path] [--no-images] [--with-contact]` | Write the sync file. Without a path it goes to the package root and is marked as applied, so this site never re-imports its own export |
| `portfolio:import [path] [--only=settings,projects,photo] [--force]` | Apply a file (asks first unless `--force`). Without a path it uses `portfolio-sync.json` in the app root if present, else the package's |
| `portfolio:sync [path]` | Import only if the file changed since it was last applied |
| `portfolio:cv-init [--fresh]` | Create the CV row from the portfolio data and the default preset. `--fresh` rebuilds it (asks first) |

Without SSH, use Admin → Portfolio Settings → "Sync with another site": download the file on one site, import it on the other, and choose which parts to apply.

Notes:

- The import waits until `php artisan migrate` has run; the site keeps working meanwhile.
- The package's own views are always used, even if `resources/views/vendor/portfolio` exists, so an old published copy can't hide new markup. Set `PORTFOLIO_PUBLISHED_VIEWS=true` to use published views instead.
- The file holds every Portfolio Settings section as this site shows it, including sections never saved in the admin (taken from this site's config), so the server can't fall back to an older `config/portfolio.php`. Only the contact form's mode and endpoint stay with each site's `.env`.
- The file is about 1.5 MB with pictures. Use `--no-images` when only text changed, to keep the repository small.
- Set `PORTFOLIO_AUTO_SYNC=false` to import only by hand.

## Halftone portrait component

The portrait dial from the About chapter, usable on its own in any Blade view:

```blade
<x-portfolio::halftone-portrait src="images/me.jpg" />
<x-portfolio::halftone-portrait src="images/me.jpg" color="#00d4ff" :ring="false" size="380" label="Portrait of Jane" />
<x-portfolio::halftone-portrait src="images/me.jpg" color="#4b8bff" tone="light" size="300" />
```

| Prop | Default | |
| --- | --- | --- |
| `src` | required | Image path or URL. It must be same-origin (or served with CORS headers) because the pixels are read to place the dots |
| `color` | `#ff4b4b` | Dot and tick colour (`#rrggbb`); the matching ring segment lights up |
| `size` | `560` | Max width in px; the canvas is square and shrinks to fit its container |
| `ring` | `true` | `false` draws the dots only |
| `label` | `Portrait` | Accessible name |
| `tone` | `dark` | Page background, `dark` or `light`. `light` darkens the ring strokes and puts a navy disc behind the dots so the portrait reads as a positive |
| `disc` | `#0f172a` | Disc colour behind the dots (`tone="light"` only) |
| `adjust` | `[]` | Image tweaks: `zoom` (1–3), `x`/`y` focus (-1…1), `brightness`, `contrast`, `grid` (dots per row), `dot` (size), `bg` (`auto` drops a plain white backdrop, `keep`), `invert` |
| `start` | `photo` | What shows first: `photo` (a click, Enter or Space turns it into dots, and back) or `dots` (the photo loads on the first click). The admin Portrait Studio preview uses `dots` |
| `hint` | | Accessible hint for that click (screen readers only) |

It loads `js/halftone-portrait.js` once per page and needs nothing else (no anime.js, no `main.js`). After changing the canvas data attributes, `canvas.halftone.refresh()` redraws it. A plain white or grey studio backdrop is detected and left empty, so the face gets the dots instead of the background. The dots are sized from the grid spacing, so they read at any size. They grow in from the centre on first view (time-based, about 1.3 s) and lean away from the cursor; with reduced motion it draws a single still frame.

## Editing content

The page reads its content in this order: **Portfolio Settings** (database) → `config/portfolio.php` in the host app → the package defaults. The admin panel is the normal place to edit; the config file holds the starting content and anything never saved in the admin.

To edit the file, publish it to the host app:

```bash
php artisan vendor:publish --tag=portfolio-config   # → config/portfolio.php
```

| Key | What it controls |
| --- | --- |
| `seo` | Page title, description, Open Graph / Twitter tags |
| `profile` | Name, designation, tagline, photo, optional hero cover image, optional CV link, availability badge |
| `about.bio`, `about.facts` | Biography paragraphs and the info tiles (name, experience, location, languages…) |
| `education` | Timeline entries: degree, institution, specialisation, start/end, description, credentials |
| `skill_categories` | Filter categories and the skills in each. There are deliberately no percentages |
| `experience` | Jobs: company, title, type, dates (`end => null` shows "Present"), location, responsibilities, technologies, achievements |
| `project_categories`, `projects` | Project filters, plus cards and modal content. A project can belong to several categories |
| `contact.methods` | Contact rows: `href` is the click target, `copy` is what the copy button copies (`null` hides it), `action` is the button beside copy (`['icon' => 'chat-dots', 'label' => 'Chat on WhatsApp']`) |
| `contact_form` | Form mode and endpoint (see below) |
| `labels` | Optional overrides for headings, buttons, dock names and the footer (defaults in `Support/Content.php`) |
| `auto_sync` | Import `portfolio-sync.json` automatically when it changes (`PORTFOLIO_AUTO_SYNC`) |
| `published_views` | Use views published to `resources/views/vendor/portfolio` instead of the package's (`PORTFOLIO_PUBLISHED_VIEWS`, off) |

Rules of thumb:

- **Empty means hidden.** Set an optional value to `''`, `null` or `[]` and the row, badge or link disappears. Don't use placeholder text.
- **Images.** Put files in the host app's `public/` (e.g. `public/images/me.jpg`) and reference them as `'images/me.jpg'`; absolute URLs also work. Without a photo, the hero shows a gradient initials avatar. Without a project image, the card shows the project's `icon` on a gradient. Images that fail to load fall back to the same placeholder.
- **Sample projects.** `'sample' => true` adds a visible "Sample" badge and a note in the modal. Use it for illustrative entries, and replace or delete them before relying on the page.
- **Icons** are [Bootstrap Icons](https://icons.getbootstrap.com) names without the `bi-` prefix.

Clear cached config after editing in production: `php artisan config:clear` (or `config:cache`).

### Customising the look

- Pick a colour preset in Admin → Appearance. To add one, copy an entry in `src/Config/themes.php`; it shows up in the admin automatically.
- Radii, shadows, blur and timing are CSS variables at the top of `src/public/portfolio/css/style.css` (`--glass-*`, `--dur*`…); presets set `--accent`, `--accent-2`, `--bg` and `--tint-*`.
- To change markup, publish the views (`php artisan vendor:publish --tag=portfolio-views` → `resources/views/vendor/portfolio`) and set `PORTFOLIO_PUBLISHED_VIEWS=true`. Published views don't receive package updates.
- The dock items are defined in `src/resources/views/partials/dock.blade.php`. Each `id` must match a section's `id`.

## Contact form: demo vs production

Set the mode in `config/portfolio.php` or `.env`:

```dotenv
PORTFOLIO_CONTACT_MODE=production      # or: demo
PORTFOLIO_CONTACT_ENDPOINT=/api/messages-store
```

- **production** (default): the form POSTs JSON `{name, email, subject, message, website}` to the endpoint. The success animation plays **only after a 2xx response**. A 422 response maps `errors.{field}` onto the matching inputs. A 429, a timeout (15 s) or any other failure shows an error and keeps the user's input.
- **demo**: the form is validated in the browser, no request is made, and both the form and the success state say plainly that nothing was sent.

The bundled endpoint validates input, stores a `Message`, and sends a Telegram notification. A Telegram failure is logged and doesn't fail the request, because the message is already saved. A hidden honeypot field (`website`) silently drops bot submissions.

To use a different backend (Formspree, your own API, etc.), point `PORTFOLIO_CONTACT_ENDPOINT` at it. It should accept JSON, return 2xx on success, and return 422 `{ "errors": { "email": ["…"] } }` for validation errors. Keep API keys on the server, never in the page.

## File layout

```
portfolio-sync.json                    # admin content for syncing (see "Syncing with the server")
src/
├── Config/
│   ├── config.php                     # starting content (Portfolio Settings override it)
│   ├── themes.php                     # colour presets and cursor trail list
│   ├── cv_presets.php                 # CV profession presets (section order and titles)
│   ├── permissions.php, sidebar.php   # metheme permissions and menu
├── Console/                           # portfolio:export, portfolio:import, portfolio:sync, portfolio:cv-init
├── Http/Controllers/
│   ├── PortfolioController.php        # public page, assets, contact endpoint
│   ├── ProjectController.php          # Admin → Projects
│   ├── AppearanceController.php       # Admin → Appearance
│   ├── PortfolioSettingsController.php# Admin → Portfolio Settings, sync download/import
│   ├── CvController.php               # public /cv and /resume, password unlock
│   └── CvAdminController.php          # Admin → CV & Resume
├── Support/
│   ├── Theme.php                      # preset, cursor, portrait and photo settings
│   ├── Content.php                    # Portfolio Settings schema, clean-up, runtime override
│   ├── Sync.php                       # sync file export/import, auto-apply
│   └── Cv/                            # CV/resume presenters, section list, portfolio import, admin form (Form.php)
├── Models/{Project,Message,Cv}.php
├── database/migrations/
├── PortfolioServiceProvider.php
├── public/portfolio/
│   ├── css/style.css                  # tokens, glass, dock, sections, motion, presets, responsive
│   ├── js/main.js                     # dock, reveal, filters, modal, copy, form, designation roll
│   ├── js/motion.js                   # anime.js entrance, scroll and pointer effects
│   ├── js/cursor-trail.js             # the cursor trails
│   └── js/halftone-portrait.js        # the portrait dial
├── resources/views/
│   ├── index.blade.php                # <head>, SEO, theme variables, layout, scripts
│   ├── sections/{hero,about,skills,experience,projects,contact}.blade.php
│   ├── partials/{dock,project-modal}.blade.php
│   ├── components/halftone-portrait.blade.php
│   ├── projects/                      # admin project pages (_form shared by create/edit)
│   ├── appearance/edit.blade.php
│   ├── settings/                      # Portfolio Settings form (built from the schema)
│   ├── cv/                            # CV print layout, sections, password page
│   ├── cv-admin/edit.blade.php        # Admin → CV & Resume
│   └── resume/                        # ATS-friendly resume
└── routes/{web,api}.php
```

## Design and implementation notes

- **Content is rendered server-side** from config, not built by JavaScript from a `data.js`. Search engines and no-JS visitors get the full page, and the config file plays the role of `data.js`. JS only adds behaviour. The project modal reads one JSON blob (`#portfolio-projects`) and fills a single shared modal using `textContent`/`createElement`, never `innerHTML`.
- **Dock:** a centred glass tab bar flush with the bottom edge; the iPhone home-bar inset is padding inside it. On desktop, labels show as tooltips and the hovered icon magnifies, with neighbours scaling slightly (`:has()`). On phones, labels sit under the icons and every target is at least 44 px. The active item is tracked with an `IntersectionObserver` band near the middle of the viewport. It is shown with a fill, a dot and `aria-current`, so it doesn't rely on colour alone.
- **Glass with a fallback:** browsers without `backdrop-filter` get near-opaque surfaces, so text stays readable.
- **Motion** is limited to transform/opacity: hero entrance (split name, drawn lines, counting stats), designation roll, scroll reveal and progress, card spotlight and tilt, magnetic buttons, dock magnification, modal scale, copy feedback and the 3-second OK animation. anime.js is loaded from a CDN; without it the CSS entrance still plays. `prefers-reduced-motion` disables all of it, and content is visible immediately.
- **Assets** are served from the package with a long cache and a `?v=<mtime>` cache-buster, so deploys don't need `vendor:publish`. The `portfolio-assets` publish tag is available if you'd rather let the web server serve them.

## Testing checklist

- [ ] No top header; the dock stays fixed and usable at 320, 375, 576, 768, 992, 1200 and 1440 px, with no horizontal scroll
- [ ] Clicking each dock item scrolls to its section; scrolling manually updates the active item; the bottom of the page highlights Contact
- [ ] Skill and project filters work (projects with several categories appear under each); an empty category shows the empty state
- [ ] "View details" fills the modal for the right project; it closes with ✕, Escape and a backdrop click, and focus returns to the button
- [ ] Missing demo/source links are hidden in cards and modal; missing images show the icon placeholder
- [ ] Copy buttons copy the configured value and show a toast; a blocked clipboard shows an error toast
- [ ] Empty submit shows four field errors and focuses the first; an invalid email is rejected
- [ ] Production: success appears only after the server responds 2xx; 422/429/500 show errors and keep the input; double-clicking sends one request
- [ ] The success state shows for ~3 s, then the form resets and focus returns to Name
- [ ] Demo mode: no network request, and the message says nothing was sent
- [ ] Keyboard: skip link, visible focus rings, all controls reachable; reduced motion shows all content without animation
- [ ] The copyright year is current; the browser console is clean
- [ ] Switching the preset in Admin → Appearance recolours the page, including dark mode; the cursor trail and portrait studio preview update live
- [ ] A text changed in Portfolio Settings shows on the page; "Reset this section" brings back the config value
- [ ] `portfolio:export` then `portfolio:import` leaves the site unchanged; the sync file contains no email or phone number
