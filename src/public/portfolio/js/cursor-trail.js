/*
 * Cursor trails for the portfolio: circle, sparkle, ant, ladybug, butterfly,
 * firefly, kite, bubble, comet, fish, caterpillar, bee, petals, rain,
 * dragonfly, snow, confetti, boat, atom, lantern, fireworks, moon, leaf,
 * camel, flag, snowfall and balloon.
 * One full-screen canvas that never takes pointer events.
 *
 *   PortfolioCursor.start('butterfly', { color: '#2563eb', color2: '#0369a1' });
 *   PortfolioCursor.stop();
 *
 * The public page starts it from <body data-cursor="…">; the admin
 * Appearance page calls start() for the live preview. Skipped on touch
 * screens and for visitors who prefer reduced motion.
 */
(() => {
    'use strict';

    const TAU = Math.PI * 2;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

    let canvas = null, ctx = null, dpr = 1, W = 0, H = 0;
    let effect = null, raf = 0, last = 0, time = 0;
    // clicks: points clicked since the last frame (confetti and fireworks burst there).
    const mouse = { x: -100, y: -100, seen: false, inside: false, hover: false, moved: 0, clicks: [] };
    const palette = { a: [37, 99, 235], b: [3, 105, 161] };

    /* ---------------------------------------------------------------- *
     * Helpers
     * ---------------------------------------------------------------- */
    const rgba = ([r, g, b], a = 1) => `rgba(${r},${g},${b},${a})`;
    const lerp = (a, b, t) => a + (b - a) * t;
    const angleLerp = (a, b, t) => a + ((((b - a) % TAU) + TAU * 1.5) % TAU - Math.PI) * t;
    function parseColor(value, fallback) {
        const v = String(value || '').trim();
        let m = v.match(/^#?([0-9a-f]{6})$/i);
        if (m) return [0, 2, 4].map((i) => parseInt(m[1].slice(i, i + 2), 16));
        m = v.match(/^(\d+)\s*,\s*(\d+)\s*,\s*(\d+)$/) || v.match(/rgba?\((\d+)\D+(\d+)\D+(\d+)/);
        return m ? [+m[1], +m[2], +m[3]] : fallback;
    }
    const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name);

    function ensureCanvas() {
        if (canvas) return;
        canvas = document.createElement('canvas');
        canvas.setAttribute('aria-hidden', 'true');
        canvas.style.cssText = 'position:fixed;inset:0;width:100vw;height:100vh;pointer-events:none;z-index:2147483000';
        document.body.appendChild(canvas);
        ctx = canvas.getContext('2d');
        resize();
        window.addEventListener('resize', resize, { passive: true });
        window.addEventListener('pointermove', onMove, { passive: true });
        window.addEventListener('pointerdown', (e) => {
            if (e.pointerType === 'mouse' && mouse.clicks.length < 8) mouse.clicks.push({ x: e.clientX, y: e.clientY });
        }, { passive: true });
        document.addEventListener('pointerleave', () => { mouse.inside = false; });
        document.documentElement.addEventListener('mouseleave', () => { mouse.inside = false; });
    }

    function resize() {
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        W = window.innerWidth; H = window.innerHeight;
        canvas.width = Math.round(W * dpr); canvas.height = Math.round(H * dpr);
    }

    function onMove(e) {
        if (e.pointerType && e.pointerType !== 'mouse') return;
        if (!mouse.seen) { mouse.seen = true; effect?.reset?.(e.clientX, e.clientY); }
        mouse.moved += Math.hypot(e.clientX - mouse.x, e.clientY - mouse.y);
        mouse.x = e.clientX; mouse.y = e.clientY; mouse.inside = true;
        mouse.hover = !!e.target.closest?.('a, button, label, summary, input, select, textarea, [role="button"], .filter-btn');
    }

    function loop(now) {
        const dt = Math.min(0.05, last ? (now - last) / 1000 : 0.016);
        last = now; time += dt;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        ctx.clearRect(0, 0, W, H);
        if (effect && mouse.seen && !document.hidden) effect.frame(dt);
        mouse.moved = 0;
        mouse.clicks.length = 0;
        raf = requestAnimationFrame(loop);
    }

    /* ---------------------------------------------------------------- *
     * Effects. Each has frame(dt) and optional reset(x, y).
     * ---------------------------------------------------------------- */
    const effects = {
        // A dot on the pointer and a ring that catches up; the ring opens over links and buttons.
        circle() {
            const ring = { x: mouse.x, y: mouse.y, r: 16 };
            return {
                reset(x, y) { ring.x = x; ring.y = y; },
                frame() {
                    if (!mouse.inside) return;
                    ring.x = lerp(ring.x, mouse.x, 0.2); ring.y = lerp(ring.y, mouse.y, 0.2);
                    ring.r = lerp(ring.r, mouse.hover ? 26 : 16, 0.18);
                    ctx.beginPath(); ctx.arc(ring.x, ring.y, ring.r, 0, TAU);
                    ctx.lineWidth = 1.6; ctx.strokeStyle = rgba(palette.a, 0.75); ctx.stroke();
                    if (mouse.hover) { ctx.fillStyle = rgba(palette.a, 0.08); ctx.fill(); }
                    ctx.beginPath(); ctx.arc(mouse.x, mouse.y, mouse.hover ? 2.5 : 3.5, 0, TAU);
                    ctx.fillStyle = rgba(palette.a, 0.95); ctx.fill();
                },
            };
        },

        // Four-point stars shed while moving; they drift down, spin and fade.
        sparkle() {
            const bits = [];
            const gold = [250, 204, 21];
            const star = (x, y, r, rot) => {
                ctx.save(); ctx.translate(x, y); ctx.rotate(rot); ctx.beginPath();
                for (let i = 0; i < 8; i++) {
                    const rr = i % 2 ? r * 0.35 : r, a = (i / 8) * TAU;
                    ctx.lineTo(Math.cos(a) * rr, Math.sin(a) * rr);
                }
                ctx.closePath(); ctx.fill(); ctx.restore();
            };
            return {
                frame(dt) {
                    const n = Math.min(4, Math.floor(mouse.moved / 9));
                    for (let i = 0; i < n; i++) {
                        bits.push({ x: mouse.x, y: mouse.y, vx: (Math.random() - 0.5) * 60, vy: (Math.random() - 0.2) * 60,
                            life: 1, size: 3 + Math.random() * 5, rot: Math.random() * TAU, spin: (Math.random() - 0.5) * 6,
                            c: Math.random() < 0.35 ? gold : (Math.random() < 0.5 ? palette.a : palette.b) });
                    }
                    for (let i = bits.length - 1; i >= 0; i--) {
                        const p = bits[i];
                        p.life -= dt * 1.25;
                        if (p.life <= 0) { bits.splice(i, 1); continue; }
                        p.vy += 70 * dt; p.x += p.vx * dt; p.y += p.vy * dt; p.rot += p.spin * dt;
                        ctx.fillStyle = rgba(p.c, p.life);
                        star(p.x, p.y, p.size * (0.4 + p.life * 0.6), p.rot);
                    }
                },
            };
        },

        // Ants march single file along the path the pointer took.
        ant() {
            const path = [], COUNT = 7, GAP = 24;
            let walked = 0;
            const ink = [43, 27, 18];
            const drawAnt = (x, y, a, step) => {
                ctx.save(); ctx.translate(x, y); ctx.rotate(a);
                ctx.strokeStyle = rgba(ink, 0.85); ctx.lineWidth = 1; ctx.lineCap = 'round';
                for (let k = -1; k <= 1; k++) {
                    for (const side of [-1, 1]) {
                        const swing = Math.sin(step + (k + side) * 1.6) * 2.4 * side;
                        ctx.beginPath(); ctx.moveTo(k * 2.2, 0);
                        ctx.lineTo(k * 2.2 + swing, side * 3.5); ctx.lineTo(k * 3 + swing * 1.4, side * 6.5); ctx.stroke();
                    }
                }
                ctx.beginPath(); ctx.moveTo(6, -1); ctx.quadraticCurveTo(9, -4, 11, -5);
                ctx.moveTo(6, 1); ctx.quadraticCurveTo(9, 4, 11, 5); ctx.stroke();
                ctx.fillStyle = rgba(ink, 0.95);
                ctx.beginPath(); ctx.ellipse(-6, 0, 4.2, 3, 0, 0, TAU); ctx.fill();
                ctx.beginPath(); ctx.ellipse(0, 0, 2.6, 1.7, 0, 0, TAU); ctx.fill();
                ctx.beginPath(); ctx.arc(5, 0, 2.2, 0, TAU); ctx.fill();
                ctx.restore();
            };
            return {
                reset(x, y) { path.length = 0; path.push({ x, y }); },
                frame() {
                    const head = path[path.length - 1];
                    if (!head || Math.hypot(mouse.x - head.x, mouse.y - head.y) > 2) {
                        path.push({ x: mouse.x, y: mouse.y });
                        walked += mouse.moved;
                        if (path.length > 900) path.shift();
                    }
                    // Walk back along the path, placing an ant every GAP pixels.
                    let need = 18, acc = 0, placed = 0;
                    for (let i = path.length - 1; i > 0 && placed < COUNT; i--) {
                        const p = path[i], q = path[i - 1];
                        const seg = Math.hypot(p.x - q.x, p.y - q.y);
                        while (placed < COUNT && seg > 0 && acc + seg >= need) {
                            const t = (need - acc) / seg;
                            drawAnt(lerp(p.x, q.x, t), lerp(p.y, q.y, t), Math.atan2(p.y - q.y, p.x - q.x), walked * 0.25 + placed * 2);
                            placed += 1; need += GAP;
                        }
                        acc += seg;
                    }
                },
            };
        },

        // One ladybug crawls to the pointer, turning gradually, and rests beside it.
        ladybug() {
            const bug = { x: mouse.x, y: mouse.y, a: 0, v: 0, step: 0 };
            const red = [220, 38, 38], black = [17, 17, 17];
            return {
                reset(x, y) { bug.x = x + 60; bug.y = y + 60; },
                frame(dt) {
                    const dx = mouse.x - bug.x, dy = mouse.y - bug.y, d = Math.hypot(dx, dy);
                    const want = d > 34 ? Math.min(180, d * 2.2) : 0;
                    bug.v = lerp(bug.v, want, 0.08);
                    if (d > 4) bug.a = angleLerp(bug.a, Math.atan2(dy, dx), Math.min(1, dt * 5));
                    bug.x += Math.cos(bug.a) * bug.v * dt; bug.y += Math.sin(bug.a) * bug.v * dt;
                    bug.step += bug.v * dt * 0.35;
                    ctx.save(); ctx.translate(bug.x, bug.y); ctx.rotate(bug.a);
                    ctx.strokeStyle = rgba(black, 0.9); ctx.lineWidth = 1.3; ctx.lineCap = 'round';
                    for (let k = -1; k <= 1; k++) {
                        for (const side of [-1, 1]) {
                            const swing = Math.sin(bug.step + k * 2 + (side > 0 ? Math.PI : 0)) * 2.5;
                            ctx.beginPath(); ctx.moveTo(k * 4, side * 5); ctx.lineTo(k * 5 + swing, side * 11); ctx.stroke();
                        }
                    }
                    ctx.fillStyle = rgba(black); ctx.beginPath(); ctx.ellipse(9, 0, 4.5, 5.5, 0, 0, TAU); ctx.fill();
                    ctx.beginPath(); ctx.moveTo(12, -2); ctx.lineTo(17, -6); ctx.moveTo(12, 2); ctx.lineTo(17, 6); ctx.stroke();
                    const shell = ctx.createRadialGradient(-3, -4, 1, 0, 0, 12);
                    shell.addColorStop(0, '#f87171'); shell.addColorStop(1, rgba(red));
                    ctx.fillStyle = shell; ctx.beginPath(); ctx.ellipse(0, 0, 10.5, 9, 0, 0, TAU); ctx.fill();
                    ctx.strokeStyle = rgba(black, 0.85); ctx.lineWidth = 1.1;
                    ctx.beginPath(); ctx.moveTo(8, 0); ctx.lineTo(-10, 0); ctx.stroke();
                    ctx.fillStyle = rgba(black);
                    [[3, -4.5, 1.9], [3, 4.5, 1.9], [-4, -5, 1.7], [-4, 5, 1.7], [-7.5, -1.8, 1.3], [-7.5, 1.8, 1.3]].forEach(([x, y, r]) => {
                        ctx.beginPath(); ctx.arc(x, y, r, 0, TAU); ctx.fill();
                    });
                    ctx.fillStyle = 'rgba(255,255,255,.55)'; ctx.beginPath(); ctx.ellipse(-2, -5, 2.5, 1.2, -0.4, 0, TAU); ctx.fill();
                    ctx.restore();
                },
            };
        },

        // Two butterflies flutter after the pointer, circling it when it stops.
        butterfly() {
            const flock = [0, 1].map((i) => ({ x: mouse.x, y: mouse.y, vx: 0, vy: 0, phase: i * 2.3, orbit: 34 + i * 22, flap: Math.random() * TAU }));
            const wing = (dir, scale, colA, colB) => {
                ctx.save(); ctx.scale(dir * scale, 1);
                const g = ctx.createLinearGradient(0, -14, 14, 10);
                g.addColorStop(0, rgba(colA, 0.95)); g.addColorStop(1, rgba(colB, 0.9));
                ctx.fillStyle = g;
                ctx.beginPath(); ctx.moveTo(0, -1);
                ctx.bezierCurveTo(6, -18, 20, -16, 16, -4); ctx.bezierCurveTo(14, 0, 6, 1, 0, 0); ctx.fill();
                ctx.beginPath(); ctx.moveTo(0, 1);
                ctx.bezierCurveTo(10, 4, 14, 14, 6, 14); ctx.bezierCurveTo(2, 14, 0, 8, 0, 2); ctx.fill();
                ctx.fillStyle = 'rgba(255,255,255,.6)';
                ctx.beginPath(); ctx.arc(11, -9, 1.6, 0, TAU); ctx.fill();
                ctx.restore();
            };
            return {
                reset(x, y) { flock.forEach((b, i) => { b.x = x - 40 - i * 30; b.y = y + 30; }); },
                frame(dt) {
                    flock.forEach((b, i) => {
                        const tx = mouse.x + Math.cos(time * 1.3 + b.phase) * b.orbit;
                        const ty = mouse.y + Math.sin(time * 1.7 + b.phase) * b.orbit * 0.6 - 18;
                        b.vx = lerp(b.vx, (tx - b.x) * 2.4, 0.06); b.vy = lerp(b.vy, (ty - b.y) * 2.4, 0.06);
                        b.x += b.vx * dt; b.y += b.vy * dt + Math.sin(time * 9 + b.phase) * 0.6;
                        b.flap += dt * (14 + Math.min(10, Math.hypot(b.vx, b.vy) / 30));
                        const open = 0.2 + Math.abs(Math.sin(b.flap)) * 0.8;
                        ctx.save(); ctx.translate(b.x, b.y);
                        ctx.rotate(Math.max(-0.6, Math.min(0.6, b.vx / 400)));
                        ctx.scale(1.4, 1.4);
                        const cA = i ? palette.b : palette.a, cB = i ? [250, 204, 21] : [244, 114, 182];
                        wing(-1, open, cA, cB); wing(1, open, cA, cB);
                        ctx.fillStyle = 'rgba(30,20,20,.9)';
                        ctx.beginPath(); ctx.ellipse(0, 1, 1.6, 8, 0, 0, TAU); ctx.fill();
                        ctx.strokeStyle = 'rgba(30,20,20,.8)'; ctx.lineWidth = 0.8;
                        ctx.beginPath(); ctx.moveTo(0, -6); ctx.quadraticCurveTo(-2, -11, -4, -13);
                        ctx.moveTo(0, -6); ctx.quadraticCurveTo(2, -11, 4, -13); ctx.stroke();
                        ctx.restore();
                    });
                },
            };
        },

        // জোনাকি: a small swarm drifts around the pointer, each blinking on its own rhythm.
        firefly() {
            const glow = [190, 242, 100];
            const swarm = Array.from({ length: 16 }, () => ({
                x: mouse.x, y: mouse.y, vx: 0, vy: 0, wander: Math.random() * TAU,
                rate: 1.5 + Math.random() * 2.5, phase: Math.random() * TAU, reach: 30 + Math.random() * 70,
            }));
            return {
                reset(x, y) { swarm.forEach((f) => { f.x = x + (Math.random() - 0.5) * 200; f.y = y + (Math.random() - 0.5) * 200; }); },
                frame(dt) {
                    ctx.globalCompositeOperation = 'lighter';
                    swarm.forEach((f) => {
                        f.wander += (Math.random() - 0.5) * 3 * dt * 6;
                        const tx = mouse.x + Math.cos(f.wander) * f.reach, ty = mouse.y + Math.sin(f.wander) * f.reach;
                        f.vx = lerp(f.vx, (tx - f.x) * 1.2, 0.03); f.vy = lerp(f.vy, (ty - f.y) * 1.2, 0.03);
                        f.x += f.vx * dt; f.y += f.vy * dt;
                        const lit = Math.pow((Math.sin(time * f.rate + f.phase) + 1) / 2, 3);
                        const r = 10 + lit * 8;
                        const g = ctx.createRadialGradient(f.x, f.y, 0, f.x, f.y, r);
                        g.addColorStop(0, rgba(glow, 0.15 + lit * 0.85)); g.addColorStop(0.25, rgba(glow, 0.2 + lit * 0.35)); g.addColorStop(1, rgba(glow, 0));
                        ctx.fillStyle = g; ctx.beginPath(); ctx.arc(f.x, f.y, r, 0, TAU); ctx.fill();
                    });
                    ctx.globalCompositeOperation = 'source-over';
                    // On light pages a pure glow vanishes; a faint dark core keeps each fly visible.
                    swarm.forEach((f) => { ctx.fillStyle = 'rgba(60,70,20,.55)'; ctx.beginPath(); ctx.arc(f.x, f.y, 1.2, 0, TAU); ctx.fill(); });
                },
            };
        },

        // ঘুড়ি: the pointer holds the string; the kite floats up and behind with a ribboned tail.
        kite() {
            const kite = { x: mouse.x, y: mouse.y - 120, vx: 0, vy: 0, tilt: 0 };
            const tail = Array.from({ length: 12 }, () => ({ x: kite.x, y: kite.y, px: kite.x, py: kite.y }));
            const SEG = 9;
            return {
                reset(x, y) {
                    kite.x = x + 40; kite.y = y - 130;
                    tail.forEach((n, i) => { n.x = n.px = kite.x; n.y = n.py = kite.y + i * SEG; });
                },
                frame(dt) {
                    const wind = Math.sin(time * 0.9) * 22;
                    const tx = mouse.x + 50 + wind, ty = mouse.y - 130 + Math.sin(time * 1.6) * 10;
                    kite.vx += ((tx - kite.x) * 9 - kite.vx * 3.2) * dt; kite.vy += ((ty - kite.y) * 9 - kite.vy * 3.2) * dt;
                    kite.x += kite.vx * dt; kite.y += kite.vy * dt;
                    kite.tilt = lerp(kite.tilt, Math.max(-0.7, Math.min(0.7, kite.vx / 300 + wind / 120)), 0.08);

                    // String: sags a little between the hand and the kite.
                    ctx.strokeStyle = 'rgba(71,85,105,.55)'; ctx.lineWidth = 0.9;
                    ctx.beginPath(); ctx.moveTo(mouse.x, mouse.y);
                    ctx.quadraticCurveTo((mouse.x + kite.x) / 2 + 18, (mouse.y + kite.y) / 2 + 30, kite.x, kite.y + 4); ctx.stroke();

                    // Tail: verlet rope hanging from the kite's bottom corner.
                    const bx = kite.x - Math.sin(kite.tilt) * 26, by = kite.y + Math.cos(kite.tilt) * 26;
                    tail[0].x = bx; tail[0].y = by;
                    for (let i = 1; i < tail.length; i++) {
                        const n = tail[i], vx = (n.x - n.px) * 0.96, vy = (n.y - n.py) * 0.96;
                        n.px = n.x; n.py = n.y;
                        n.x += vx - wind * 0.012; n.y += vy + 0.35;
                    }
                    for (let pass = 0; pass < 3; pass++) {
                        for (let i = 1; i < tail.length; i++) {
                            const a = tail[i - 1], b = tail[i];
                            const dx = b.x - a.x, dy = b.y - a.y, d = Math.hypot(dx, dy) || 1, k = (d - SEG) / d;
                            b.x -= dx * k; b.y -= dy * k;
                        }
                    }
                    ctx.strokeStyle = rgba(palette.b, 0.7); ctx.lineWidth = 1.2;
                    ctx.beginPath(); tail.forEach((n, i) => (i ? ctx.lineTo(n.x, n.y) : ctx.moveTo(n.x, n.y))); ctx.stroke();
                    tail.forEach((n, i) => {
                        if (i % 3 !== 2) return;
                        ctx.fillStyle = rgba(i % 2 ? palette.a : [250, 204, 21], 0.9);
                        ctx.beginPath(); ctx.moveTo(n.x - 5, n.y - 3); ctx.lineTo(n.x, n.y); ctx.lineTo(n.x - 5, n.y + 3);
                        ctx.moveTo(n.x + 5, n.y - 3); ctx.lineTo(n.x, n.y); ctx.lineTo(n.x + 5, n.y + 3); ctx.fill();
                    });

                    // Kite: a diamond split in two colours, like a Bangladeshi ghuri.
                    ctx.save(); ctx.translate(kite.x, kite.y); ctx.rotate(kite.tilt);
                    const top = -24, bottom = 26, side = 19, mid = -4;
                    ctx.fillStyle = rgba(palette.a, 0.95);
                    ctx.beginPath(); ctx.moveTo(0, top); ctx.lineTo(side, mid); ctx.lineTo(0, bottom); ctx.closePath(); ctx.fill();
                    ctx.fillStyle = rgba(palette.b, 0.95);
                    ctx.beginPath(); ctx.moveTo(0, top); ctx.lineTo(-side, mid); ctx.lineTo(0, bottom); ctx.closePath(); ctx.fill();
                    ctx.fillStyle = 'rgba(255,255,255,.85)';
                    ctx.beginPath(); ctx.arc(0, mid, 4, 0, TAU); ctx.fill();
                    ctx.strokeStyle = 'rgba(30,41,59,.5)'; ctx.lineWidth = 1;
                    ctx.beginPath(); ctx.moveTo(0, top); ctx.lineTo(0, bottom); ctx.moveTo(-side, mid);
                    ctx.quadraticCurveTo(0, mid - 9, side, mid); ctx.stroke();
                    ctx.restore();
                },
            };
        },

        // Soap bubbles rise from the pointer, wobble, and pop into a ring.
        bubble() {
            const bubbles = [], pops = [];
            let idle = 0;
            return {
                frame(dt) {
                    idle += dt;
                    const n = Math.min(2, Math.floor(mouse.moved / 28)) + (idle > 0.5 ? 1 : 0);
                    if (idle > 0.5) idle = 0;
                    for (let i = 0; i < n && bubbles.length < 40; i++) {
                        bubbles.push({ x: mouse.x + (Math.random() - 0.5) * 10, y: mouse.y, r: 4 + Math.random() * 10,
                            vy: -(25 + Math.random() * 35), phase: Math.random() * TAU, life: 1.6 + Math.random() * 1.6 });
                    }
                    for (let i = bubbles.length - 1; i >= 0; i--) {
                        const b = bubbles[i];
                        b.life -= dt;
                        if (b.life <= 0) { pops.push({ x: b.x, y: b.y, r: b.r, t: 0 }); bubbles.splice(i, 1); continue; }
                        b.y += b.vy * dt; b.x += Math.sin(time * 3 + b.phase) * 0.4;
                        const g = ctx.createRadialGradient(b.x - b.r * 0.35, b.y - b.r * 0.35, b.r * 0.1, b.x, b.y, b.r);
                        g.addColorStop(0, 'rgba(255,255,255,.85)'); g.addColorStop(0.35, rgba(palette.a, 0.08));
                        g.addColorStop(0.85, rgba(palette.b, 0.18)); g.addColorStop(1, rgba(palette.a, 0.45));
                        ctx.fillStyle = g; ctx.beginPath(); ctx.arc(b.x, b.y, b.r, 0, TAU); ctx.fill();
                        ctx.strokeStyle = rgba(palette.a, 0.35); ctx.lineWidth = 0.8; ctx.stroke();
                    }
                    for (let i = pops.length - 1; i >= 0; i--) {
                        const p = pops[i];
                        p.t += dt * 4;
                        if (p.t >= 1) { pops.splice(i, 1); continue; }
                        ctx.strokeStyle = rgba(palette.a, 0.5 * (1 - p.t)); ctx.lineWidth = 1;
                        ctx.beginPath(); ctx.arc(p.x, p.y, p.r * (1 + p.t), 0, TAU); ctx.setLineDash([2, 4]); ctx.stroke(); ctx.setLineDash([]);
                    }
                },
            };
        },

        // ধূমকেতু: a glowing ribbon that tapers off behind the pointer.
        comet() {
            const pts = [];
            return {
                reset(x, y) { pts.length = 0; pts.push({ x, y, t: time }); },
                frame() {
                    pts.push({ x: mouse.x, y: mouse.y, t: time });
                    while (pts.length && time - pts[0].t > 0.6) pts.shift();
                    if (pts.length < 2) return;
                    ctx.lineCap = 'round';
                    ctx.shadowColor = rgba(palette.a, 0.8); ctx.shadowBlur = 12;
                    for (let i = 1; i < pts.length; i++) {
                        const k = i / pts.length;
                        ctx.strokeStyle = rgba(k > 0.5 ? palette.a : palette.b, k * 0.9);
                        ctx.lineWidth = 2 + k * 9;
                        ctx.beginPath(); ctx.moveTo(pts[i - 1].x, pts[i - 1].y); ctx.lineTo(pts[i].x, pts[i].y); ctx.stroke();
                    }
                    ctx.shadowBlur = 0;
                    ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.arc(mouse.x, mouse.y, 3, 0, TAU); ctx.fill();
                },
            };
        },

        // ইলিশ: a silver hilsa swims after the pointer; its body bends along a short spine.
        fish() {
            const SEG = 9, N = 9;
            const spine = Array.from({ length: N }, () => ({ x: mouse.x, y: mouse.y }));
            let heading = 0, speed = 0;
            const width = (i) => [7, 11, 14, 14.5, 14, 12, 9, 5.5, 3][i];
            return {
                reset(x, y) { spine.forEach((p, i) => { p.x = x - 80 - i * SEG; p.y = y + 40; }); },
                frame(dt) {
                    const head = spine[0];
                    const dx = mouse.x - head.x, dy = mouse.y - head.y, d = Math.hypot(dx, dy);
                    speed = lerp(speed, d > 30 ? Math.min(220, d * 2) : 25, 0.05);
                    heading = angleLerp(heading, Math.atan2(dy, dx), Math.min(1, dt * (d > 30 ? 4 : 1.5)));
                    const swim = Math.sin(time * (6 + speed / 40)) * 0.35;
                    head.x += Math.cos(heading + swim * 0.3) * speed * dt; head.y += Math.sin(heading + swim * 0.3) * speed * dt;
                    for (let i = 1; i < N; i++) {
                        const a = spine[i - 1], b = spine[i];
                        const ang = Math.atan2(b.y - a.y, b.x - a.x);
                        b.x = a.x + Math.cos(ang) * SEG; b.y = a.y + Math.sin(ang) * SEG;
                    }
                    // Outline: walk down one side of the spine and back up the other.
                    const left = [], right = [];
                    spine.forEach((p, i) => {
                        const q = spine[Math.min(N - 1, i + 1)], r = spine[Math.max(0, i - 1)];
                        const ang = Math.atan2(r.y - q.y, r.x - q.x) + Math.PI / 2;
                        left.push([p.x + Math.cos(ang) * width(i), p.y + Math.sin(ang) * width(i)]);
                        right.push([p.x - Math.cos(ang) * width(i), p.y - Math.sin(ang) * width(i)]);
                    });
                    const tail = spine[N - 1], pre = spine[N - 2];
                    const ta = Math.atan2(tail.y - pre.y, tail.x - pre.x);
                    ctx.fillStyle = rgba(palette.b, 0.75);
                    ctx.beginPath(); ctx.moveTo(tail.x, tail.y);
                    ctx.lineTo(tail.x + Math.cos(ta + 0.6) * 20, tail.y + Math.sin(ta + 0.6) * 20);
                    ctx.lineTo(tail.x + Math.cos(ta) * 11, tail.y + Math.sin(ta) * 11);
                    ctx.lineTo(tail.x + Math.cos(ta - 0.6) * 20, tail.y + Math.sin(ta - 0.6) * 20); ctx.closePath(); ctx.fill();
                    const g = ctx.createLinearGradient(left[3][0], left[3][1], right[3][0], right[3][1]);
                    g.addColorStop(0, '#64748b'); g.addColorStop(0.45, '#e2e8f0'); g.addColorStop(1, '#f8fafc');
                    ctx.fillStyle = g; ctx.strokeStyle = 'rgba(51,65,85,.45)'; ctx.lineWidth = 0.8;
                    ctx.beginPath(); ctx.moveTo(head.x + Math.cos(heading) * 9, head.y + Math.sin(heading) * 9);
                    left.forEach(([x, y]) => ctx.lineTo(x, y)); right.reverse().forEach(([x, y]) => ctx.lineTo(x, y));
                    ctx.closePath(); ctx.fill(); ctx.stroke();
                    // A violet sheen line and the eye.
                    ctx.strokeStyle = rgba(palette.a, 0.5); ctx.lineWidth = 1.2;
                    ctx.beginPath(); spine.slice(1, 7).forEach((p, i) => (i ? ctx.lineTo(p.x, p.y) : ctx.moveTo(p.x, p.y))); ctx.stroke();
                    const ex = head.x + Math.cos(heading + 0.9) * 4.5, ey = head.y + Math.sin(heading + 0.9) * 4.5;
                    ctx.fillStyle = '#0f172a'; ctx.beginPath(); ctx.arc(ex, ey, 2, 0, TAU); ctx.fill();
                },
            };
        },

        // শুঁয়োপোকা: a green caterpillar follows the pointer's path, inching in a wave.
        caterpillar() {
            const N = 11, GAP = 9;
            const body = Array.from({ length: N }, () => ({ x: mouse.x, y: mouse.y }));
            let crawl = 0;
            return {
                reset(x, y) { body.forEach((p, i) => { p.x = x - i * GAP; p.y = y + 30; }); },
                frame(dt) {
                    const head = body[0];
                    const dx = mouse.x - head.x, dy = mouse.y - head.y, d = Math.hypot(dx, dy);
                    const step = d > 26 ? Math.min(d - 26, 150 * dt) : 0;
                    if (step) { head.x += (dx / d) * step; head.y += (dy / d) * step; crawl += step * 0.18; }
                    for (let i = 1; i < N; i++) {
                        const a = body[i - 1], b = body[i];
                        const ang = Math.atan2(b.y - a.y, b.x - a.x);
                        b.x = a.x + Math.cos(ang) * GAP; b.y = a.y + Math.sin(ang) * GAP;
                    }
                    for (let i = N - 1; i >= 0; i--) {
                        const p = body[i], hump = Math.max(0, Math.sin(crawl - i * 0.7)) * 3;
                        const r = i === 0 ? 7 : 6.2 - i * 0.18;
                        ctx.strokeStyle = 'rgba(20,83,45,.8)'; ctx.lineWidth = 1;
                        ctx.beginPath(); ctx.moveTo(p.x - 2, p.y + r - 1); ctx.lineTo(p.x - 3, p.y + r + 3);
                        ctx.moveTo(p.x + 2, p.y + r - 1); ctx.lineTo(p.x + 3, p.y + r + 3); ctx.stroke();
                        const g = ctx.createRadialGradient(p.x - 2, p.y - hump - 2, 1, p.x, p.y - hump, r);
                        g.addColorStop(0, i === 0 ? '#bef264' : '#86efac'); g.addColorStop(1, i % 2 ? '#15803d' : '#16a34a');
                        ctx.fillStyle = g; ctx.beginPath(); ctx.arc(p.x, p.y - hump, r, 0, TAU); ctx.fill();
                        if (i && i % 2 === 0) { ctx.fillStyle = 'rgba(250,204,21,.9)'; ctx.beginPath(); ctx.arc(p.x, p.y - hump - r * 0.45, 1.3, 0, TAU); ctx.fill(); }
                    }
                    const h = body[0], n = body[1], a = Math.atan2(h.y - n.y, h.x - n.x);
                    ctx.save(); ctx.translate(h.x, h.y); ctx.rotate(a);
                    ctx.strokeStyle = 'rgba(20,83,45,.9)'; ctx.lineWidth = 1.1;
                    ctx.beginPath(); ctx.moveTo(4, -4); ctx.lineTo(10, -9); ctx.moveTo(4, 4); ctx.lineTo(10, 9); ctx.stroke();
                    ctx.fillStyle = '#111'; ctx.beginPath(); ctx.arc(3, -2.5, 1.3, 0, TAU); ctx.arc(3, 2.5, 1.3, 0, TAU); ctx.fill();
                    ctx.restore();
                },
            };
        },

        // মৌমাছি: a bee buzzes around the pointer, leaving a dashed flight path.
        bee() {
            const bee = { x: mouse.x, y: mouse.y, vx: 0, vy: 0, a: 0 };
            const path = [];
            return {
                reset(x, y) { bee.x = x + 80; bee.y = y - 40; },
                frame(dt) {
                    const tx = mouse.x + Math.cos(time * 2.2) * 38 + Math.sin(time * 5.3) * 10;
                    const ty = mouse.y + Math.sin(time * 3.1) * 26 + Math.cos(time * 7.1) * 6;
                    bee.vx = lerp(bee.vx, (tx - bee.x) * 4, 0.08); bee.vy = lerp(bee.vy, (ty - bee.y) * 4, 0.08);
                    bee.x += bee.vx * dt + (Math.random() - 0.5) * 0.8; bee.y += bee.vy * dt + (Math.random() - 0.5) * 0.8;
                    bee.a = angleLerp(bee.a, Math.atan2(bee.vy, bee.vx), 0.15);
                    path.push({ x: bee.x, y: bee.y });
                    if (path.length > 70) path.shift();
                    ctx.setLineDash([3, 5]); ctx.strokeStyle = rgba(palette.a, 0.35); ctx.lineWidth = 1.2;
                    ctx.beginPath(); path.forEach((p, i) => (i ? ctx.lineTo(p.x, p.y) : ctx.moveTo(p.x, p.y))); ctx.stroke();
                    ctx.setLineDash([]);
                    ctx.save(); ctx.translate(bee.x, bee.y); ctx.rotate(bee.a);
                    const flap = Math.sin(time * 70) * 0.5 + 0.5;
                    ctx.fillStyle = 'rgba(219,234,254,.7)'; ctx.strokeStyle = 'rgba(100,116,139,.6)'; ctx.lineWidth = 0.6;
                    [-1, 1].forEach((s) => {
                        ctx.beginPath(); ctx.ellipse(-1, s * (5 + flap * 2), 5, 3 + flap * 1.5, s * 0.5, 0, TAU); ctx.fill(); ctx.stroke();
                    });
                    ctx.fillStyle = '#facc15'; ctx.beginPath(); ctx.ellipse(0, 0, 8, 5, 0, 0, TAU); ctx.fill();
                    ctx.fillStyle = '#1c1917';
                    [-3.5, 0, 3.5].forEach((x) => { ctx.fillRect(x - 1, -4.6, 1.8, 9.2); });
                    ctx.beginPath(); ctx.arc(8, 0, 3, 0, TAU); ctx.fill();
                    ctx.beginPath(); ctx.moveTo(-8, 0); ctx.lineTo(-11, 0); ctx.strokeStyle = '#1c1917'; ctx.lineWidth = 1; ctx.stroke();
                    ctx.restore();
                },
            };
        },

        // কৃষ্ণচূড়া: red-orange petals drift down and sway as the pointer moves.
        petals() {
            const petals = [];
            const colours = [[220, 38, 38], [234, 88, 12], [239, 68, 68], [250, 204, 21]];
            return {
                frame(dt) {
                    const n = Math.min(2, Math.floor(mouse.moved / 16));
                    for (let i = 0; i < n && petals.length < 60; i++) {
                        petals.push({ x: mouse.x, y: mouse.y, vx: (Math.random() - 0.5) * 40, vy: 10 + Math.random() * 20,
                            rot: Math.random() * TAU, spin: (Math.random() - 0.5) * 4, sway: Math.random() * TAU,
                            size: 5 + Math.random() * 4, life: 2.2 + Math.random(), c: colours[(Math.random() * colours.length) | 0] });
                    }
                    for (let i = petals.length - 1; i >= 0; i--) {
                        const p = petals[i];
                        p.life -= dt;
                        if (p.life <= 0) { petals.splice(i, 1); continue; }
                        p.vy = Math.min(70, p.vy + 30 * dt);
                        p.x += (p.vx + Math.sin(time * 2 + p.sway) * 30) * dt; p.y += p.vy * dt; p.rot += p.spin * dt;
                        const fade = Math.min(1, p.life / 0.6);
                        ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rot);
                        ctx.scale(1, 0.55 + Math.abs(Math.sin(time * 3 + p.sway)) * 0.45);
                        ctx.fillStyle = rgba(p.c, 0.9 * fade);
                        ctx.beginPath(); ctx.moveTo(0, -p.size);
                        ctx.quadraticCurveTo(p.size, -p.size * 0.2, 0, p.size); ctx.quadraticCurveTo(-p.size, -p.size * 0.2, 0, -p.size); ctx.fill();
                        ctx.restore();
                    }
                },
            };
        },

        // বৃষ্টি: a small cloud rides above the pointer and rains; drops ripple where they land.
        rain() {
            const cloud = { x: mouse.x, y: mouse.y - 70 };
            const drops = [], ripples = [];
            const FALL = 110;
            return {
                reset(x, y) { cloud.x = x; cloud.y = y - 70; },
                frame(dt) {
                    cloud.x = lerp(cloud.x, mouse.x, 0.08); cloud.y = lerp(cloud.y, mouse.y - 70, 0.08);
                    if (Math.random() < 0.6) drops.push({ x: cloud.x + (Math.random() - 0.5) * 44, y: cloud.y + 8, vy: 160 + Math.random() * 80, floor: cloud.y + FALL });
                    for (let i = drops.length - 1; i >= 0; i--) {
                        const d = drops[i];
                        d.vy += 300 * dt; d.y += d.vy * dt;
                        if (d.y >= d.floor) { ripples.push({ x: d.x, y: d.floor, t: 0 }); drops.splice(i, 1); continue; }
                        ctx.strokeStyle = rgba(palette.b, 0.6); ctx.lineWidth = 1.3; ctx.lineCap = 'round';
                        ctx.beginPath(); ctx.moveTo(d.x, d.y - 7); ctx.lineTo(d.x, d.y); ctx.stroke();
                    }
                    for (let i = ripples.length - 1; i >= 0; i--) {
                        const r = ripples[i];
                        r.t += dt * 2.2;
                        if (r.t >= 1) { ripples.splice(i, 1); continue; }
                        ctx.strokeStyle = rgba(palette.b, 0.45 * (1 - r.t)); ctx.lineWidth = 1;
                        ctx.beginPath(); ctx.ellipse(r.x, r.y, 2 + r.t * 12, 1 + r.t * 3.5, 0, 0, TAU); ctx.stroke();
                    }
                    const g = ctx.createLinearGradient(0, cloud.y - 20, 0, cloud.y + 12);
                    g.addColorStop(0, 'rgba(248,250,252,.98)'); g.addColorStop(1, 'rgba(148,163,184,.95)');
                    ctx.fillStyle = g; ctx.shadowColor = 'rgba(15,23,42,.2)'; ctx.shadowBlur = 8;
                    ctx.beginPath();
                    [[-16, 2, 10], [-4, -6, 14], [12, -2, 11], [22, 4, 8], [2, 5, 12]].forEach(([x, y, r]) => {
                        ctx.moveTo(cloud.x + x + r, cloud.y + y); ctx.arc(cloud.x + x, cloud.y + y, r, 0, TAU);
                    });
                    ctx.fill(); ctx.shadowBlur = 0;
                },
            };
        },

        // ফড়িং: a dragonfly hovers near the pointer, then darts to a new spot.
        dragonfly() {
            const fly = { x: mouse.x, y: mouse.y, a: 0, tx: 0, ty: 0, wait: 0 };
            const body = [[20, 108, 120], [14, 184, 166]];
            return {
                reset(x, y) { fly.x = x - 70; fly.y = y - 50; },
                frame(dt) {
                    fly.wait -= dt;
                    if (fly.wait <= 0) {
                        const a = Math.random() * TAU, r = 35 + Math.random() * 45;
                        fly.tx = Math.cos(a) * r; fly.ty = Math.sin(a) * r * 0.7;
                        fly.wait = 0.5 + Math.random() * 0.9;
                    }
                    const tx = mouse.x + fly.tx, ty = mouse.y + fly.ty;
                    const px = fly.x, py = fly.y;
                    fly.x = lerp(fly.x, tx, 0.12); fly.y = lerp(fly.y, ty, 0.12);
                    if (Math.hypot(fly.x - px, fly.y - py) > 0.6) fly.a = angleLerp(fly.a, Math.atan2(fly.y - py, fly.x - px), 0.25);
                    const hover = Math.sin(time * 6) * 1.2;
                    ctx.save(); ctx.translate(fly.x, fly.y + hover); ctx.rotate(fly.a);
                    const flick = 0.75 + Math.sin(time * 60) * 0.25;
                    ctx.fillStyle = 'rgba(186,230,253,.45)'; ctx.strokeStyle = 'rgba(71,85,105,.45)'; ctx.lineWidth = 0.6;
                    [[4, 1], [-1, 0.8]].forEach(([x, len]) => {
                        [-1, 1].forEach((s) => {
                            ctx.beginPath(); ctx.ellipse(x, s * 9 * len * flick, 3, 9 * len * flick, s * 0.25, 0, TAU); ctx.fill(); ctx.stroke();
                        });
                    });
                    const g = ctx.createLinearGradient(-22, 0, 10, 0);
                    g.addColorStop(0, rgba(body[0])); g.addColorStop(1, rgba(body[1]));
                    ctx.fillStyle = g;
                    ctx.beginPath(); ctx.ellipse(-9, 0, 13, 1.6, 0, 0, TAU); ctx.fill();
                    ctx.beginPath(); ctx.ellipse(3, 0, 4.5, 3, 0, 0, TAU); ctx.fill();
                    ctx.fillStyle = '#0f172a'; ctx.beginPath(); ctx.arc(8, -1.8, 1.9, 0, TAU); ctx.arc(8, 1.8, 1.9, 0, TAU); ctx.fill();
                    ctx.restore();
                },
            };
        },

        // তুষার: six-armed snowflakes drift down and turn slowly.
        snow() {
            const flakes = [];
            let idle = 0;
            const drawFlake = (r) => {
                ctx.beginPath();
                for (let k = 0; k < 6; k++) {
                    const a = (k / 6) * TAU, cx = Math.cos(a), cy = Math.sin(a);
                    ctx.moveTo(0, 0); ctx.lineTo(cx * r, cy * r);
                    const bx = cx * r * 0.55, by = cy * r * 0.55, s = r * 0.3;
                    ctx.moveTo(bx, by); ctx.lineTo(bx + Math.cos(a + 0.7) * s, by + Math.sin(a + 0.7) * s);
                    ctx.moveTo(bx, by); ctx.lineTo(bx + Math.cos(a - 0.7) * s, by + Math.sin(a - 0.7) * s);
                }
                ctx.stroke();
            };
            return {
                frame(dt) {
                    idle += dt;
                    let n = Math.min(2, Math.floor(mouse.moved / 20));
                    if (idle > 0.35) { n += 1; idle = 0; }
                    for (let i = 0; i < n && flakes.length < 50; i++) {
                        flakes.push({ x: mouse.x + (Math.random() - 0.5) * 30, y: mouse.y, r: 3 + Math.random() * 5, vy: 18 + Math.random() * 25,
                            sway: Math.random() * TAU, rot: Math.random() * TAU, spin: (Math.random() - 0.5) * 1.5, life: 3 + Math.random() * 2 });
                    }
                    ctx.lineCap = 'round';
                    for (let i = flakes.length - 1; i >= 0; i--) {
                        const f = flakes[i];
                        f.life -= dt;
                        if (f.life <= 0) { flakes.splice(i, 1); continue; }
                        f.y += f.vy * dt; f.x += Math.sin(time * 1.5 + f.sway) * 0.5; f.rot += f.spin * dt;
                        const fade = Math.min(1, f.life / 0.8);
                        ctx.save(); ctx.translate(f.x, f.y); ctx.rotate(f.rot);
                        // A blue halo stroke keeps white flakes visible on light pages.
                        ctx.strokeStyle = `rgba(96,165,250,${0.55 * fade})`; ctx.lineWidth = 2.4; drawFlake(f.r);
                        ctx.strokeStyle = `rgba(255,255,255,${0.95 * fade})`; ctx.lineWidth = 1; drawFlake(f.r);
                        ctx.restore();
                    }
                },
            };
        },

        // কনফেটি: paper confetti trails the pointer; a click throws a whole handful.
        confetti() {
            const bits = [];
            const colours = [[239, 68, 68], [249, 115, 22], [234, 179, 8], [34, 197, 94], [14, 165, 233], [168, 85, 247], [236, 72, 153]];
            const throwAt = (x, y, n, power) => {
                for (let i = 0; i < n && bits.length < 220; i++) {
                    const a = -Math.PI / 2 + (Math.random() - 0.5) * 2.2, v = power * (0.4 + Math.random() * 0.6);
                    bits.push({ x, y, vx: Math.cos(a) * v, vy: Math.sin(a) * v, w: 4 + Math.random() * 4, h: 2.5 + Math.random() * 3,
                        rot: Math.random() * TAU, spin: (Math.random() - 0.5) * 12, flip: Math.random() * TAU, life: 2 + Math.random() * 1.5,
                        c: colours[(Math.random() * colours.length) | 0] });
                }
            };
            return {
                frame(dt) {
                    const n = Math.min(3, Math.floor(mouse.moved / 14));
                    if (n) throwAt(mouse.x, mouse.y, n, 90);
                    mouse.clicks.forEach((c) => throwAt(c.x, c.y, 60, 420));
                    for (let i = bits.length - 1; i >= 0; i--) {
                        const b = bits[i];
                        b.life -= dt;
                        if (b.life <= 0) { bits.splice(i, 1); continue; }
                        b.vy += 380 * dt; b.vx *= 0.985; b.vy = Math.min(b.vy, 140);
                        b.x += b.vx * dt + Math.sin(time * 4 + b.flip) * 0.4; b.y += b.vy * dt; b.rot += b.spin * dt;
                        ctx.save(); ctx.translate(b.x, b.y); ctx.rotate(b.rot);
                        ctx.scale(1, Math.cos(time * 8 + b.flip));
                        ctx.fillStyle = rgba(b.c, Math.min(1, b.life / 0.5));
                        ctx.fillRect(-b.w / 2, -b.h / 2, b.w, b.h);
                        ctx.restore();
                    }
                },
            };
        },

        // কাগজের নৌকা: a paper boat rides a little wave under the pointer, leaving a wake.
        boat() {
            const boat = { x: mouse.x, y: mouse.y, tilt: 0, vx: 0 };
            const wake = [];
            return {
                reset(x, y) { boat.x = x - 60; boat.y = y + 20; },
                frame(dt) {
                    const px = boat.x;
                    boat.x = lerp(boat.x, mouse.x, 0.05); boat.y = lerp(boat.y, mouse.y + 18, 0.08);
                    boat.vx = (boat.x - px) / Math.max(dt, 0.001);
                    const bob = Math.sin(time * 2.6) * 2.5;
                    boat.tilt = lerp(boat.tilt, Math.max(-0.35, Math.min(0.35, -boat.vx / 500)) + Math.sin(time * 2.1) * 0.06, 0.1);
                    if (Math.abs(boat.vx) > 15 && Math.random() < 0.35) wake.push({ x: boat.x - Math.sign(boat.vx) * 18, y: boat.y + 6, t: 0 });
                    // Water line.
                    ctx.strokeStyle = rgba(palette.b, 0.35); ctx.lineWidth = 1.2;
                    ctx.beginPath();
                    for (let x = -70; x <= 70; x += 4) {
                        const y = boat.y + 7 + Math.sin(time * 3 + x / 12) * 1.8;
                        x === -70 ? ctx.moveTo(boat.x + x, y) : ctx.lineTo(boat.x + x, y);
                    }
                    ctx.stroke();
                    for (let i = wake.length - 1; i >= 0; i--) {
                        const w = wake[i];
                        w.t += dt * 0.9;
                        if (w.t >= 1) { wake.splice(i, 1); continue; }
                        ctx.strokeStyle = rgba(palette.b, 0.4 * (1 - w.t)); ctx.lineWidth = 1;
                        ctx.beginPath(); ctx.ellipse(w.x, w.y, 4 + w.t * 18, 1 + w.t * 3, 0, 0, TAU); ctx.stroke();
                    }
                    ctx.save(); ctx.translate(boat.x, boat.y + bob); ctx.rotate(boat.tilt);
                    ctx.fillStyle = '#ffffff'; ctx.strokeStyle = rgba(palette.a, 0.8); ctx.lineWidth = 1.2; ctx.lineJoin = 'round';
                    ctx.beginPath(); ctx.moveTo(-24, -2); ctx.lineTo(24, -2); ctx.lineTo(15, 8); ctx.lineTo(-15, 8); ctx.closePath(); ctx.fill(); ctx.stroke();
                    ctx.fillStyle = '#f8fafc';
                    ctx.beginPath(); ctx.moveTo(-14, -2); ctx.lineTo(0, -24); ctx.lineTo(14, -2); ctx.closePath(); ctx.fill(); ctx.stroke();
                    ctx.beginPath(); ctx.moveTo(0, -24); ctx.lineTo(0, -2); ctx.moveTo(-24, -2); ctx.lineTo(-8, -12); ctx.moveTo(24, -2); ctx.lineTo(8, -12); ctx.stroke();
                    ctx.restore();
                },
            };
        },

        // Atom: three electrons circle the pointer on tilted orbits.
        atom() {
            const c = { x: mouse.x, y: mouse.y };
            return {
                reset(x, y) { c.x = x; c.y = y; },
                frame() {
                    c.x = lerp(c.x, mouse.x, 0.2); c.y = lerp(c.y, mouse.y, 0.2);
                    const R = mouse.hover ? 30 : 24;
                    [0, Math.PI / 3, (2 * Math.PI) / 3].forEach((tilt, k) => {
                        ctx.save(); ctx.translate(c.x, c.y); ctx.rotate(tilt + time * 0.3);
                        ctx.strokeStyle = rgba(k === 1 ? palette.b : palette.a, 0.35); ctx.lineWidth = 1;
                        ctx.beginPath(); ctx.ellipse(0, 0, R, R * 0.36, 0, 0, TAU); ctx.stroke();
                        const a = time * (3 + k * 0.7) + k * 2;
                        const ex = Math.cos(a) * R, ey = Math.sin(a) * R * 0.36;
                        ctx.fillStyle = rgba(k === 1 ? palette.b : palette.a, 0.95);
                        ctx.shadowColor = ctx.fillStyle; ctx.shadowBlur = 8;
                        ctx.beginPath(); ctx.arc(ex, ey, 2.6, 0, TAU); ctx.fill(); ctx.shadowBlur = 0;
                        ctx.restore();
                    });
                    ctx.fillStyle = rgba(palette.a); ctx.beginPath(); ctx.arc(c.x, c.y, 3.5, 0, TAU); ctx.fill();
                },
            };
        },

        // ফানুস: glowing sky lanterns lift off from the pointer and float away.
        lantern() {
            const lanterns = [];
            let travelled = 0;
            return {
                frame(dt) {
                    travelled += mouse.moved;
                    if (travelled > 70 && lanterns.length < 14) {
                        travelled = 0;
                        lanterns.push({ x: mouse.x, y: mouse.y, vy: -(22 + Math.random() * 18), sway: Math.random() * TAU,
                            s: 0.8 + Math.random() * 0.5, life: 4 + Math.random() * 2, flick: Math.random() * TAU });
                    }
                    for (let i = lanterns.length - 1; i >= 0; i--) {
                        const l = lanterns[i];
                        l.life -= dt;
                        if (l.life <= 0) { lanterns.splice(i, 1); continue; }
                        l.y += l.vy * dt; l.x += Math.sin(time * 0.9 + l.sway) * 0.35;
                        const fade = Math.min(1, l.life / 1.2), glow = 0.75 + Math.sin(time * 9 + l.flick) * 0.12;
                        ctx.save(); ctx.translate(l.x, l.y); ctx.scale(l.s, l.s); ctx.rotate(Math.sin(time * 1.3 + l.sway) * 0.08);
                        ctx.globalCompositeOperation = 'lighter';
                        const halo = ctx.createRadialGradient(0, 0, 2, 0, 0, 30);
                        halo.addColorStop(0, `rgba(251,191,36,${0.45 * glow * fade})`); halo.addColorStop(1, 'rgba(251,191,36,0)');
                        ctx.fillStyle = halo; ctx.beginPath(); ctx.arc(0, 0, 30, 0, TAU); ctx.fill();
                        ctx.globalCompositeOperation = 'source-over';
                        const g = ctx.createLinearGradient(0, -14, 0, 12);
                        g.addColorStop(0, `rgba(234,88,12,${0.95 * fade})`); g.addColorStop(1, `rgba(253,224,71,${0.95 * fade})`);
                        ctx.fillStyle = g;
                        ctx.beginPath(); ctx.moveTo(-8, -13); ctx.lineTo(8, -13); ctx.quadraticCurveTo(11, 0, 6, 11); ctx.lineTo(-6, 11); ctx.quadraticCurveTo(-11, 0, -8, -13); ctx.fill();
                        ctx.fillStyle = `rgba(255,255,255,${0.9 * glow * fade})`; ctx.beginPath(); ctx.ellipse(0, 9, 2.4, 3, 0, 0, TAU); ctx.fill();
                        ctx.restore();
                    }
                },
            };
        },

        // আতশবাজি: click anywhere and a rocket climbs from the bottom and bursts there;
        // a small spark marks the pointer.
        fireworks() {
            const sparks = [], rockets = [];
            const colours = [[250, 204, 21], [239, 68, 68], [34, 197, 94], [56, 189, 248], [244, 114, 182]];
            const burst = (x, y) => {
                const col = colours[(Math.random() * colours.length) | 0], alt = palette.a;
                for (let i = 0; i < 70; i++) {
                    const a = (i / 70) * TAU + Math.random() * 0.1, v = 120 + Math.random() * 160;
                    sparks.push({ x, y, px: x, py: y, vx: Math.cos(a) * v, vy: Math.sin(a) * v, life: 1 + Math.random() * 0.6, c: i % 3 ? col : alt });
                }
            };
            return {
                frame(dt) {
                    mouse.clicks.forEach((c) => rockets.push({ x: c.x + (Math.random() - 0.5) * 60, y: H + 10, tx: c.x, ty: c.y, trail: [] }));
                    for (let i = rockets.length - 1; i >= 0; i--) {
                        const r = rockets[i];
                        // Constant climb speed, so every rocket reaches its click in a fraction of a second.
                        const dx = r.tx - r.x, dy = r.ty - r.y, d = Math.hypot(dx, dy), step = 1100 * dt;
                        if (d <= step) { r.x = r.tx; r.y = r.ty; } else { r.x += (dx / d) * step; r.y += (dy / d) * step; }
                        r.trail.push({ x: r.x, y: r.y });
                        if (r.trail.length > 6) r.trail.shift();
                        ctx.strokeStyle = 'rgba(250,204,21,.8)'; ctx.lineWidth = 2; ctx.lineCap = 'round';
                        ctx.beginPath(); r.trail.forEach((p, j) => (j ? ctx.lineTo(p.x, p.y) : ctx.moveTo(p.x, p.y))); ctx.stroke();
                        if (r.x === r.tx && r.y === r.ty) { burst(r.tx, r.ty); rockets.splice(i, 1); }
                    }
                    ctx.lineCap = 'round';
                    for (let i = sparks.length - 1; i >= 0; i--) {
                        const s = sparks[i];
                        s.life -= dt;
                        if (s.life <= 0) { sparks.splice(i, 1); continue; }
                        s.px = s.x; s.py = s.y;
                        s.vx *= 0.97; s.vy = s.vy * 0.97 + 140 * dt;
                        s.x += s.vx * dt; s.y += s.vy * dt;
                        ctx.strokeStyle = rgba(s.c, Math.min(1, s.life)); ctx.lineWidth = 2;
                        ctx.beginPath(); ctx.moveTo(s.px, s.py); ctx.lineTo(s.x, s.y); ctx.stroke();
                    }
                    if (!mouse.inside) return;
                    const flick = 2 + Math.random() * 2;
                    ctx.fillStyle = 'rgba(250,204,21,.9)'; ctx.shadowColor = 'rgba(250,204,21,.9)'; ctx.shadowBlur = 10;
                    ctx.beginPath(); ctx.arc(mouse.x, mouse.y, flick, 0, TAU); ctx.fill(); ctx.shadowBlur = 0;
                },
            };
        },

        // চাঁদ: a golden crescent and star float beside the pointer, leaving twinkles.
        moon() {
            const S = 64;
            const sprite = makeSprite(S, S, (g) => {
                const grad = g.createLinearGradient(10, 10, 50, 54);
                grad.addColorStop(0, '#fef3c7'); grad.addColorStop(0.5, '#fbbf24'); grad.addColorStop(1, '#d97706');
                g.fillStyle = grad; g.beginPath(); g.arc(28, 32, 20, 0, TAU); g.fill();
                g.globalCompositeOperation = 'destination-out';
                g.beginPath(); g.arc(37, 26, 17, 0, TAU); g.fill();
                g.globalCompositeOperation = 'source-over';
                g.fillStyle = '#fde68a'; drawStar(g, 46, 30, 6, 2.6, 5);
            });
            const moon = { x: mouse.x, y: mouse.y };
            const twinkles = [];
            return {
                reset(x, y) { moon.x = x + 26; moon.y = y - 26; },
                frame(dt) {
                    moon.x = lerp(moon.x, mouse.x + 26, 0.1); moon.y = lerp(moon.y, mouse.y - 26 + Math.sin(time * 1.6) * 3, 0.1);
                    if (mouse.moved > 3 && Math.random() < 0.5) twinkles.push({ x: moon.x + (Math.random() - 0.5) * 50, y: moon.y + (Math.random() - 0.5) * 50, t: 0, r: 1.5 + Math.random() * 2.5 });
                    for (let i = twinkles.length - 1; i >= 0; i--) {
                        const s = twinkles[i];
                        s.t += dt * 1.4;
                        if (s.t >= 1) { twinkles.splice(i, 1); continue; }
                        ctx.fillStyle = `rgba(251,191,36,${Math.sin(s.t * Math.PI)})`;
                        drawStar(ctx, s.x, s.y, s.r * 2, s.r * 0.5, 4);
                    }
                    ctx.save(); ctx.translate(moon.x, moon.y); ctx.rotate(-0.35 + Math.sin(time * 0.8) * 0.08);
                    ctx.shadowColor = 'rgba(251,191,36,.7)'; ctx.shadowBlur = 16;
                    ctx.drawImage(sprite, -S / 2, -S / 2, S, S);
                    ctx.restore();
                },
            };
        },

        // পাতা: green and autumn leaves drift down, flipping as they fall.
        leaf() {
            const leaves = [];
            const colours = [[22, 163, 74], [101, 163, 13], [21, 128, 61], [202, 138, 4], [234, 88, 12]];
            return {
                frame(dt) {
                    const n = Math.min(2, Math.floor(mouse.moved / 20));
                    for (let i = 0; i < n && leaves.length < 45; i++) {
                        leaves.push({ x: mouse.x, y: mouse.y, vx: (Math.random() - 0.5) * 50, vy: 12 + Math.random() * 18, rot: Math.random() * TAU,
                            spin: (Math.random() - 0.5) * 3, sway: Math.random() * TAU, size: 7 + Math.random() * 5, life: 2.6 + Math.random(),
                            c: colours[(Math.random() * colours.length) | 0] });
                    }
                    for (let i = leaves.length - 1; i >= 0; i--) {
                        const l = leaves[i];
                        l.life -= dt;
                        if (l.life <= 0) { leaves.splice(i, 1); continue; }
                        l.vy = Math.min(60, l.vy + 25 * dt);
                        l.x += (l.vx + Math.sin(time * 1.8 + l.sway) * 40) * dt; l.y += l.vy * dt; l.rot += l.spin * dt;
                        const fade = Math.min(1, l.life / 0.6), s = l.size;
                        ctx.save(); ctx.translate(l.x, l.y); ctx.rotate(l.rot); ctx.scale(Math.cos(time * 2.4 + l.sway), 1);
                        ctx.fillStyle = rgba(l.c, 0.92 * fade);
                        ctx.beginPath(); ctx.moveTo(0, -s);
                        ctx.bezierCurveTo(s * 0.8, -s * 0.5, s * 0.7, s * 0.5, 0, s); ctx.bezierCurveTo(-s * 0.7, s * 0.5, -s * 0.8, -s * 0.5, 0, -s); ctx.fill();
                        ctx.strokeStyle = `rgba(255,255,255,${0.45 * fade})`; ctx.lineWidth = 0.8;
                        ctx.beginPath(); ctx.moveTo(0, -s * 0.85); ctx.lineTo(0, s * 1.25); ctx.stroke();
                        ctx.restore();
                    }
                },
            };
        },

        // উট: a camel plods along the ground beneath the pointer, kicking up dust.
        camel() {
            const camel = { x: mouse.x, y: mouse.y, dir: 1, v: 0, step: 0 };
            const dust = [];
            const sand = '#c58b4c', dark = '#9a6634';
            const leg = (x, phase) => {
                const swing = Math.sin(camel.step + phase) * 0.45 * Math.min(1, camel.v / 40);
                const kx = x + Math.sin(swing) * 10, ky = 6 + Math.cos(swing) * 10;
                ctx.beginPath(); ctx.moveTo(x, 4); ctx.lineTo(kx, ky);
                ctx.lineTo(kx + Math.sin(swing * 0.4) * 11, ky + 11); ctx.stroke();
            };
            return {
                reset(x, y) { camel.x = x - 90; camel.y = y + 46; },
                frame(dt) {
                    const tx = mouse.x - camel.dir * 34, ty = mouse.y + 46;
                    const dx = tx - camel.x;
                    const want = Math.abs(dx) > 8 ? Math.min(110, Math.abs(dx) * 1.6) : 0;
                    camel.v = lerp(camel.v, want, 0.06);
                    if (Math.abs(dx) > 20) camel.dir = dx > 0 ? 1 : -1;
                    camel.x += Math.sign(dx) * camel.v * dt; camel.y = lerp(camel.y, ty, 0.08);
                    camel.step += camel.v * dt * 0.11;
                    if (camel.v > 30 && Math.random() < 0.3) dust.push({ x: camel.x - camel.dir * 14, y: camel.y + 27, t: 0 });
                    for (let i = dust.length - 1; i >= 0; i--) {
                        const d = dust[i];
                        d.t += dt * 1.5;
                        if (d.t >= 1) { dust.splice(i, 1); continue; }
                        ctx.fillStyle = `rgba(197,139,76,${0.3 * (1 - d.t)})`;
                        ctx.beginPath(); ctx.arc(d.x - camel.dir * d.t * 10, d.y - d.t * 6, 2 + d.t * 6, 0, TAU); ctx.fill();
                    }
                    const bob = Math.abs(Math.sin(camel.step)) * 1.6 * Math.min(1, camel.v / 40);
                    ctx.save(); ctx.translate(camel.x, camel.y - bob); ctx.scale(camel.dir, 1);
                    ctx.lineCap = 'round'; ctx.lineJoin = 'round';
                    ctx.strokeStyle = dark; ctx.lineWidth = 3;
                    leg(-12, Math.PI); leg(10, 0);
                    ctx.strokeStyle = sand;
                    leg(-7, 0); leg(15, Math.PI);
                    ctx.fillStyle = sand;
                    ctx.beginPath(); ctx.ellipse(1, 0, 19, 9, 0, 0, TAU); ctx.fill();
                    ctx.beginPath(); ctx.ellipse(-2, -7, 10, 9, 0, Math.PI, TAU); ctx.fill();
                    ctx.strokeStyle = sand; ctx.lineWidth = 6;
                    ctx.beginPath(); ctx.moveTo(15, -2); ctx.quadraticCurveTo(24, -4, 27, -19); ctx.stroke();
                    ctx.beginPath(); ctx.ellipse(31, -21, 7, 4, 0.15, 0, TAU); ctx.fill();
                    ctx.fillStyle = dark; ctx.beginPath(); ctx.ellipse(27, -25, 1.6, 2.6, -0.3, 0, TAU); ctx.fill();
                    ctx.fillStyle = '#1f2937'; ctx.beginPath(); ctx.arc(31, -22.5, 1.1, 0, TAU); ctx.fill();
                    ctx.strokeStyle = dark; ctx.lineWidth = 1.5;
                    ctx.beginPath(); ctx.moveTo(-18, -1); ctx.quadraticCurveTo(-22, 3, -21, 9); ctx.stroke();
                    // A red saddle cloth with a gold edge.
                    ctx.fillStyle = 'rgba(220,38,38,.85)'; ctx.fillRect(-9, -3, 14, 7);
                    ctx.fillStyle = 'rgba(250,204,21,.9)'; ctx.fillRect(-9, 3, 14, 1.6);
                    ctx.restore();
                },
            };
        },

        // বাংলাদেশের পতাকা: the pointer holds a pole and the flag waves, streaming away from the motion.
        flag() {
            const FW = 60, FH = 36;
            const sprite = makeSprite(FW, FH, (g) => {
                g.fillStyle = '#006a4e'; g.fillRect(0, 0, FW, FH);
                g.fillStyle = '#f42a41'; g.beginPath(); g.arc(FW * 0.45, FH / 2, FW / 5, 0, TAU); g.fill();
            });
            const SLICES = 20, sw = FW / SLICES;
            let side = 1, vx = 0, px = mouse.x;
            return {
                reset(x) { px = x; },
                frame(dt) {
                    vx = lerp(vx, (mouse.x - px) / Math.max(dt, 0.001), 0.1); px = mouse.x;
                    if (Math.abs(vx) > 60) side = lerp(side, vx > 0 ? -1 : 1, 0.12);
                    const flap = 2.5 + Math.min(6, Math.abs(vx) / 80);
                    const poleX = mouse.x, top = mouse.y - 78;
                    ctx.strokeStyle = '#78716c'; ctx.lineWidth = 2.2; ctx.lineCap = 'round';
                    ctx.beginPath(); ctx.moveTo(poleX, mouse.y); ctx.lineTo(poleX, top - 3); ctx.stroke();
                    ctx.fillStyle = '#facc15'; ctx.beginPath(); ctx.arc(poleX, top - 4, 2.6, 0, TAU); ctx.fill();
                    ctx.save(); ctx.translate(poleX, top); ctx.scale(side, 1);
                    for (let i = 0; i < SLICES; i++) {
                        const k = i / SLICES, wave = Math.sin(i * 0.55 - time * 7) * flap * k;
                        const slope = Math.cos(i * 0.55 - time * 7) * k;
                        ctx.drawImage(sprite, i * (sprite.width / SLICES), 0, sprite.width / SLICES + 1, sprite.height, i * sw, wave, sw + 0.6, FH);
                        ctx.fillStyle = slope > 0 ? `rgba(255,255,255,${slope * 0.12})` : `rgba(0,0,0,${-slope * 0.18})`;
                        ctx.fillRect(i * sw, wave, sw + 0.6, FH);
                    }
                    ctx.restore();
                },
            };
        },

        // তুষারপাত: snow falls across the whole screen and parts around the pointer.
        snowfall() {
            let flakes = [];
            const fill = () => {
                const n = Math.min(160, Math.round((W * H) / 12000));
                flakes = Array.from({ length: n }, () => ({ x: Math.random() * W, y: Math.random() * H, r: 1.4 + Math.random() * 3,
                    vy: 20 + Math.random() * 35, phase: Math.random() * TAU, ox: 0, oy: 0 }));
            };
            fill();
            return {
                frame(dt) {
                    if (Math.abs(flakes.length - Math.min(160, Math.round((W * H) / 12000))) > 20) fill();
                    flakes.forEach((f) => {
                        f.y += f.vy * dt * (0.6 + f.r / 4); f.x += Math.sin(time * 0.8 + f.phase) * 0.35;
                        if (f.y > H + 5) { f.y = -5; f.x = Math.random() * W; }
                        if (f.x > W + 5) f.x = -5; else if (f.x < -5) f.x = W + 5;
                        // Push away from the pointer, then ease back.
                        const dx = f.x + f.ox - mouse.x, dy = f.y + f.oy - mouse.y, d = Math.hypot(dx, dy);
                        if (mouse.inside && d < 90 && d > 0) { const p = ((90 - d) / 90) * 140 * dt; f.ox += (dx / d) * p; f.oy += (dy / d) * p; }
                        f.ox *= 0.96; f.oy *= 0.96;
                        const x = f.x + f.ox, y = f.y + f.oy;
                        ctx.fillStyle = 'rgba(59,130,246,.45)'; ctx.beginPath(); ctx.arc(x, y, f.r + 1.3, 0, TAU); ctx.fill();
                        ctx.fillStyle = 'rgba(255,255,255,.95)'; ctx.beginPath(); ctx.arc(x, y, f.r, 0, TAU); ctx.fill();
                    });
                },
            };
        },

        // বেলুন: colourful balloons float up on wavy strings; click one to pop it.
        balloon() {
            const balloons = [], scraps = [];
            const colours = [[239, 68, 68], [249, 115, 22], [234, 179, 8], [34, 197, 94], [14, 165, 233], [168, 85, 247], [236, 72, 153]];
            let travelled = 0;
            const pop = (b) => {
                for (let i = 0; i < 14; i++) {
                    const a = Math.random() * TAU, v = 60 + Math.random() * 140;
                    scraps.push({ x: b.x, y: b.y, vx: Math.cos(a) * v, vy: Math.sin(a) * v, rot: Math.random() * TAU, life: 0.7, c: b.c });
                }
            };
            return {
                frame(dt) {
                    travelled += mouse.moved;
                    if (travelled > 90 && balloons.length < 12) {
                        travelled = 0;
                        balloons.push({ x: mouse.x, y: mouse.y, vy: -(40 + Math.random() * 35), sway: Math.random() * TAU,
                            r: 13 + Math.random() * 6, life: 5 + Math.random() * 2, c: colours[(Math.random() * colours.length) | 0] });
                    }
                    mouse.clicks.forEach((c) => {
                        for (let i = balloons.length - 1; i >= 0; i--) {
                            const b = balloons[i];
                            if (Math.hypot(c.x - b.x, c.y - b.y) < b.r * 1.3) { pop(b); balloons.splice(i, 1); break; }
                        }
                    });
                    for (let i = balloons.length - 1; i >= 0; i--) {
                        const b = balloons[i];
                        b.life -= dt;
                        if (b.life <= 0 || b.y < -60) { balloons.splice(i, 1); continue; }
                        b.y += b.vy * dt; b.x += Math.sin(time * 1.2 + b.sway) * 0.5;
                        const fade = Math.min(1, b.life / 0.8), tilt = Math.sin(time * 1.2 + b.sway) * 0.12;
                        ctx.save(); ctx.translate(b.x, b.y); ctx.rotate(tilt);
                        // String: a gentle S-curve hanging from the knot.
                        ctx.strokeStyle = `rgba(100,116,139,${0.7 * fade})`; ctx.lineWidth = 0.9;
                        const sy = b.r * 1.25;
                        ctx.beginPath(); ctx.moveTo(0, sy);
                        ctx.bezierCurveTo(Math.sin(time * 3 + b.sway) * 6, sy + 14, -Math.sin(time * 3 + b.sway) * 6, sy + 26, Math.sin(time * 2 + b.sway) * 3, sy + 40);
                        ctx.stroke();
                        const g = ctx.createRadialGradient(-b.r * 0.35, -b.r * 0.45, b.r * 0.1, 0, 0, b.r * 1.3);
                        g.addColorStop(0, `rgba(255,255,255,${0.9 * fade})`); g.addColorStop(0.25, rgba(b.c, 0.95 * fade)); g.addColorStop(1, rgba(b.c.map((v) => v * 0.7), fade));
                        ctx.fillStyle = g;
                        ctx.beginPath(); ctx.ellipse(0, 0, b.r, b.r * 1.18, 0, 0, TAU); ctx.fill();
                        ctx.fillStyle = rgba(b.c.map((v) => v * 0.75), fade);
                        ctx.beginPath(); ctx.moveTo(-3, sy - 1); ctx.lineTo(3, sy - 1); ctx.lineTo(0, sy - 5); ctx.closePath(); ctx.fill();
                        ctx.restore();
                    }
                    for (let i = scraps.length - 1; i >= 0; i--) {
                        const s = scraps[i];
                        s.life -= dt;
                        if (s.life <= 0) { scraps.splice(i, 1); continue; }
                        s.vy += 300 * dt; s.x += s.vx * dt; s.y += s.vy * dt; s.rot += 10 * dt;
                        ctx.save(); ctx.translate(s.x, s.y); ctx.rotate(s.rot);
                        ctx.fillStyle = rgba(s.c, s.life / 0.7); ctx.fillRect(-3, -1.5, 6, 3);
                        ctx.restore();
                    }
                },
            };
        },
    };

    // Small offscreen canvas drawn once (crescent, flag), at device pixel ratio.
    function makeSprite(w, h, draw) {
        const c = document.createElement('canvas');
        c.width = Math.round(w * dpr); c.height = Math.round(h * dpr);
        const g = c.getContext('2d');
        g.scale(dpr, dpr);
        draw(g);
        return c;
    }

    function drawStar(g, x, y, outer, inner, points) {
        g.beginPath();
        for (let i = 0; i < points * 2; i++) {
            const r = i % 2 ? inner : outer, a = (i / (points * 2)) * TAU - Math.PI / 2;
            g.lineTo(x + Math.cos(a) * r, y + Math.sin(a) * r);
        }
        g.closePath(); g.fill();
    }

    /* ---------------------------------------------------------------- *
     * Public API
     * ---------------------------------------------------------------- */
    function stop() {
        effect = null;
        if (raf) cancelAnimationFrame(raf);
        raf = 0; last = 0;
        if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function start(type, opts = {}) {
        stop();
        if (!effects[type] || reducedMotion.matches || !finePointer.matches) return false;
        palette.a = parseColor(opts.color || cssVar('--accent'), palette.a);
        palette.b = parseColor(opts.color2 || cssVar('--accent-2'), palette.b);
        ensureCanvas();
        effect = effects[type]();
        if (mouse.seen) effect.reset?.(mouse.x, mouse.y);
        raf = requestAnimationFrame(loop);
        return true;
    }

    window.PortfolioCursor = { start, stop, types: Object.keys(effects) };

    const boot = () => {
        const type = document.body?.dataset.cursor;
        if (type && type !== 'none') start(type);
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
