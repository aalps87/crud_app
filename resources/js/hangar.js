// AERIS · Hangar — comportamento do painel estilo iOS
const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)');
const haptic = (ms = 8) => { try { navigator.vibrate?.(ms); } catch { /* sem suporte */ } };
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

/* ------------------------------------------------------------------ */
/* Direção da transição entre páginas                                  */
/* ------------------------------------------------------------------ */
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey) return;
    try { sessionStorage.setItem('vt-dir', link.dataset.vt || 'forward'); } catch { /* storage bloqueado */ }
});
document.addEventListener('submit', () => {
    try { sessionStorage.setItem('vt-dir', 'fade'); } catch { /* storage bloqueado */ }
});

/* ------------------------------------------------------------------ */
/* Navbar: título pequeno aparece quando o título grande some          */
/* ------------------------------------------------------------------ */
const navbar = $('[data-navbar]');
const largeTitle = $('[data-large-title]');
if (navbar) {
    if (largeTitle && 'IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            navbar.classList.toggle('is-condensed', !entry.isIntersecting);
        }, { rootMargin: '-56px 0px 0px 0px', threshold: 0 }).observe(largeTitle.querySelector('h1') || largeTitle);
    } else {
        const onScroll = () => navbar.classList.toggle('is-condensed', scrollY > 8);
        addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }
}

/* ------------------------------------------------------------------ */
/* Action sheet                                                        */
/* ------------------------------------------------------------------ */
const sheet = $('[data-sheet]');
let sheetResolve = null;

function closeSheet(value = null) {
    if (!sheet?.open) return;
    const finish = () => {
        sheet.classList.remove('is-closing');
        sheet.close();
        const resolve = sheetResolve;
        sheetResolve = null;
        resolve?.(value);
    };
    if (reduceMotion.matches) return finish();
    sheet.classList.add('is-closing');
    setTimeout(finish, 210);
}

function openSheet({ title = '', message = '', actions = [], header = null }) {
    if (!sheet) return Promise.resolve(null);
    $('[data-sheet-title]', sheet).textContent = title;
    $('[data-sheet-message]', sheet).textContent = message;
    const head = $('.sheet-header', sheet);
    head.querySelector('.avatar')?.remove();
    if (header) head.prepend(header);
    const box = $('[data-sheet-actions]', sheet);
    box.replaceChildren(...actions.map((action) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `sheet-action pressable${action.destructive ? ' is-destructive' : ''}`;
        button.textContent = action.label;
        button.addEventListener('click', () => { haptic(action.destructive ? 14 : 8); closeSheet(action.value); });
        return button;
    }));
    sheet.showModal();
    haptic();
    return new Promise((resolve) => { sheetResolve = resolve; });
}

if (sheet) {
    $('[data-sheet-cancel]', sheet).addEventListener('click', () => closeSheet(null));
    sheet.addEventListener('cancel', (event) => { event.preventDefault(); closeSheet(null); });
    sheet.addEventListener('click', (event) => {
        if (!event.target.closest('.sheet-panel')) closeSheet(null);
    });
}

// Excluir: troca o confirm() do navegador por um action sheet
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!form.matches('[data-delete-form]') || form.dataset.confirmed) return;
    event.preventDefault();
    closeMenu();
    closeSwipes();
    const ok = await openSheet({
        title: form.dataset.deleteTitle || 'Excluir item?',
        message: form.dataset.deleteForm,
        actions: [{ label: 'Excluir', value: true, destructive: true }],
    });
    if (!ok) return;
    form.dataset.confirmed = '1';
    try { sessionStorage.setItem('vt-dir', 'fade'); } catch { /* storage bloqueado */ }
    form.submit();
}, true);

// Aba "Conta" no iPhone
$$('[data-account-sheet]').forEach((trigger) => trigger.addEventListener('click', async (event) => {
    const tab = event.currentTarget;
    const avatar = tab.querySelector('.avatar')?.cloneNode(true);
    if (avatar) avatar.className = 'avatar';
    const choice = await openSheet({
        title: tab.dataset.name,
        message: tab.dataset.role,
        header: avatar,
        actions: [{ label: 'Sair do sistema', value: 'logout', destructive: true }],
    });
    if (choice === 'logout') $('[data-logout-form]')?.submit();
}));

/* ------------------------------------------------------------------ */
/* Menu de contexto (botão ⋯)                                          */
/* ------------------------------------------------------------------ */
let openMenu = null;

function closeMenu() {
    if (!openMenu) return;
    const { menu, trigger, home } = openMenu;
    openMenu = null;
    trigger.setAttribute('aria-expanded', 'false');
    const done = () => { menu.hidden = true; menu.classList.remove('is-closing'); home.append(menu); };
    if (reduceMotion.matches) return done();
    menu.classList.add('is-closing');
    setTimeout(done, 150);
}

function showMenu(trigger) {
    const menu = document.getElementById(trigger.getAttribute('aria-controls'));
    if (!menu) return;
    const home = menu.parentElement;
    document.body.append(menu); // escapa de ancestrais com transform/overflow
    menu.hidden = false;
    const r = trigger.getBoundingClientRect();
    const w = menu.offsetWidth, h = menu.offsetHeight;
    const below = r.bottom + 6 + h < innerHeight - 90;
    const left = Math.max(12, Math.min(r.right - w, innerWidth - w - 12));
    menu.style.left = `${left}px`;
    menu.style.top = `${below ? r.bottom + 6 : Math.max(12, r.top - h - 6)}px`;
    menu.style.setProperty('--origin', `${r.right - left - r.width / 2}px ${below ? '0' : '100%'}`);
    trigger.setAttribute('aria-expanded', 'true');
    openMenu = { menu, trigger, home };
    haptic();
    menu.querySelector('.menu-item')?.focus({ preventScroll: true });
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-menu-trigger]');
    if (trigger) {
        event.preventDefault();
        const same = openMenu?.trigger === trigger;
        closeMenu();
        if (!same) showMenu(trigger);
        return;
    }
    if (openMenu && !event.target.closest('.menu')) closeMenu();
    else if (openMenu && event.target.closest('.menu-item')) setTimeout(closeMenu, 0);
});
document.addEventListener('keydown', (event) => {
    if (!openMenu) return;
    if (event.key === 'Escape') { const t = openMenu.trigger; closeMenu(); t.focus(); }
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        const items = $$('.menu-item', openMenu.menu);
        const i = items.indexOf(document.activeElement);
        items[(i + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length]?.focus();
    }
});
addEventListener('scroll', closeMenu, { passive: true });
addEventListener('resize', closeMenu);

/* ------------------------------------------------------------------ */
/* Deslizar linha para revelar ações (toque)                           */
/* ------------------------------------------------------------------ */
let openSwipe = null;

function setSwipe(row, x) {
    $('.row-front', row).style.transform = x ? `translateX(${x}px)` : '';
}
function closeSwipes(except) {
    if (openSwipe && openSwipe !== except) { setSwipe(openSwipe, 0); openSwipe.classList.remove('is-open'); openSwipe = null; }
}

$$('[data-swipe]').forEach((row) => {
    const front = $('.row-front', row);
    const actions = $('.swipe-actions', row);
    if (!front || !actions) return;
    let startX = 0, startY = 0, dx = 0, dragging = false, decided = false, base = 0, pointerId = null;

    front.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse') return;
        pointerId = event.pointerId;
        startX = event.clientX; startY = event.clientY;
        base = row.classList.contains('is-open') ? -actions.offsetWidth : 0;
        dx = 0; dragging = false; decided = false;
    });
    front.addEventListener('pointermove', (event) => {
        if (event.pointerId !== pointerId) return;
        const mx = event.clientX - startX, my = event.clientY - startY;
        if (!decided) {
            if (Math.abs(mx) < 8 && Math.abs(my) < 8) return;
            decided = true;
            dragging = Math.abs(mx) > Math.abs(my);
            if (dragging) { closeSwipes(row); row.classList.add('is-dragging'); front.setPointerCapture(pointerId); }
        }
        if (!dragging) return;
        const max = actions.offsetWidth;
        let x = base + mx;
        if (x > 0) x = x / 4; // resistência elástica
        if (x < -max) x = -max - (-max - x) / 4;
        dx = x;
        setSwipe(row, x);
    });
    const end = (event) => {
        if (event.pointerId !== pointerId) return;
        pointerId = null;
        if (!dragging) return;
        row.classList.remove('is-dragging');
        const open = dx < -actions.offsetWidth / 2.5;
        row.classList.toggle('is-open', open);
        setSwipe(row, open ? -actions.offsetWidth : 0);
        if (open) { openSwipe = row; haptic(); } else if (openSwipe === row) openSwipe = null;
        row.dataset.justSwiped = '1';
        setTimeout(() => delete row.dataset.justSwiped, 50);
    };
    front.addEventListener('pointerup', end);
    front.addEventListener('pointercancel', end);
    front.addEventListener('click', (event) => {
        if (row.dataset.justSwiped || row.classList.contains('is-open')) {
            event.preventDefault();
            event.stopPropagation();
            if (!row.dataset.justSwiped) closeSwipes();
        }
    }, true);
});
document.addEventListener('pointerdown', (event) => {
    if (openSwipe && !openSwipe.contains(event.target)) closeSwipes();
});

/* ------------------------------------------------------------------ */
/* Banner de sucesso                                                   */
/* ------------------------------------------------------------------ */
$$('[data-banner]').forEach((banner) => {
    let timer, startY = 0, dy = 0, active = false;
    const dismiss = () => {
        clearTimeout(timer);
        banner.classList.remove('is-dragging');
        banner.style.transform = '';
        banner.classList.add('is-leaving');
        setTimeout(() => banner.remove(), 400);
    };
    const arm = () => { clearTimeout(timer); timer = setTimeout(dismiss, 3600); };
    arm();
    haptic(10);
    banner.addEventListener('pointerdown', (event) => {
        active = true; startY = event.clientY; dy = 0;
        banner.setPointerCapture(event.pointerId);
        banner.classList.add('is-dragging');
        clearTimeout(timer);
    });
    banner.addEventListener('pointermove', (event) => {
        if (!active) return;
        dy = event.clientY - startY;
        banner.style.transform = `translateY(${dy < 0 ? dy : dy / 5}px)`;
    });
    const up = () => {
        if (!active) return;
        active = false;
        banner.classList.remove('is-dragging');
        if (dy < -24) dismiss(); else { banner.style.transform = ''; arm(); }
    };
    banner.addEventListener('pointerup', up);
    banner.addEventListener('pointercancel', up);
    banner.addEventListener('mouseenter', () => clearTimeout(timer));
    banner.addEventListener('mouseleave', () => { if (!banner.classList.contains('is-leaving')) arm(); });
});

/* ------------------------------------------------------------------ */
/* Formulários                                                         */
/* ------------------------------------------------------------------ */
// Inteiro positivo
$$('[data-positive-integer]').forEach((input) => {
    input.addEventListener('beforeinput', (event) => {
        if (event.data && /\D/.test(event.data)) event.preventDefault();
    });
    input.addEventListener('input', () => {
        if (input.value !== '' && !/^[1-9]\d*$/.test(input.value)) input.value = '';
        syncStepper(input.closest('[data-stepper]'));
    });
});

// Stepper (1–1000)
function syncStepper(stepper) {
    if (!stepper) return;
    const input = $('input', stepper);
    const v = parseInt(input.value, 10) || 0;
    $('[data-step="-1"]', stepper).disabled = v <= 1;
    $('[data-step="1"]', stepper).disabled = v >= 1000;
}
$$('[data-stepper]').forEach((stepper) => {
    const input = $('input', stepper);
    $$('[data-step]', stepper).forEach((button) => {
        button.addEventListener('click', () => {
            const v = parseInt(input.value, 10) || 0;
            const next = Math.min(1000, Math.max(1, v + Number(button.dataset.step)));
            if (next === v) return;
            input.value = String(next);
            input.classList.remove('is-bumped');
            void input.offsetWidth;
            input.classList.add('is-bumped');
            haptic(5);
            syncStepper(stepper);
        });
    });
    syncStepper(stepper);
});

// Maiúsculas no prefixo
$$('[data-uppercase]').forEach((input) => {
    input.addEventListener('input', () => {
        const { selectionStart: s, selectionEnd: e } = input;
        input.value = input.value.toUpperCase();
        input.setSelectionRange(s, e);
    });
});

// Foto: prévia, arrastar e soltar
$$('[data-image-upload]').forEach((well) => {
    const input = $('[data-image-input]', well);
    const preview = $('[data-image-preview]', well);
    const drop = $('[data-photo-drop]', well);
    const fileName = $('[data-file-name]', well);
    const fileStatus = $('[data-file-status]', well);
    const buttonText = $('label.button span', well);
    let url = null;

    const show = (file) => {
        if (!file) return;
        if (url) URL.revokeObjectURL(url);
        url = URL.createObjectURL(file);
        preview.src = url;
        preview.alt = `Preview de ${file.name}`;
        preview.hidden = false;
        // reinicia a animação de entrada
        preview.style.animation = 'none'; void preview.offsetWidth; preview.style.animation = '';
        drop?.classList.add('has-image');
        fileName.textContent = file.name;
        fileStatus.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB · pronta para salvar`;
        if (buttonText) buttonText.textContent = 'Substituir imagem';
        haptic();
    };
    input.addEventListener('change', () => show(input.files?.[0]));

    if (drop) {
        ['dragenter', 'dragover'].forEach((type) => drop.addEventListener(type, (event) => {
            event.preventDefault(); drop.classList.add('is-dragover');
        }));
        ['dragleave', 'drop'].forEach((type) => drop.addEventListener(type, () => drop.classList.remove('is-dragover')));
        drop.addEventListener('drop', (event) => {
            event.preventDefault();
            const file = event.dataTransfer?.files?.[0];
            if (!file || !/^image\/(jpeg|png)$/.test(file.type)) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            input.files = dt.files;
            show(file);
        });
    }
    addEventListener('beforeunload', () => { if (url) URL.revokeObjectURL(url); });
});

// Switches: toque leve
document.addEventListener('change', (event) => {
    if (event.target.matches?.('.switch')) haptic(6);
});

// Envio: trava o botão e mostra estado de carregamento
$$('[data-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const buttons = [...form.querySelectorAll('[data-submit]'), ...$$(`[form="${form.id}"]`)];
        requestAnimationFrame(() => buttons.forEach((b) => { b.setAttribute('aria-busy', 'true'); b.disabled = true; }));
    });
});

/* ------------------------------------------------------------------ */
/* Busca ao vivo (usuários, papéis)                                    */
/* ------------------------------------------------------------------ */
const normalize = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
$$('[data-live-search]').forEach((input) => {
    const sel = input.dataset.liveSearch;
    const list = $(sel);
    const empty = $(`[data-live-empty="${sel}"]`);
    const clear = input.parentElement.querySelector('.search-clear');
    if (!list) return;
    const rows = $$('li[data-search-text]', list);
    rows.forEach((row) => { row.dataset.searchNorm = normalize(row.dataset.searchText); });
    const run = () => {
        const q = normalize(input.value);
        let shown = 0;
        rows.forEach((row) => {
            const hit = !q || row.dataset.searchNorm.includes(q);
            row.hidden = !hit;
            if (hit) row.style.setProperty('--i', shown++);
        });
        list.hidden = shown === 0;
        if (empty) empty.hidden = shown !== 0;
        if (clear) clear.hidden = !q;
    };
    input.addEventListener('input', run);
    input.addEventListener('keydown', (event) => { if (event.key === 'Escape') { input.value = ''; run(); } });
});

/* ------------------------------------------------------------------ */
/* Fotos: revelam com fade + leve zoom quando terminam de carregar     */
/* ------------------------------------------------------------------ */
$$('.photo img').forEach((img) => {
    const done = () => img.classList.add('is-loaded');
    if (img.complete && img.naturalWidth) done();
    else { img.addEventListener('load', done, { once: true }); img.addEventListener('error', done, { once: true }); }
});

/* ------------------------------------------------------------------ */
/* Parallax suave nas fotos grandes                                    */
/* ------------------------------------------------------------------ */
const parallax = $$('[data-parallax]');
if (parallax.length && !reduceMotion.matches) {
    let ticking = false;
    const update = () => {
        ticking = false;
        parallax.forEach((img) => {
            const r = img.parentElement.getBoundingClientRect();
            if (r.bottom < 0 || r.top > innerHeight) return;
            img.style.setProperty('--py', `${Math.round(-r.top * 0.18)}px`);
        });
    };
    addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
    update();
}

/* ------------------------------------------------------------------ */
/* Cards: brilho segue o ponteiro                                      */
/* ------------------------------------------------------------------ */
$$('.craft-link, .feature').forEach((card) => {
    card.addEventListener('pointermove', (event) => {
        if (event.pointerType !== 'mouse') return;
        const r = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${((event.clientX - r.left) / r.width) * 100}%`);
        card.style.setProperty('--my', `${((event.clientY - r.top) / r.height) * 100}%`);
    });
});
