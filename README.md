# Portfolio Package

A Laravel package that renders a single-page developer portfolio: a liquid-glass UI with a macOS/Ubuntu-style floating dock, built with Bootstrap 5.3, Bootstrap Icons and vanilla JS. No build step.

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
| `/admin/projects` | Admin CRUD for the `projects` table (auth required) |

The host app must provide the `activityLog` middleware alias, `ME\Services\TelegramBotService`, and a `messages` table (`name`, `email`, `subject`, `message`, `device_info`).

## Running locally

From the host Laravel app:

```bash
php artisan serve
# open http://127.0.0.1:8000
```

## Editing content

All content lives in one file. Publish it to the host app and edit it there:

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
| `contact.methods` | Contact rows: `href` is the click target, `copy` is what the copy button copies (`null` hides it) |
| `contact_form` | Form mode and endpoint (see below) |

Rules of thumb:

- **Empty means hidden.** Set an optional value to `''`, `null` or `[]` and the row, badge or link disappears. Don't use placeholder text.
- **Images.** Put files in the host app's `public/` (e.g. `public/images/me.jpg`) and reference them as `'images/me.jpg'`; absolute URLs also work. Without a photo, the hero shows a gradient initials avatar. Without a project image, the card shows the project's `icon` on a gradient. Images that fail to load fall back to the same placeholder.
- **Sample projects.** `'sample' => true` adds a visible "Sample" badge and a note in the modal. Use it for illustrative entries, and replace or delete them before relying on the page.
- **Icons** are [Bootstrap Icons](https://icons.getbootstrap.com) names without the `bi-` prefix.

Clear cached config after editing in production: `php artisan config:clear` (or `config:cache`).

### Customising the look

- Colours, radii, shadows, blur and timing are CSS variables at the top of `src/public/portfolio/css/style.css` (`--accent`, `--accent-2`, `--glass-*`, `--dur*`…).
- To change markup, publish the views: `php artisan vendor:publish --tag=portfolio-views` (→ `resources/views/vendor/portfolio`).
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
src/
├── Config/config.php                  # all portfolio content
├── Http/Controllers/PortfolioController.php
├── PortfolioServiceProvider.php
├── public/portfolio/
│   ├── css/style.css                  # tokens, glass, dock, sections, motion, responsive
│   ├── js/main.js                     # dock, reveal, filters, modal, copy, form
│   └── images/favicon.svg
├── resources/views/
│   ├── index.blade.php                # <head>, SEO, layout, scripts
│   ├── sections/{hero,about,skills,experience,projects,contact}.blade.php
│   └── partials/{dock,project-modal}.blade.php
└── routes/{web,api}.php
```

## Design and implementation notes

- **Content is rendered server-side** from config, not built by JavaScript from a `data.js`. Search engines and no-JS visitors get the full page, and the config file plays the role of `data.js`. JS only adds behaviour. The project modal reads one JSON blob (`#portfolio-projects`) and fills a single shared modal using `textContent`/`createElement`, never `innerHTML`.
- **Dock:** fixed, centred, glass pill with safe-area insets. On desktop, labels show as tooltips and the hovered icon magnifies, with neighbours scaling slightly (`:has()`). On phones, labels sit under the icons and every target is at least 44 px. The active item is tracked with an `IntersectionObserver` band near the middle of the viewport. It is shown with a fill, a dot and `aria-current`, so it doesn't rely on colour alone.
- **Glass with a fallback:** browsers without `backdrop-filter` get near-opaque surfaces, so text stays readable.
- **Motion** is limited to transform/opacity: hero entrance, scroll reveal, hover lift, modal scale, copy feedback and the 3-second OK animation. `prefers-reduced-motion` disables all of it, and content is visible immediately.
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
