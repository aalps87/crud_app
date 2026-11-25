// Scroll choreography for the public landing page:
//  - orange ribbon in "A visão" drawn down the page as you scroll, plane at its tip revealing the text
//  - boarding-pass "gate" carousel in "O hangar" (pinned horizontal travel on desktop)
//  - clean section transitions (hangar unclips, rails draw, headings rise)
const page = document.querySelector('.flight-page');

if (page) {
    const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
    const ease = (t) => 1 - Math.pow(1 - t, 3);
    const still = () => page.classList.contains('motion-paused');
    let vh = window.innerHeight;
    let vw = window.innerWidth;

    /* ---------------- Route (A visão) ---------------- */
    // Seção alta (≈3 telas) que rola normalmente. A fita laranja desce pela página e vai
    // sendo desenhada conforme o scroll: a ponta acompanha uma linha fixa da tela, o
    // aviãozinho voa nela e os textos surgem quando ele passa por eles.
    const vision = page.querySelector('[data-route]');
    const route = vision && {
        stage: vision.querySelector('.vision-stage'),
        svg: vision.querySelector('.route'),
        fill: vision.querySelector('.route-fill'),
        plane: vision.querySelector('.route-plane'),
        blocks: [],
        table: [], // amostras [comprimento, avanço virtual, y máx.] para mapear scroll -> fita
        toV: (y) => y,
        length: 0,
        current: 0,
        target: 0,
        live: false,
        planeScale: 1,
        flying: false,
    };
    const TIP_LINE = 0.6; // a ponta da fita fica a 60% da altura da tela

    const splitWords = (root) => {
        const collected = [];
        const walk = (node) => {
            [...node.childNodes].forEach((child) => {
                if (child.nodeType === Node.TEXT_NODE) {
                    const frag = document.createDocumentFragment();
                    child.textContent.split(/(\s+)/).forEach((part) => {
                        if (!part) return;
                        if (/^\s+$/.test(part)) { frag.append(part); return; }
                        const span = document.createElement('span');
                        span.className = 'w';
                        span.textContent = part;
                        frag.append(span);
                        collected.push(span);
                    });
                    child.replaceWith(frag);
                } else if (child.nodeType === Node.ELEMENT_NODE && child.tagName !== 'BR') {
                    walk(child);
                }
            });
        };
        walk(root);
        return collected;
    };

    if (route) {
        vision.querySelectorAll('[data-words]').forEach((el) => {
            route.blocks.push({ el, words: splitWords(el).map((span) => ({ span, at: 0, lit: false })) });
        });
        vision.classList.add('has-route');
    }

    // Catmull-Rom centrípeta -> curvas cúbicas: passa suave por todos os pontos, sem bicos.
    const smoothPath = (pts) => {
        const f = (n) => n.toFixed(1);
        const dist = (p, q) => Math.sqrt(Math.hypot(q[0] - p[0], q[1] - p[1]));
        let d = `M${f(pts[0][0])} ${f(pts[0][1])}`;
        for (let i = 0; i < pts.length - 1; i += 1) {
            const p0 = pts[Math.max(0, i - 1)];
            const p1 = pts[i];
            const p2 = pts[i + 1];
            const p3 = pts[Math.min(pts.length - 1, i + 2)];
            const d1 = dist(p0, p1);
            const d2 = dist(p1, p2);
            const d3 = dist(p2, p3);
            const c1 = d1 < 1e-6 ? p1 : [0, 1].map((k) => (d1 * d1 * p2[k] - d2 * d2 * p0[k] + (2 * d1 * d1 + 3 * d1 * d2 + d2 * d2) * p1[k]) / (3 * d1 * (d1 + d2)));
            const c2 = d3 < 1e-6 ? p2 : [0, 1].map((k) => (d3 * d3 * p1[k] - d2 * d2 * p3[k] + (2 * d3 * d3 + 3 * d3 * d2 + d2 * d2) * p2[k]) / (3 * d3 * (d3 + d2)));
            d += ` C${f(c1[0])} ${f(c1[1])} ${f(c2[0])} ${f(c2[1])} ${f(p2[0])} ${f(p2[1])}`;
        }
        return d;
    };

    // Loop completo começando e terminando embaixo do centro; dir -1 = entra indo p/ direita.
    const loop = (cx, cy, r, dir) => {
        const pts = [];
        for (let k = 0; k <= 12; k += 1) {
            const a = ((90 + dir * k * 30) * Math.PI) / 180;
            pts.push([cx + r * Math.cos(a), cy + r * Math.sin(a)]);
        }
        return pts;
    };

    // Avanço virtual da fita: desce com o y, mas dentro dos loops (onde o y volta) continua
    // andando um pouco, para o avião fazer a volta enquanto a página rola.
    const virtualAt = (l) => {
        const t = route.table;
        if (!t.length) return 0;
        let lo = 0;
        let hi = t.length - 1;
        while (hi - lo > 1) { const m = (lo + hi) >> 1; if (t[m][0] <= l) lo = m; else hi = m; }
        return t[lo][1];
    };
    const lengthAt = (v) => {
        const t = route.table;
        if (!t.length || v <= t[0][1]) return 0;
        if (v >= t[t.length - 1][1]) return route.length;
        let lo = 0;
        let hi = t.length - 1;
        while (hi - lo > 1) { const m = (lo + hi) >> 1; if (t[m][1] <= v) lo = m; else hi = m; }
        const [l0, v0] = t[lo];
        const [l1, v1] = t[hi];
        return l0 + (l1 - l0) * (v1 > v0 ? (v - v0) / (v1 - v0) : 0);
    };

    const layoutRoute = () => {
        if (!route) return;
        route.live = !still();
        vision.classList.toggle('is-live', route.live);
        const w = route.stage.clientWidth;
        const h = route.stage.clientHeight;
        const narrow = w < 700;
        const r = Math.min(w * (narrow ? 0.19 : 0.11), 200);
        const pts = [
            [-w * 0.08, h * 0.06],
            [w * 0.24, h * 0.17],
            [w * 0.5, h * 0.16],
            ...loop(w * (narrow ? 0.66 : 0.7), h * 0.26, r, -1),
            [w * 0.9, h * 0.4],
            [w * 0.62, h * 0.52],
            [w * 0.3, h * 0.6],
            [w * (narrow ? 0.3 : 0.38), h * 0.76],
            [w * 0.5, h * 0.9],
            [w * 0.52, h + 40],
        ];
        const d = smoothPath(pts);
        route.svg.setAttribute('viewBox', `0 0 ${w} ${h}`);
        route.svg.style.setProperty('--rw', `${Math.max(16, Math.min(30, w * 0.02)).toFixed(1)}px`);
        route.fill.setAttribute('d', d);
        route.length = route.fill.getTotalLength();
        route.fill.style.strokeDasharray = `${route.length} ${route.length}`;
        route.planeScale = Math.max(1.5, Math.min(2.6, w / 560));

        // tabela [comprimento, avanço virtual, y máximo até ali]. O avanço segue a descida;
        // nos trechos em que a fita sobe (loops) ele anda um pouco pelo comprimento, para a
        // volta ser desenhada enquanto a página rola.
        const step = 6;
        const table = [];
        let prev = route.fill.getPointAtLength(0);
        let v = prev.y;
        let ymax = prev.y;
        table.push([0, v, ymax]);
        for (let l = step; l <= route.length + step; l += step) {
            const at = Math.min(l, route.length);
            const p = route.fill.getPointAtLength(at);
            const rise = Math.max(0, p.y - ymax);
            v += rise > 0 ? rise : Math.hypot(p.x - prev.x, p.y - prev.y) * 0.25;
            ymax = Math.max(ymax, p.y);
            table.push([at, v, ymax]);
            prev = p;
            if (at === route.length) break;
        }
        route.table = table;
        // converte a altura da linha da tela (y da seção) em avanço virtual
        const [, v0, y0] = table[0];
        const [, v1, y1] = table[table.length - 1];
        route.toV = (y) => v0 + (y - y0) * ((v1 - v0) / Math.max(1, y1 - y0));

        // cada palavra acende quando a ponta da fita chega na altura dela
        const firstAtY = (y) => {
            const row = table.find((t) => t[2] >= y);
            return row ? row[1] : v1;
        };
        const top = route.stage.getBoundingClientRect().top;
        let shared = null;
        route.blocks.forEach(({ el, words }) => {
            const box = el.getBoundingClientRect();
            // data-words="junto": aparece junto com o bloco anterior
            if (el.dataset.words !== 'junto' || !shared) {
                shared = { from: box.top - top - vh * 0.2, span: Math.max(box.height, vh * 0.15) };
            }
            words.forEach((word, i) => {
                word.at = firstAtY(shared.from + shared.span * (i / Math.max(1, words.length - 1)));
            });
        });
    };

    const drawRoute = () => {
        if (!route || !route.length) return false;
        if (!route.live) {
            route.current = route.length;
            route.target = route.length;
        } else {
            const rect = route.stage.getBoundingClientRect();
            route.target = lengthAt(route.toV(vh * TIP_LINE - rect.top));
            route.current += (route.target - route.current) * 0.16;
            if (Math.abs(route.target - route.current) < 0.5) route.current = route.target;
        }
        const l = route.current;
        const pr = l / route.length;
        route.fill.style.strokeDashoffset = String(route.length - l);

        const a = route.fill.getPointAtLength(Math.max(0, l - 3));
        const b = route.fill.getPointAtLength(Math.min(route.length, l + 3));
        const pt = route.fill.getPointAtLength(l);
        const angle = (Math.atan2(b.y - a.y, b.x - a.x) * 180) / Math.PI;
        route.plane.setAttribute('transform', `translate(${pt.x} ${pt.y}) rotate(${angle}) scale(${route.planeScale})`);
        route.plane.style.opacity = route.live && pr > 0.004 && pr < 0.995 ? '1' : '0';

        const flying = pr > 0.004;
        if (flying !== route.flying) { route.flying = flying; vision.classList.toggle('is-flying', flying); }
        const v = virtualAt(l);
        route.blocks.forEach(({ words }) => words.forEach((word) => {
            const lit = !route.live || v >= word.at;
            if (lit !== word.lit) { word.lit = lit; word.span.classList.toggle('is-lit', lit); }
        }));
        return route.current !== route.target;
    };

    /* ---------------- Gate carousel (O hangar) ---------------- */
    const gateEl = page.querySelector('[data-gate]');
    const gate = gateEl && {
        el: gateEl,
        sticky: gateEl.querySelector('.gate-sticky'),
        viewport: gateEl.querySelector('[data-gate-viewport]'),
        rail: gateEl.querySelector('[data-gate-rail]'),
        cards: [...gateEl.querySelectorAll('.pass')],
        bar: gateEl.querySelector('[data-gate-bar]'),
        counter: gateEl.querySelector('[data-gate-current]'),
        pinned: false,
        max: 0,
        top: 0,
        last: null,
        bank: 0,
        active: -1,
    };

    const layoutGate = () => {
        if (!gate) return;
        const wantPin = vw > 900 && !still() && gate.cards.length > 1;
        gate.el.classList.toggle('is-pinned', wantPin);
        gate.pinned = wantPin;
        gate.rail.style.transform = '';
        const first = gate.cards[0];
        const last = gate.cards[gate.cards.length - 1];
        gate.max = last.offsetLeft + last.offsetWidth / 2 - (first.offsetLeft + first.offsetWidth / 2);
        // Fixa o bloco centrado na tela, mas com a altura do próprio conteúdo:
        // o título da seção fica colado acima, sem faixa vazia antes do pin.
        gate.top = wantPin ? Math.max(16, (vh - gate.sticky.offsetHeight) / 2) : 0;
        gate.sticky.style.top = wantPin ? `${gate.top}px` : '';
        gate.el.style.height = wantPin ? `${gate.sticky.offsetHeight + gate.max}px` : '';
        gate.last = null;
    };

    const gateProgress = () => {
        if (gate.pinned) return clamp((gate.top - gate.el.getBoundingClientRect().top) / (gate.max || 1));
        const span = gate.viewport.scrollWidth - gate.viewport.clientWidth;
        return span > 0 ? clamp(gate.viewport.scrollLeft / span) : 0;
    };

    const drawGate = () => {
        if (!gate) return false;
        const p = gateProgress();
        if (gate.pinned) gate.rail.style.transform = `translate3d(${-p * gate.max}px,0,0)`;

        const travel = p * gate.max;
        if (gate.last !== null && !still()) {
            const velocity = travel - gate.last;
            gate.bank += (clamp(-velocity * 0.12, -3.5, 3.5) - gate.bank) * 0.2;
        }
        gate.bank *= 0.9;
        if (Math.abs(gate.bank) < 0.01 || still()) gate.bank = 0;
        gate.last = travel;

        const vp = gate.viewport.getBoundingClientRect();
        const center = vp.left + vp.width / 2;
        let nearest = 0;
        let best = Infinity;
        gate.cards.forEach((card, i) => {
            const r = card.getBoundingClientRect();
            const d = (r.left + r.width / 2 - center) / r.width;
            const ad = Math.min(Math.abs(d), 1.5);
            if (Math.abs(d) < best) { best = Math.abs(d); nearest = i; }
            card.style.setProperty('--s', (1 - ad * 0.06).toFixed(4));
            card.style.setProperty('--o', (1 - ad * 0.35).toFixed(4));
            // a foto desliza um pouco dentro da janela: paralaxe de quem olha pela janelinha
            card.style.setProperty('--px', `${(clamp(d, -1.5, 1.5) * -16).toFixed(1)}px`);
            card.style.setProperty('--bank', `${(gate.bank * (1 - ad * 0.4)).toFixed(3)}deg`);
            card.classList.toggle('is-gate', Math.abs(d) < 0.5);
        });

        if (gate.bar) gate.bar.style.transform = `scaleX(${gate.cards.length > 1 ? p : 1})`;
        if (nearest !== gate.active) {
            gate.active = nearest;
            if (gate.counter) {
                gate.counter.textContent = String(nearest + 1).padStart(2, '0');
                gate.counter.classList.remove('tick');
                void gate.counter.offsetWidth;
                gate.counter.classList.add('tick');
            }
        }
        return gate.bank !== 0;
    };

    if (gate) {
        const step = () => gate.max / Math.max(1, gate.cards.length - 1);
        const goTo = (i) => {
            if (gate.pinned) {
                const top = window.scrollY + gate.el.getBoundingClientRect().top - gate.top + i * step();
                window.scrollTo({ top, behavior: still() ? 'auto' : 'smooth' });
            } else {
                const card = gate.cards[i];
                gate.viewport.scrollTo({
                    left: card.offsetLeft + card.offsetWidth / 2 - gate.viewport.clientWidth / 2,
                    behavior: still() ? 'auto' : 'smooth',
                });
            }
        };
        gate.cards.forEach((card, i) => {
            card.addEventListener('focus', () => goTo(i));
            card.addEventListener('click', () => goTo(i));
            card.addEventListener('keydown', (event) => {
                const next = event.key === 'ArrowRight' ? i + 1 : event.key === 'ArrowLeft' ? i - 1 : null;
                if (next === null || !gate.cards[next]) return;
                event.preventDefault();
                gate.cards[next].focus({ preventScroll: true });
            });
        });
        gate.viewport.addEventListener('scroll', () => kick(), { passive: true });
    }

    /* ---------------- Section transitions ---------------- */
    const hangar = page.querySelector('.flight-hangar');
    const drawHangar = () => {
        if (!hangar) return;
        const top = hangar.getBoundingClientRect().top;
        if (still() || top <= 0) { hangar.style.clipPath = ''; return; }
        const t = ease(clamp(1 - top / vh));
        const x = (4 * (1 - t)).toFixed(3);
        const radius = (36 * (1 - t)).toFixed(2);
        hangar.style.clipPath = `inset(0 ${x}% 0 ${x}% round ${radius}px ${radius}px 0 0)`;
    };

    /* ---------------- A operação: waypoint ativo gira o instrumento ---------------- */
    const ops = page.querySelector('[data-ops]');
    const opsSteps = ops ? [...ops.querySelectorAll('.ops-step')] : [];
    const opsInstrument = ops && ops.querySelector('.ops-instrument');
    const opsHdg = ops && ops.querySelector('[data-ops-hdg]');
    const opsStep = ops && ops.querySelector('[data-ops-step]');
    let opsActive = 0;
    const drawOps = () => {
        if (!opsSteps.length) return;
        const r = ops.getBoundingClientRect();
        if (r.bottom < 0 || r.top > vh) return;
        let nearest = 0;
        let best = Infinity;
        opsSteps.forEach((step, i) => {
            const b = step.getBoundingClientRect();
            const d = Math.abs(b.top + Math.min(b.height, 260) / 2 - vh * 0.5);
            if (d < best) { best = d; nearest = i; }
        });
        if (nearest === opsActive) return;
        opsActive = nearest;
        opsSteps.forEach((step, i) => step.classList.toggle('is-active', i === nearest));
        const hdg = opsSteps[nearest].dataset.hdg;
        opsInstrument.style.setProperty('--hdg', String(Number(hdg)));
        opsHdg.textContent = hdg;
        opsStep.textContent = `0${nearest + 1} / 0${opsSteps.length}`;
    };

    page.classList.add('js-sections');
    const rails = page.querySelectorAll('main > section:not(.flight-hero) .section-rail');
    const risers = page.querySelectorAll('[data-rise]');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add(entry.target.hasAttribute('data-rise') ? 'is-in' : 'is-drawn');
                io.unobserve(entry.target);
            });
        }, { threshold: 0.2, rootMargin: '0px 0px -8% 0px' });
        rails.forEach((el) => io.observe(el));
        risers.forEach((el) => io.observe(el));
    } else {
        rails.forEach((el) => el.classList.add('is-drawn'));
        risers.forEach((el) => el.classList.add('is-in'));
    }

    /* ---------------- Ticker ---------------- */
    let frame = null;
    const tick = () => {
        frame = null;
        const moving = [drawRoute(), drawGate()].some(Boolean);
        drawHangar();
        drawOps();
        if (moving) kick();
    };
    function kick() {
        if (frame === null) frame = requestAnimationFrame(tick);
    }
    const relayout = () => {
        vh = window.innerHeight;
        vw = window.innerWidth;
        layoutGate();
        layoutRoute();
        kick();
    };

    window.addEventListener('scroll', kick, { passive: true });
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(relayout, 120);
    });
    window.addEventListener('load', relayout, { once: true });
    document.fonts?.ready.then(relayout);
    new MutationObserver(relayout).observe(page, { attributes: true, attributeFilter: ['class'] });
    relayout();
}
