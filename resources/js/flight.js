const page = document.querySelector('.flight-page');
if (page) {
    const media = window.matchMedia('(prefers-reduced-motion: reduce)');
    const scene = page.querySelector('.flight-scene');
    const hero = page.querySelector('.flight-hero');
    const toggle = page.querySelector('[data-motion-toggle]');
    const motionPath = page.querySelector('.flight-path svg');
    let preference = null;
    try { preference = localStorage.getItem('aeris-motion'); } catch {}
    let paused = preference ? preference !== 'enabled' : media.matches;
    let scheduled = false;
    const dim = page.querySelector('.hero-dim');
    const lines = page.querySelectorAll('.hero-heading .hl');
    const fades = page.querySelectorAll('.hero-intro, .hero-caption, .hero-baseline, .hero-heading .flight-label, .hero-heading sup');
    // hero fica preso (sticky) enquanto "A visão" sobe por cima: ele recua, escurece e o título se abre
    const pin = () => { hero.style.top = `${Math.min(0, window.innerHeight - hero.offsetHeight)}px`; };
    const clearHero = () => {
        [hero, scene, dim, ...lines, ...fades].forEach((el) => { if (el) { el.style.transform = ''; el.style.opacity = ''; } });
        hero.style.borderRadius = '';
    };
    const render = () => {
        scheduled = false;
        if (!scene || paused) return;
        const span = Math.max(1, hero.offsetHeight + Math.min(0, window.innerHeight - hero.offsetHeight));
        const p = Math.min(Math.max(window.scrollY / span, 0), 1);
        const e = p * p * (3 - 2 * p);
        hero.style.transform = p ? `scale(${1 - e * 0.08})` : '';
        hero.style.borderRadius = p ? `${e * 32}px` : '';
        scene.style.transform = `translate3d(0, ${p * 70}px, 0) scale(${1 + p * 0.06})`;
        dim.style.opacity = String(e * 0.55);
        if (lines[0]) lines[0].style.transform = `translate3d(${-e * 9}vw, 0, 0)`;
        if (lines[1]) lines[1].style.transform = `translate3d(${e * 7}vw, 0, 0)`;
        lines.forEach((l) => { l.style.opacity = String(1 - e * 0.9); });
        fades.forEach((f) => { f.style.opacity = String(Math.max(0, 1 - p * 2.2)); });
    };
    // altímetro conta até FL410 enquanto o horizonte se abre
    const alt = page.querySelector('[data-alt]');
    if (alt && document.documentElement.classList.contains('js-intro')) {
        const goal = 41000;
        const start = performance.now() + 1300;
        alt.textContent = '0';
        const step = (now) => {
            if (paused) { alt.textContent = goal.toLocaleString('pt-BR'); return; }
            const k = Math.min(Math.max((now - start) / 1400, 0), 1);
            alt.textContent = Math.round(goal * (1 - Math.pow(1 - k, 3)) / 10 * 10).toLocaleString('pt-BR');
            if (k < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }
    const syncMotion = () => {
        page.classList.toggle('motion-paused', paused);
        page.classList.toggle('motion-enabled', !paused);
        toggle.setAttribute('aria-pressed', String(paused));
        toggle.textContent = paused ? 'Ativar movimento ↗' : 'Pausar movimento Ⅱ';
        if (paused) {
            clearHero();
            motionPath?.pauseAnimations?.();
        } else {
            motionPath?.unpauseAnimations?.();
            render();
        }
        document.documentElement.style.scrollBehavior = paused ? 'auto' : 'smooth';
    };
    toggle.addEventListener('click', () => {
        paused = !paused;
        preference = paused ? 'paused' : 'enabled';
        try { localStorage.setItem('aeris-motion', preference); } catch {}
        syncMotion();
    });
    media.addEventListener('change', () => {
        if (preference === null) { paused = media.matches; syncMotion(); }
    });
    window.addEventListener('scroll', () => {
        if (!paused && !scheduled) { scheduled = true; requestAnimationFrame(render); }
    }, { passive: true });
    pin();
    window.addEventListener('resize', () => { pin(); render(); });
    syncMotion();
    const vision = page.querySelector('.flight-vision');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => vision.classList.toggle('in-view', entry.isIntersecting));
        }, { threshold: .35 });
        observer.observe(vision);
    } else { vision.classList.add('in-view'); }
    const operationDetails = page.querySelectorAll('.operation-lines details');
    operationDetails.forEach(detail => detail.addEventListener('toggle', () => {
        if (detail.open) operationDetails.forEach(other => { if (other !== detail) other.open = false; });
    }));
}
