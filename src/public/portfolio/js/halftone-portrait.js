/*
 * Halftone portrait dial — the <x-portfolio::halftone-portrait> component.
 * Redraws a photo as dots (bigger where it is brighter) inside a colour ring
 * with ticks, a sweep and counter-rotating arcs. The dots lean away from the
 * cursor and grow in from the centre the first time the dial is seen.
 * Standalone: no dependency on main.js or anime.js.
 *
 * Options come from data attributes on the canvas (see readOptions). After
 * changing them, call canvas.halftone.refresh() to redraw; the admin
 * Appearance page does this for its live preview.
 *
 * With data-reveal="1" (default) a click, Enter or Space shows the real photo
 * inside the disc: it opens as a circle from the click point while the dots
 * swell and fade; clicking again brings the dots back. The photo is only
 * loaded on the first reveal.
 */
(() => {
    'use strict';

    const RING = ['#7dff6a', '#ff4b4b', '#ffa828', '#ffd23f', '#2ee59d', '#00d4ff', '#4b8bff', '#ff8a5c'];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const rgbOf = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16)).join(',');
    const hex = (v, fallback) => (/^#[0-9a-f]{6}$/i.test(v || '') ? v : fallback);
    const num = (v, fallback, lo, hi) => {
        const n = parseFloat(v);
        return Number.isFinite(n) ? Math.min(hi, Math.max(lo, n)) : fallback;
    };

    // The disc only just clears the outermost dots (half a grid step), so no empty dark rim is left.
    const discScale = (o) => 1 + 1 / o.grid;

    function readOptions(canvas) {
        const d = canvas.dataset;
        return {
            src: d.src || '',
            color: hex(d.color, '#ff4b4b'),
            disc: hex(d.disc, '#0f172a'),
            light: d.tone === 'light',
            ring: d.ring !== '0',
            grid: Math.round(num(d.grid, 48, 24, 80)),     // samples per side: higher = finer detail
            zoom: num(d.zoom, 1, 1, 3),                    // 1 = whole square crop
            x: num(d.x, 0, -1, 1),                         // crop focus, -1 left … 1 right
            y: num(d.y, 0, -1, 1),                         // crop focus, -1 top … 1 bottom
            brightness: num(d.brightness, 0, -0.5, 0.5),
            contrast: num(d.contrast, 1, 0.4, 2.5),
            dot: num(d.dot, 1, 0.5, 1.6),                  // dot size multiplier
            bg: d.bg === 'keep' ? 'keep' : 'auto',         // auto = drop a plain white backdrop
            invert: d.invert === '1',
            reveal: d.reveal !== '0',                      // click to show the real photo
            hint: d.hint || 'Click to see the photo',
        };
    }

    function sample(img, o) {
        const G = o.grid;
        const c = document.createElement('canvas');
        c.width = c.height = G;
        const g = c.getContext('2d');
        // Square crop, zoomed in and moved toward the chosen focus point.
        const w = img.naturalWidth, h = img.naturalHeight;
        const side = Math.min(w, h) / o.zoom;
        const sx = (w - side) / 2 + (o.x * (w - side)) / 2;
        const sy = (h - side) / 2 + (o.y * (h - side)) / 2;
        g.drawImage(img, sx, sy, side, side, 0, 0, G, G);
        const px = g.getImageData(0, 0, G, G).data;
        const backdrop = o.bg === 'auto' ? findBackdrop(px, G) : new Uint8Array(G * G);
        const dots = [];
        for (let y = 0; y < G; y++) {
            for (let x = 0; x < G; x++) {
                const u = (x / (G - 1)) * 2 - 1, v = (y / (G - 1)) * 2 - 1;
                if (u * u + v * v > 1 || backdrop[y * G + x]) continue;
                const i = (y * G + x) * 4;
                let lum = (px[i] * 0.3 + px[i + 1] * 0.59 + px[i + 2] * 0.11) / 255;
                if (o.invert) lum = 1 - lum;
                dots.push({ u, v, lum, d: Math.hypot(u, v) });
            }
        }
        // Stretch the 5th–95th percentile to 0..1 (with a slight lift in the mids), so one very bright
        // patch, like a white shirt, doesn't flatten the face. Then apply the chosen contrast/brightness.
        const sorted = dots.map((p) => p.lum).sort((a, b) => a - b);
        const lo = sorted[Math.floor(sorted.length * 0.05)] ?? 0, hi = sorted[Math.floor(sorted.length * 0.95)] ?? 1;
        dots.forEach((p) => {
            let l = Math.min(1, Math.max(0, (p.lum - lo) / Math.max(0.01, hi - lo)));
            l = (l - 0.5) * o.contrast + 0.5 + o.brightness;
            p.lum = Math.pow(Math.min(1, Math.max(0, l)), 0.8);
        });
        return dots.filter((p) => p.lum > 0.04);
    }

    // A plain white/grey studio backdrop is the brightest thing in the photo, so it would get the
    // biggest dots and drown the face. Flood-fill bright, colourless pixels that touch the top or
    // sides (not the bottom, where a white shirt usually meets the edge) and leave them empty.
    function findBackdrop(px, G) {
        const plain = (k) => {
            const i = k * 4, r = px[i], gr = px[i + 1], b = px[i + 2];
            return (r * 0.3 + gr * 0.59 + b * 0.11) / 255 > 0.82 && Math.max(r, gr, b) - Math.min(r, gr, b) < 40;
        };
        const mask = new Uint8Array(G * G);
        const queue = [];
        const seed = (x, y) => { const k = y * G + x; if (!mask[k] && plain(k)) { mask[k] = 1; queue.push(k); } };
        for (let x = 0; x < G; x++) seed(x, 0);
        for (let y = 0; y < G; y++) { seed(0, y); seed(G - 1, y); }
        while (queue.length) {
            const k = queue.pop(), x = k % G, y = (k - x) / G;
            if (x > 0) seed(x - 1, y);
            if (x < G - 1) seed(x + 1, y);
            if (y > 0) seed(x, y - 1);
            if (y < G - 1) seed(x, y + 1);
        }
        // Only trust a real backdrop: a few stray pixels, or a whole white image, mean "keep everything".
        const share = mask.reduce((n, m) => n + m, 0) / mask.length;
        if (share <= 0.08 || share >= 0.85) return new Uint8Array(G * G);
        // Grow the mask one sample so the soft, half-bright outline around hair and shoulders goes too.
        const grown = mask.slice();
        for (let k = 0; k < mask.length; k++) {
            if (mask[k]) continue;
            const x = k % G, y = (k - x) / G;
            if ((x > 0 && mask[k - 1]) || (x < G - 1 && mask[k + 1]) || (y > 0 && mask[k - G]) || (y < G - 1 && mask[k + G])) grown[k] = 1;
        }
        return grown;
    }

    function mount(canvas) {
        const ctx = canvas.getContext('2d');
        let o, ring, rgb, ink;
        const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
        let dots = [], size = 0, dpr = 1, t = 0, grow = reducedMotion.matches ? 1 : 0, onScreen = false;
        let img = null, loadedSrc = '';
        // Reveal: 0 = dots, 1 = photo. `target` is where it is heading.
        let reveal = 0, target = 0, overlay = null;
        const figure = canvas.parentElement;
        const baseLabel = canvas.getAttribute('aria-label') || 'Portrait';

        const applyOptions = () => {
            o = readOptions(canvas);
            // A colour outside the ring palette takes the blue slot, so one segment always lights up.
            ring = RING.some((c) => c.toLowerCase() === o.color.toLowerCase()) ? RING : RING.map((c) => (c === '#4b8bff' ? o.color : c));
            rgb = rgbOf(o.color);
            // Neutral strokes: pale on dark pages, slate on light ones.
            ink = o.light ? '15,23,42' : '246,244,242';
        };

        const resample = () => {
            if (!img || !img.naturalWidth) return;
            try { dots = sample(img, o); } catch (err) {
                // A cross-origin image without CORS headers can't be read.
                console.warn('halftone-portrait: could not read image pixels', err);
            }
        };

        const resize = () => {
            dpr = Math.min(window.devicePixelRatio || 1, 2);
            size = canvas.getBoundingClientRect().width;
            canvas.width = canvas.height = Math.round(size * dpr);
            if (reducedMotion.matches) draw();
        };

        const arc = (r, a0, a1, stroke, width, glow) => {
            ctx.beginPath();
            ctx.arc(0, 0, r, a0, a1);
            ctx.strokeStyle = stroke;
            ctx.lineWidth = width;
            ctx.shadowColor = stroke;
            ctx.shadowBlur = glow ? 14 : 0;
            ctx.stroke();
            ctx.shadowBlur = 0;
        };

        function drawRing(R) {
            const spin = t * 0.15 + pointer.x * 0.5;
            const seg = (Math.PI * 2) / ring.length;
            ring.forEach((c, k) => {
                const lit = c.toLowerCase() === o.color.toLowerCase();
                ctx.globalAlpha = lit ? 1 : 0.22;
                arc(R, spin + k * seg + 0.03, spin + (k + 1) * seg - 0.03, c, 4, lit);
            });
            ctx.globalAlpha = 1;

            const sweep = (t * 0.6) % (Math.PI * 2);
            for (let k = 0; k < 180; k++) {
                const a = (k / 180) * Math.PI * 2;
                const near = Math.max(0, Math.cos(a - sweep));
                const len = k % 15 === 0 ? 12 : 7;
                ctx.strokeStyle = `rgba(${rgb},${0.22 + Math.pow(near, 6) * 0.7})`;
                ctx.lineWidth = k % 15 === 0 ? 1.5 : 1;
                ctx.beginPath();
                ctx.moveTo(Math.cos(a) * R * 0.9, Math.sin(a) * R * 0.9);
                ctx.lineTo(Math.cos(a) * (R * 0.9 - len), Math.sin(a) * (R * 0.9 - len));
                ctx.stroke();
            }
            arc(R * 0.82, 0, Math.PI * 2, `rgba(${ink},.08)`, 1);
            arc(R * 0.76, 0, Math.PI * 2, `rgba(${ink},.05)`, 10);
            for (let k = 0; k < 4; k++) {
                const a0 = -spin * 1.4 + 0.15 + k * 0.05;
                arc(R * (0.8 - k * 0.035), a0, a0 + 0.9 - k * 0.12, `rgba(255,190,160,${0.75 - k * 0.15})`, 2);
            }
            arc(R * 0.72, Math.PI * 1.05 + spin * 0.3, Math.PI * 1.5 + spin * 0.3, `rgba(${ink},.2)`, 14);
        }

        function drawDots(inner) {
            // Size dots from the grid spacing so they read the same at any canvas size.
            const step = (inner * 2) / o.grid;
            for (let i = 0; i < dots.length; i++) {
                const p = dots[i];
                // Grow outwards from the centre on first view.
                const g = Math.min(1, Math.max(0, grow * 1.6 - p.d * 0.6));
                if (g <= 0) continue;
                const dist = Math.hypot(p.u - pointer.x * 0.8, p.v - pointer.y * 0.8);
                const push = 1 + Math.exp(-dist * dist * 8) * 0.06;
                // While the photo opens, the dots swell and fade out.
                const r = step * (0.16 + p.lum * 0.44) * o.dot * g * (1 + reveal * 0.8);
                ctx.fillStyle = `rgba(${rgb},${(0.25 + p.lum * 0.75) * g * (1 - reveal)})`;
                ctx.beginPath();
                ctx.arc((p.u * push + Math.sin(t * 2 + i) * 0.003) * inner, p.v * push * inner, r, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function draw() {
            if (!size) return;
            pointer.x += (pointer.tx - pointer.x) * 0.06;
            pointer.y += (pointer.ty - pointer.y) * 0.06;
            const R = size * 0.47;
            ctx.setTransform(dpr, 0, 0, dpr, (size / 2) * dpr, (size / 2) * dpr);
            ctx.clearRect(-size / 2, -size / 2, size, size);
            if (o.ring) drawRing(R);
            const inner = o.ring ? R * 0.6 : R;
            // Bright pixels make big dots, which reads as a negative on a light page,
            // so light pages get a dark disc behind the portrait.
            if (o.light) {
                ctx.beginPath();
                ctx.arc(0, 0, inner * discScale(o), 0, Math.PI * 2);
                ctx.fillStyle = o.disc;
                ctx.fill();
            }
            drawDots(inner);
        }

        // Time-based, so slow devices still finish the grow-in on schedule (~1.3 s).
        let last = 0;
        const loop = (now = 0) => {
            const dt = last ? (now - last) / 1000 : 0;
            last = now;
            if (onScreen && !document.hidden) {
                t += Math.min(0.05, dt); // motion steps stay small after a stall
                if (grow < 1) grow = Math.min(1, grow + dt * 0.75);
                if (reveal !== target) reveal = target > reveal ? Math.min(target, reveal + dt / 0.8) : Math.max(target, reveal - dt / 0.8);
                draw();
            }
            requestAnimationFrame(loop);
        };

        const load = () => {
            loadedSrc = o.src;
            if (!o.src) { img = null; dots = []; return; }
            const next = new Image();
            next.onload = () => {
                if (loadedSrc !== o.src) return; // a newer image was picked meanwhile
                img = next;
                resample();
                layoutOverlay();
                if (reducedMotion.matches) { t = 1.2; draw(); }
            };
            next.src = o.src;
        };

        /* ------------------------------------------------------------ *
         * Reveal: the real photo in an <img> over the disc, cropped exactly like the dots
         * ------------------------------------------------------------ */
        function ensureOverlay() {
            if (overlay || !o.reveal) return;
            overlay = document.createElement('div');
            overlay.className = 'halftone__photo';
            overlay.setAttribute('aria-hidden', 'true');
            overlay.appendChild(document.createElement('img'));
            const hint = document.createElement('span');
            hint.className = 'halftone__hint';
            hint.setAttribute('aria-hidden', 'true');
            figure.append(overlay, hint);
            figure.classList.add('halftone--reveal');
            canvas.tabIndex = 0;
            canvas.setAttribute('role', 'button');
        }

        // Same geometry as draw(): the dots fill a circle of radius `inner` and the disc is discScale() times that.
        // The photo's crop square (zoom + focus) fills the whole disc, so no dark rim is left.
        function layoutOverlay() {
            if (!overlay) return;
            // Reveal can be switched off later (Portrait studio): then the canvas is a plain image again.
            figure.classList.toggle('halftone--reveal', o.reveal);
            if (o.reveal) { canvas.tabIndex = 0; canvas.setAttribute('role', 'button'); }
            else {
                canvas.removeAttribute('tabindex');
                canvas.setAttribute('role', 'img');
                canvas.setAttribute('aria-label', baseLabel);
                canvas.removeAttribute('aria-pressed');
                return;
            }
            const inner = o.ring ? 0.47 * 0.6 : 0.47;
            const disc = Math.min(1, inner * 2 * discScale(o));
            overlay.style.width = `${disc * 100}%`;
            figure.querySelector('.halftone__hint').textContent = o.hint;
            canvas.setAttribute('aria-label', `${baseLabel}. ${o.hint}`);
            canvas.setAttribute('aria-pressed', target ? 'true' : 'false');
            if (!img || !img.naturalWidth) return;
            const w = img.naturalWidth, h = img.naturalHeight;
            const side = Math.min(w, h) / o.zoom;
            const sx = (w - side) / 2 + (o.x * (w - side)) / 2;
            const sy = (h - side) / 2 + (o.y * (h - side)) / 2;
            const f = 1; // the crop square's share of the disc
            const photo = overlay.querySelector('img');
            Object.assign(photo.style, {
                width: `${(w / side) * f * 100}%`,
                height: `${(h / side) * f * 100}%`,
                left: `${((1 - f) / 2 - (sx / side) * f) * 100}%`,
                top: `${((1 - f) / 2 - (sy / side) * f) * 100}%`,
            });
        }

        function toggle(e) {
            if (!o.reveal) return;
            ensureOverlay();
            const photo = overlay.querySelector('img');
            if (!photo.getAttribute('src') && o.src) photo.src = o.src; // first reveal: load it now
            // Open (or close) the circle from where the click landed; keyboard uses the centre.
            const r = overlay.getBoundingClientRect();
            const cx = e && e.clientX ? ((e.clientX - r.left) / r.width) * 100 : 50;
            const cy = e && e.clientY ? ((e.clientY - r.top) / r.height) * 100 : 50;
            overlay.style.setProperty('--cx', `${Math.min(100, Math.max(0, cx))}%`);
            overlay.style.setProperty('--cy', `${Math.min(100, Math.max(0, cy))}%`);
            target = target ? 0 : 1;
            figure.classList.toggle('is-revealed', target === 1);
            figure.classList.add('was-revealed');
            if (reducedMotion.matches) { reveal = target; draw(); }
            layoutOverlay();
        }

        canvas.addEventListener('click', toggle);
        canvas.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
        });

        canvas.addEventListener('pointermove', (e) => {
            const r = canvas.getBoundingClientRect();
            pointer.tx = ((e.clientX - r.left) / r.width) * 2 - 1;
            pointer.ty = ((e.clientY - r.top) / r.height) * 2 - 1;
        });
        canvas.addEventListener('pointerleave', () => { pointer.tx = 0; pointer.ty = 0; });
        new ResizeObserver(resize).observe(canvas);
        new IntersectionObserver(([e]) => { onScreen = e.isIntersecting; }).observe(canvas);

        applyOptions();
        ensureOverlay();
        layoutOverlay();
        resize();
        load();
        if (!reducedMotion.matches) loop();

        // Re-read the data attributes: reload the photo if it changed, otherwise just resample.
        canvas.halftone = {
            refresh() {
                const prevSrc = o.src;
                applyOptions();
                if (o.src !== prevSrc) {
                    load();
                    const photo = overlay?.querySelector('img');
                    // Open: swap the photo now; closed: load the new one on the next reveal.
                    if (photo) target ? (photo.src = o.src) : photo.removeAttribute('src');
                } else {
                    resample();
                }
                if (!o.reveal && target) { // reveal switched off while open: back to dots
                    target = 0;
                    figure.classList.remove('is-revealed');
                }
                ensureOverlay();
                layoutOverlay();
                if (reducedMotion.matches) draw();
            },
        };
    }

    const start = () => document.querySelectorAll('canvas[data-halftone]').forEach((c) => { if (!c.halftone) mount(c); });
    window.HalftonePortrait = { mount: (c) => { if (!c.halftone) mount(c); return c.halftone; }, start };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
