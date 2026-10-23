/*
 * Motion layer: entrance, scroll and pointer effects on top of main.js.
 *   hero name/lines/stats/code entrance, magnetic buttons, portrait tilt,
 *   section-title word reveal, card spotlight + project tilt, filter
 *   re-entry, jobs timeline progress, scroll progress bar, dock magnification.
 * Uses anime.js (loaded before this file) when present; the page is fully
 * usable without it, and nothing here runs for reduced-motion visitors.
 */
(() => {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const A = window.anime;
    const clamp = (v, lo, hi) => Math.min(hi, Math.max(lo, v));

    // One rAF-throttled scroll/resize hook shared by the progress bar and the timeline.
    const onScrollFrame = (fn) => {
        let queued = false;
        const run = () => { queued = false; fn(); };
        const queue = () => { if (!queued) { queued = true; requestAnimationFrame(run); } };
        window.addEventListener('scroll', queue, { passive: true });
        window.addEventListener('resize', queue, { passive: true });
        run();
    };

    /* ---------------------------------------------------------------- *
     * Scroll progress bar (static for reduced motion too: it is feedback, not decoration)
     * ---------------------------------------------------------------- */
    const bar = $('.scroll-progress');
    if (bar) {
        onScrollFrame(() => {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.setProperty('--progress', max > 0 ? clamp(window.scrollY / max, 0, 1).toFixed(4) : '0');
        });
    }

    /* ---------------------------------------------------------------- *
     * Jobs timeline: fill the line up to the middle of the viewport, light reached markers
     * ---------------------------------------------------------------- */
    const timeline = $('[data-timeline-progress]');
    if (timeline) {
        const markers = $$('.timeline__marker', timeline);
        onScrollFrame(() => {
            const r = timeline.getBoundingClientRect();
            const mid = window.innerHeight * 0.55;
            const p = reducedMotion ? 1 : clamp((mid - r.top) / r.height, 0, 1);
            timeline.style.setProperty('--progress', p.toFixed(4));
            markers.forEach((m) => m.classList.toggle('is-lit', m.getBoundingClientRect().top < mid));
        });
    }

    if (reducedMotion) return;

    /* ---------------------------------------------------------------- *
     * Card spotlight: every glass card lights up under the cursor
     * ---------------------------------------------------------------- */
    if (finePointer) {
        let lit = null;
        document.addEventListener('pointermove', (e) => {
            const card = e.target.closest?.('.glass-card, .skill-group');
            if (lit && lit !== card) { lit.style.removeProperty('--sx'); lit.style.removeProperty('--sy'); }
            lit = card;
            if (!card) return;
            const r = card.getBoundingClientRect();
            card.style.setProperty('--sx', `${e.clientX - r.left}px`);
            card.style.setProperty('--sy', `${e.clientY - r.top}px`);
        }, { passive: true });
    }

    /* ---------------------------------------------------------------- *
     * Project cards: tilt toward the cursor
     * ---------------------------------------------------------------- */
    if (finePointer) {
        $$('.project-card[data-tilt]').forEach((card) => {
            card.addEventListener('pointermove', (e) => {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                card.classList.add('is-tilting');
                card.style.setProperty('--rx', `${(-y * 7).toFixed(2)}deg`);
                card.style.setProperty('--ry', `${(x * 9).toFixed(2)}deg`);
            });
            card.addEventListener('pointerleave', () => {
                card.classList.remove('is-tilting');
                card.style.removeProperty('--rx');
                card.style.removeProperty('--ry');
            });
        });
    }

    /* ---------------------------------------------------------------- *
     * Hero: portrait tilt and magnetic buttons
     * ---------------------------------------------------------------- */
    const hero = $('.hero');
    const portrait = $('.hero__portrait .halftone');
    if (hero && portrait && finePointer) {
        hero.addEventListener('pointermove', (e) => {
            const r = hero.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width - 0.5;
            const y = (e.clientY - r.top) / r.height - 0.5;
            portrait.style.setProperty('--rx', `${(-y * 10).toFixed(2)}deg`);
            portrait.style.setProperty('--ry', `${(x * 14).toFixed(2)}deg`);
        }, { passive: true });
        hero.addEventListener('pointerleave', () => {
            portrait.style.removeProperty('--rx');
            portrait.style.removeProperty('--ry');
        });
    }

    if (finePointer) {
        $$('[data-magnetic]').forEach((btn) => {
            btn.addEventListener('pointermove', (e) => {
                const r = btn.getBoundingClientRect();
                const x = (e.clientX - r.left - r.width / 2) * 0.25;
                const y = (e.clientY - r.top - r.height / 2) * 0.35;
                btn.classList.add('is-pulled');
                btn.style.translate = `${x.toFixed(1)}px ${y.toFixed(1)}px`;
            });
            btn.addEventListener('pointerleave', () => {
                btn.classList.remove('is-pulled');
                btn.style.translate = '';
            });
        });
    }

    /* ---------------------------------------------------------------- *
     * Dock: magnify icons near the cursor (desktop only)
     * ---------------------------------------------------------------- */
    const dock = $('.dock');
    if (dock && finePointer && window.matchMedia('(min-width: 768px)').matches) {
        const icons = $$('.dock__item', dock).map((item) => ({ item, icon: $('.dock__icon', item) }));
        dock.addEventListener('pointermove', (e) => {
            icons.forEach(({ item, icon }) => {
                const r = item.getBoundingClientRect();
                const d = Math.abs(e.clientX - (r.left + r.width / 2));
                icon.style.scale = (1 + 0.45 * Math.max(0, 1 - d / 120)).toFixed(3);
            });
        });
        dock.addEventListener('pointerleave', () => icons.forEach(({ icon }) => { icon.style.scale = ''; }));
    }

    // Everything below animates with anime.js; without it the CSS entrance stays in charge.
    root.classList.remove('motion-pending');
    if (!A) return;
    const { animate, stagger, svg } = A;

    /* ---------------------------------------------------------------- *
     * Splitting helpers. The original text stays available to assistive tech via aria-label.
     * ---------------------------------------------------------------- */
    function splitChars(el) {
        const text = el.textContent.trim();
        el.setAttribute('aria-label', text);
        el.replaceChildren(...text.split(/\s+/).flatMap((word, i) => {
            const w = document.createElement('span');
            w.className = 'word';
            w.setAttribute('aria-hidden', 'true');
            Array.from(word).forEach((ch) => {
                const c = document.createElement('span');
                c.className = 'char';
                c.textContent = ch;
                w.appendChild(c);
            });
            return i ? [document.createTextNode(' '), w] : [w];
        }));
        return $$('.char', el);
    }

    function splitWords(el) {
        const text = el.textContent.trim();
        el.setAttribute('aria-label', text);
        el.replaceChildren(...text.split(/\s+/).flatMap((word, i) => {
            const mask = document.createElement('span');
            mask.className = 'word-mask';
            mask.setAttribute('aria-hidden', 'true');
            const w = document.createElement('span');
            w.className = 'word';
            w.textContent = word;
            mask.appendChild(w);
            return i ? [document.createTextNode(' '), mask] : [mask];
        }));
        return $$('.word', el);
    }

    const whenVisible = (el, fn, rootMargin = '0px 0px -12% 0px') => {
        const io = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) return;
            io.disconnect();
            fn();
        }, { rootMargin });
        io.observe(el);
    };

    /* ---------------------------------------------------------------- *
     * Hero entrance
     * ---------------------------------------------------------------- */
    const name = $('.hero__name[data-split]');
    if (name) {
        name.style.animation = 'none';
        const chars = splitChars(name);
        animate(chars, {
            opacity: [0, 1],
            y: ['0.55em', '0em'],
            filter: ['blur(10px)', 'blur(0px)'],
            duration: 900,
            delay: stagger(28, { start: 150 }),
            ease: 'outExpo',
        });
    }

    const lines = $$('.hero__lines path');
    if (lines.length) {
        animate(svg.createDrawable(lines), {
            draw: ['0 0', '0 1'],
            duration: 2400,
            delay: stagger(220, { start: 200 }),
            ease: 'inOutQuad',
        });
        // Afterwards the lines breathe slowly, each on its own phase.
        animate(lines, {
            y: (_, i) => [0, i % 2 ? -10 : 10],
            duration: (_, i) => 7000 + i * 1300,
            delay: 2600,
            alternate: true,
            loop: true,
            ease: 'inOutSine',
        });
    }

    const counters = $$('.hero__stats [data-count]');
    if (counters.length) {
        whenVisible(counters[0], () => {
            counters.forEach((el, i) => {
                const state = { n: 0 };
                const target = Number(el.dataset.count) || 0;
                el.textContent = '0';
                animate(state, {
                    n: target,
                    duration: 1600,
                    delay: 700 + i * 120,
                    ease: 'outExpo',
                    onUpdate: () => { el.textContent = String(Math.round(state.n)); },
                });
            });
        }, '0px');
    }

    // The code chip types itself out once, then keeps a blinking cursor.
    const code = $('.hero__code-text[data-code]');
    if (code && getComputedStyle(code.parentElement).display !== 'none') {
        const full = code.dataset.code;
        code.textContent = '';
        code.classList.add('is-typing');
        let i = 0;
        const type = () => {
            code.textContent = full.slice(0, ++i);
            if (i < full.length) setTimeout(type, 28 + Math.random() * 40);
        };
        setTimeout(type, 1600);
    }

    /* ---------------------------------------------------------------- *
     * Section titles: words rise into place as the section arrives
     * ---------------------------------------------------------------- */
    $$('.section-title[data-split-words]').forEach((title) => {
        const words = splitWords(title);
        words.forEach((w) => { w.style.transform = 'translateY(110%)'; });
        whenVisible(title, () => {
            animate(words, {
                y: ['110%', '0%'],
                duration: 900,
                delay: stagger(70, { start: 120 }),
                ease: 'outExpo',
            });
        });
    });

    /* ---------------------------------------------------------------- *
     * Filters: shown items re-enter with a stagger (main.js does the filtering)
     * ---------------------------------------------------------------- */
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-filter-group] .filter-btn');
        if (!btn) return;
        const target = $(`[data-filter-target="${btn.closest('[data-filter-group]').dataset.filterGroup}"]`);
        if (!target) return;
        const shown = Array.from(target.children).filter((c) => !c.hidden);
        shown.forEach((c) => c.classList.add('is-visible')); // skip the scroll-reveal, this takes over
        animate(shown, {
            opacity: [0, 1],
            y: [18, 0],
            scale: [0.97, 1],
            duration: 600,
            delay: stagger(60),
            ease: 'outExpo',
        });
    });
})();
