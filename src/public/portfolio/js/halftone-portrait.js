/*
 * Halftone portrait dial — the <x-portfolio::halftone-portrait> component.
 * Redraws a photo as dots (bigger where it is brighter) inside a colour ring
 * with ticks, a sweep and counter-rotating arcs. The dots lean away from the
 * cursor and grow in from the centre the first time the dial is seen.
 * Standalone: no dependency on main.js or anime.js.
 */
(() => {
    'use strict';

    const RING = ['#7dff6a', '#ff4b4b', '#ffa828', '#ffd23f', '#2ee59d', '#00d4ff', '#4b8bff', '#ff8a5c'];
    const GRID = 48; // samples per side
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const rgbOf = (hex) => [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16)).join(',');

    function sample(img) {
        const c = document.createElement('canvas');
        c.width = c.height = GRID;
        const g = c.getContext('2d');
        // Centre-crop to a square so the face isn't stretched.
        const side = Math.min(img.naturalWidth, img.naturalHeight);
        g.drawImage(img, (img.naturalWidth - side) / 2, (img.naturalHeight - side) / 2, side, side, 0, 0, GRID, GRID);
        const px = g.getImageData(0, 0, GRID, GRID).data;
        const dots = [];
        for (let y = 0; y < GRID; y++) {
            for (let x = 0; x < GRID; x++) {
                const u = (x / (GRID - 1)) * 2 - 1, v = (y / (GRID - 1)) * 2 - 1;
                if (u * u + v * v > 1) continue;
                const i = (y * GRID + x) * 4;
                const lum = (px[i] * 0.3 + px[i + 1] * 0.59 + px[i + 2] * 0.11) / 255;
                if (lum > 0.08) dots.push({ u, v, lum, d: Math.hypot(u, v) });
            }
        }
        // Stretch to the full 0..1 range (with a slight lift in the mids) so features read clearly.
        const lo = Math.min(...dots.map((p) => p.lum)), hi = Math.max(...dots.map((p) => p.lum));
        dots.forEach((p) => { p.lum = Math.pow((p.lum - lo) / Math.max(0.01, hi - lo), 0.8); });
        return dots;
    }

    function mount(canvas) {
        const ctx = canvas.getContext('2d');
        const color = /^#[0-9a-f]{6}$/i.test(canvas.dataset.color || '') ? canvas.dataset.color : '#ff4b4b';
        const rgb = rgbOf(color);
        const withRing = canvas.dataset.ring !== '0';
        // Neutral strokes: pale on dark pages, slate on light ones.
        const light = canvas.dataset.tone === 'light';
        const ink = light ? '15,23,42' : '246,244,242';
        const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
        let dots = [], size = 0, dpr = 1, t = 0, grow = reducedMotion.matches ? 1 : 0, onScreen = false;

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
            const seg = (Math.PI * 2) / RING.length;
            RING.forEach((c, k) => {
                const lit = c.toLowerCase() === color.toLowerCase();
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
            const step = (inner * 2) / GRID;
            for (let i = 0; i < dots.length; i++) {
                const p = dots[i];
                // Grow outwards from the centre on first view.
                const g = Math.min(1, Math.max(0, grow * 1.6 - p.d * 0.6));
                if (g <= 0) continue;
                const dist = Math.hypot(p.u - pointer.x * 0.8, p.v - pointer.y * 0.8);
                const push = 1 + Math.exp(-dist * dist * 8) * 0.06;
                const r = step * (0.16 + p.lum * 0.44) * g;
                ctx.fillStyle = `rgba(${rgb},${(0.25 + p.lum * 0.75) * g})`;
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
            if (withRing) drawRing(R);
            const inner = withRing ? R * 0.6 : R;
            // Bright pixels make big dots, which reads as a negative on a light page,
            // so light pages get a dark disc behind the portrait.
            if (light) {
                ctx.beginPath();
                ctx.arc(0, 0, inner * 1.1, 0, Math.PI * 2);
                ctx.fillStyle = '#0f172a';
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
                draw();
            }
            requestAnimationFrame(loop);
        };

        canvas.addEventListener('pointermove', (e) => {
            const r = canvas.getBoundingClientRect();
            pointer.tx = ((e.clientX - r.left) / r.width) * 2 - 1;
            pointer.ty = ((e.clientY - r.top) / r.height) * 2 - 1;
        });
        canvas.addEventListener('pointerleave', () => { pointer.tx = 0; pointer.ty = 0; });
        new ResizeObserver(resize).observe(canvas);
        new IntersectionObserver(([e]) => { onScreen = e.isIntersecting; }).observe(canvas);
        resize();

        const img = new Image();
        img.onload = () => {
            try { dots = sample(img); } catch (err) {
                // A cross-origin image without CORS headers can't be read.
                console.warn('halftone-portrait: could not read image pixels', err);
            }
            if (reducedMotion.matches) { t = 1.2; draw(); }
        };
        img.src = canvas.dataset.src;
        if (!reducedMotion.matches) loop();
    }

    const start = () => document.querySelectorAll('canvas[data-halftone]').forEach(mount);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
