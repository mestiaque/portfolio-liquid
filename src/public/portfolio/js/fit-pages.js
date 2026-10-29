/*
 * Fit to pages: an element with data-fit-pages="N" is measured at its printed width and, if it
 * would run past N pages, its font size (the CSS variable named in data-fit-var) is stepped down
 * until it fits, never below data-fit-min (pt). Used by /resume (1 page) and /cv (2 pages).
 * data-fit-paper-w / data-fit-paper-h / data-fit-margin give the page in mm.
 */
(() => {
    'use strict';

    const PX_PER_MM = 96 / 25.4;
    const PX_PER_PT = 96 / 72;
    // break-inside: avoid pushes whole items over a page break, so leave room at each break.
    const BREAK_SLACK = 0.08;

    function fit(el) {
        const d = el.dataset;
        const pages = Number(d.fitPages) || 1;
        const margin = Number(d.fitMargin) || 15;
        const width = ((Number(d.fitPaperW) || 210) - 2 * margin) * PX_PER_MM;
        const page = ((Number(d.fitPaperH) || 297) - 2 * margin) * PX_PER_MM;
        // Screen and print wrap lines slightly differently, so keep a 6% safety margin as well.
        const height = page * (pages - (pages - 1) * BREAK_SLACK) * 0.94;
        const prop = d.fitVar;
        const min = Number(d.fitMin) || 9;

        el.style.removeProperty(prop);
        let size = parseFloat(getComputedStyle(el).fontSize) / PX_PER_PT;

        // Measure at the printed width, whatever the screen is.
        const saved = el.style.cssText;
        el.style.width = width + 'px';
        el.style.maxWidth = 'none';
        while (el.scrollHeight > height && size > min) {
            size = Math.max(min, size - 0.25);
            el.style.setProperty(prop, size + 'pt');
        }
        const fitted = el.style.getPropertyValue(prop);
        el.style.cssText = saved;
        if (fitted) el.style.setProperty(prop, fitted);
    }

    const run = () => document.querySelectorAll('[data-fit-pages]').forEach(fit);
    (document.fonts ? document.fonts.ready : Promise.resolve()).then(run);
    window.addEventListener('beforeprint', run);
})();
