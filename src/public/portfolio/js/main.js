/*
 * Portfolio interactions. Content is rendered server-side from
 * config/portfolio.php; this file only adds behaviour:
 *   dock + active section, scroll reveal, skill/project filters,
 *   project modal, copy-to-clipboard, contact form.
 */
(() => {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    /* ---------------------------------------------------------------- *
     * Toast
     * ---------------------------------------------------------------- */
    const toastEl = $('#appToast');
    function showToast(message, isError = false) {
        if (!toastEl || !window.bootstrap) return;
        $('.toast-text', toastEl).textContent = message;
        $('.toast-icon', toastEl).className = `bi ${isError ? 'bi-exclamation-circle' : 'bi-check2-circle'} toast-icon`;
        toastEl.classList.toggle('is-error', isError);
        bootstrap.Toast.getOrCreateInstance(toastEl).show();
    }

    /* ---------------------------------------------------------------- *
     * Current year (server renders it too; this keeps cached pages right)
     * ---------------------------------------------------------------- */
    $$('[data-current-year]').forEach((el) => { el.textContent = String(new Date().getFullYear()); });

    /* ---------------------------------------------------------------- *
     * Dock: highlight the section currently in view
     * ---------------------------------------------------------------- */
    const dockLinks = $$('.dock__link');
    const sections = dockLinks.map((a) => document.getElementById(a.dataset.dock)).filter(Boolean);
    let activeId = null;

    function setActive(id) {
        if (id === activeId) return;
        activeId = id;
        dockLinks.forEach((a) => {
            const on = a.dataset.dock === id;
            a.classList.toggle('is-active', on);
            if (on) a.setAttribute('aria-current', 'location');
            else a.removeAttribute('aria-current');
        });
        positionBubble();
    }

    // Slide the glass bubble under the active icon.
    const dock = $('.dock');
    const bubble = $('.dock__bubble');
    function positionBubble() {
        const link = $('.dock__link.is-active');
        if (!dock || !bubble || !link) return;
        const dockRect = dock.getBoundingClientRect();
        const item = link.parentElement.getBoundingClientRect(); // unaffected by the link's lift/hover transforms
        const x = item.left - dockRect.left - dock.clientLeft + item.width / 2 - bubble.offsetWidth / 2;
        bubble.style.setProperty('--bubble-x', `${x}px`);
        dock.classList.add('is-ready');
    }
    positionBubble();
    window.addEventListener('resize', () => requestAnimationFrame(positionBubble), { passive: true });
    document.fonts?.ready.then(positionBubble);
    // The dock slides in on load; measure again once it has settled.
    dock?.addEventListener('animationend', positionBubble);

    if ('IntersectionObserver' in window && sections.length) {
        // A thin band a little above the middle of the viewport decides which section is "current".
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => { if (entry.isIntersecting) setActive(entry.target.id); });
        }, { rootMargin: '-40% 0px -55% 0px' });
        sections.forEach((s) => sectionObserver.observe(s));

        // The last section may never reach the band on tall screens; treat "scrolled to the end" as Contact.
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => {
                ticking = false;
                const atEnd = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
                if (atEnd) setActive(sections[sections.length - 1].id);
            });
        }, { passive: true });
    }

    document.addEventListener('click', (e) => {
        const link = e.target.closest('.dock__link');
        if (link) setActive(link.dataset.dock);
    });

    /* ---------------------------------------------------------------- *
     * Reveal on scroll
     * ---------------------------------------------------------------- */
    const revealEls = $$('.reveal');
    if ('IntersectionObserver' in window && !reducedMotion.matches) {
        const revealObserver = new IntersectionObserver((entries, obs) => {
            entries.filter((e) => e.isIntersecting).forEach((entry, i) => {
                entry.target.style.setProperty('--reveal-delay', `${Math.min(i, 5) * 80}ms`);
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
        revealEls.forEach((el) => revealObserver.observe(el));
    } else {
        revealEls.forEach((el) => el.classList.add('is-visible'));
    }

    /* ---------------------------------------------------------------- *
     * Filters (skills + projects share one implementation)
     * ---------------------------------------------------------------- */
    function applyFilter(group, filter, label) {
        const target = $(`[data-filter-target="${group}"]`);
        if (!target) return;
        let shown = 0;
        Array.from(target.children).forEach((item) => {
            const cats = (item.dataset.category || '').split(/\s+/);
            const match = filter === 'all' || cats.includes(filter);
            item.hidden = !match;
            if (match) shown += 1;
        });
        const empty = $(`[data-empty-for="${group}"]`);
        if (empty) empty.hidden = shown > 0;
        const status = $(`[data-filter-status="${group}"]`);
        if (status) status.textContent = `${label}: ${shown} ${shown === 1 ? 'item' : 'items'} shown.`;
    }

    $$('[data-filter-group]').forEach((bar) => {
        bar.addEventListener('click', (e) => {
            const btn = e.target.closest('.filter-btn');
            if (!btn || btn.classList.contains('is-active')) return;
            $$('.filter-btn', bar).forEach((b) => {
                const on = b === btn;
                b.classList.toggle('is-active', on);
                b.setAttribute('aria-pressed', String(on));
            });
            applyFilter(bar.dataset.filterGroup, btn.dataset.filter, btn.textContent.trim());
        });
    });

    /* ---------------------------------------------------------------- *
     * Images: swap a broken project image for the icon placeholder
     * ---------------------------------------------------------------- */
    function makePlaceholder(icon) {
        const wrap = document.createElement('span');
        wrap.className = 'cover-placeholder';
        wrap.setAttribute('aria-hidden', 'true');
        const i = document.createElement('i');
        i.className = `bi bi-${icon || 'window'}`;
        wrap.appendChild(i);
        return wrap;
    }

    document.addEventListener('error', (e) => {
        const img = e.target;
        if (img.tagName === 'IMG' && img.dataset.fallbackIcon) {
            img.replaceWith(makePlaceholder(img.dataset.fallbackIcon));
        }
    }, true);

    /* ---------------------------------------------------------------- *
     * Project details modal — one modal, filled from JSON
     * ---------------------------------------------------------------- */
    const modalEl = $('#projectModal');
    let projects = [];
    try {
        projects = JSON.parse($('#portfolio-projects')?.textContent || '[]');
    } catch (err) {
        console.error('Could not parse project data', err);
    }

    const isSafeUrl = (url) => typeof url === 'string' && /^(https?:\/\/|\/)/i.test(url.trim());
    const hasText = (v) => typeof v === 'string' && v.trim() !== '';

    function fillList(listEl, items, className) {
        listEl.replaceChildren(...(items || []).filter(hasText).map((text) => {
            const li = document.createElement('li');
            if (className) li.className = className;
            li.textContent = text;
            return li;
        }));
        return listEl.children.length > 0;
    }

    if (modalEl) {
        const pm = (key) => $(`[data-pm="${key}"]`, modalEl);
        const catLabels = new Map(
            $$('[data-filter-group="projects"] .filter-btn').map((b) => [b.dataset.filter, b.textContent.trim()])
        );

        modalEl.addEventListener('show.bs.modal', (e) => {
            const trigger = e.relatedTarget;
            const project = trigger && projects[Number(trigger.dataset.project)];
            if (!project) { e.preventDefault(); return; }

            $('#projectModalTitle').textContent = project.title || 'Project';

            const cover = pm('cover');
            if (isSafeUrl(project.image)) {
                const img = document.createElement('img');
                img.src = project.image;
                img.alt = `Screenshot of ${project.title}`;
                img.dataset.fallbackIcon = project.icon || 'window';
                cover.replaceChildren(img);
            } else {
                cover.replaceChildren(makePlaceholder(project.icon));
            }

            pm('sample').hidden = !project.sample;
            fillList(pm('categories'), (project.categories || []).map((c) => catLabels.get(c) || c));
            pm('description').textContent = hasText(project.description) ? project.description : (project.summary || '');

            const problem = hasText(project.problem);
            const solution = hasText(project.solution);
            pm('problem').textContent = project.problem || '';
            pm('solution').textContent = project.solution || '';
            pm('problem-wrap').hidden = !problem;
            pm('solution-wrap').hidden = !solution;
            pm('ps-wrap').hidden = !problem && !solution;

            pm('features-wrap').hidden = !fillList(pm('features'), project.features);
            pm('stack-wrap').hidden = !fillList(pm('stack'), project.stack, 'chip');
            pm('challenges').textContent = project.challenges || '';
            pm('challenges-wrap').hidden = !hasText(project.challenges);

            let anyLink = false;
            [['demo', project.demo_url], ['source', project.source_url]].forEach(([key, url]) => {
                const a = pm(key);
                const ok = isSafeUrl(url);
                a.hidden = !ok;
                if (ok) { a.href = url; anyLink = true; } else { a.removeAttribute('href'); }
            });
            pm('nolinks').hidden = anyLink;
        });

        // Reset scroll so the next project opens at the top.
        modalEl.addEventListener('hidden.bs.modal', () => { $('.modal-body', modalEl).scrollTop = 0; });
    }

    /* ---------------------------------------------------------------- *
     * Hero designations: type, pause, delete, next
     * ---------------------------------------------------------------- */
    const typedEl = $('.hero__typed');
    let roles = [];
    try { roles = JSON.parse(typedEl?.dataset.roles || '[]').filter(hasText); } catch (_) { roles = []; }

    if (typedEl && roles.length > 1 && !reducedMotion.matches) {
        const TYPE_MS = 70, DELETE_MS = 35, HOLD_MS = 1800, GAP_MS = 350;
        let roleIndex = 0;
        let charIndex = roles[0].length;
        let deleting = true;

        const tick = () => {
            const word = roles[roleIndex];
            if (deleting) {
                charIndex -= 1;
                typedEl.textContent = word.slice(0, charIndex);
                if (charIndex === 0) {
                    deleting = false;
                    roleIndex = (roleIndex + 1) % roles.length;
                    return setTimeout(tick, GAP_MS);
                }
                return setTimeout(tick, DELETE_MS);
            }
            const next = roles[roleIndex];
            charIndex += 1;
            typedEl.textContent = next.slice(0, charIndex);
            if (charIndex === next.length) {
                deleting = true;
                return setTimeout(tick, HOLD_MS);
            }
            return setTimeout(tick, TYPE_MS);
        };
        // Start after the hero entrance animation, holding the first title.
        setTimeout(tick, HOLD_MS + 600);
    } else {
        $('.hero__caret')?.remove();
    }

    /* ---------------------------------------------------------------- *
     * Copy to clipboard
     * ---------------------------------------------------------------- */
    function legacyCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.cssText = 'position:fixed;top:-1000px;opacity:0';
        document.body.appendChild(ta);
        ta.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (_) { ok = false; }
        ta.remove();
        return ok;
    }

    async function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            try { await navigator.clipboard.writeText(text); return true; } catch (_) { /* fall through */ }
        }
        return legacyCopy(text);
    }

    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy]');
        if (!btn) return;
        const value = btn.dataset.copy;
        const label = btn.dataset.copyLabel || 'Value';
        const ok = await copyText(value);
        if (ok) {
            const icon = $('i', btn);
            btn.classList.add('is-copied');
            icon.className = 'bi bi-check2';
            showToast(`${label} copied to clipboard`);
            clearTimeout(btn._copyTimer);
            btn._copyTimer = setTimeout(() => {
                btn.classList.remove('is-copied');
                icon.className = 'bi bi-copy';
            }, 1800);
        } else {
            showToast(`Couldn't access the clipboard. ${label}: ${value}`, true);
        }
    });

    /* ---------------------------------------------------------------- *
     * Contact form
     * ---------------------------------------------------------------- */
    const form = $('#contactForm');
    if (!form) return;

    const card = form.closest('.form-card');
    const successEl = $('[data-form-success]', card);
    const alertEl = $('[data-form-alert]', form);
    const submitBtn = $('.btn-submit', form);
    const config = {
        mode: form.dataset.mode === 'production' ? 'production' : 'demo',
        endpoint: form.dataset.endpoint || '',
        successMs: Number(form.dataset.successMs) || 3000,
        timeoutMs: 15000,
    };
    const fields = ['name', 'email', 'subject', 'message'].map((n) => form.elements[n]);
    const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
    let busy = false;

    function fieldLabel(input) {
        return $(`label[for="${input.id}"]`, form)?.textContent.trim() || input.name;
    }

    function setFieldError(input, message) {
        const err = document.getElementById(`${input.id}-error`);
        if (err) err.textContent = message || '';
        if (message) input.setAttribute('aria-invalid', 'true');
        else input.removeAttribute('aria-invalid');
    }

    function validateField(input) {
        const value = input.value.trim();
        let message = '';
        if (!value) message = `Please enter your ${fieldLabel(input).toLowerCase()}.`;
        else if (input.type === 'email' && !EMAIL_RE.test(value)) message = 'Please enter a valid email address, like name@example.com.';
        else if (input.maxLength > 0 && value.length > input.maxLength) message = `Please keep this under ${input.maxLength} characters.`;
        setFieldError(input, message);
        return !message;
    }

    function setAlert(message) {
        alertEl.textContent = message || '';
        alertEl.hidden = !message;
    }

    function setBusy(on) {
        busy = on;
        submitBtn.disabled = on;
        submitBtn.classList.toggle('is-busy', on);
        form.setAttribute('aria-busy', String(on));
    }

    // Validate on blur once the user has typed; re-validate live after an error is shown.
    fields.forEach((input) => {
        input.addEventListener('blur', () => { if (input.value.trim() !== '' || input.hasAttribute('aria-invalid')) validateField(input); });
        input.addEventListener('input', () => { if (input.hasAttribute('aria-invalid')) validateField(input); });
    });

    async function sendToBackend(payload) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), config.timeoutMs);
        try {
            const res = await fetch(config.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
                signal: controller.signal,
            });
            let data = null;
            try { data = await res.json(); } catch (_) { data = null; }
            return { ok: res.ok, status: res.status, data };
        } finally {
            clearTimeout(timer);
        }
    }

    // Demo mode never contacts a server; it only simulates latency.
    const submitHandlers = {
        demo: () => new Promise((resolve) => setTimeout(() => resolve({ ok: true, status: 200, data: null }), 700)),
        production: sendToBackend,
    };

    function showSuccess() {
        successEl.hidden = false;
        card.classList.add('is-success');
        $('.form-success__inner', successEl).focus({ preventScroll: true });

        setTimeout(() => {
            form.reset();
            fields.forEach((f) => setFieldError(f, ''));
            card.classList.remove('is-success');
            successEl.hidden = true;
            setBusy(false);
            fields[0].focus({ preventScroll: true });
        }, config.successMs);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (busy) return;
        setAlert('');

        const valid = fields.map(validateField);
        if (valid.includes(false)) {
            fields[valid.indexOf(false)].focus();
            return;
        }

        const payload = Object.fromEntries(fields.map((f) => [f.name, f.value.trim()]));
        payload.website = form.elements.website?.value || '';

        setBusy(true);
        let result;
        try {
            result = await submitHandlers[config.mode](payload);
        } catch (err) {
            result = { ok: false, status: 0, data: null, aborted: err && err.name === 'AbortError' };
        }

        if (result.ok) {
            showSuccess();
            return;
        }

        setBusy(false);
        if (result.status === 422 && result.data && result.data.errors) {
            let first = null;
            Object.entries(result.data.errors).forEach(([name, messages]) => {
                const input = form.elements[name];
                if (input) {
                    setFieldError(input, Array.isArray(messages) ? messages[0] : String(messages));
                    first = first || input;
                }
            });
            setAlert('Please fix the highlighted fields and try again.');
            if (first) first.focus();
        } else if (result.status === 429) {
            setAlert('Too many messages in a short time. Please wait a minute and try again.');
        } else if (result.aborted) {
            setAlert('The server took too long to respond. Your message was not sent; please try again.');
        } else {
            setAlert('Your message could not be sent. Please try again, or reach me directly using the contact details on this page.');
        }
    });
})();
