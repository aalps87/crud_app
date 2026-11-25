import './bootstrap';
import '../css/flight.css';
import './flight';
import './flight-scroll';

const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
        callback();
    }
};

ready(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const loader = document.querySelector('.site-loader');

    if (loader) {
        window.addEventListener('load', () => {
            window.setTimeout(() => loader.classList.add('is-hidden'), reduceMotion ? 0 : 550);
        }, { once: true });
        window.setTimeout(() => loader.classList.add('is-hidden'), 2200);
    }

    const nav = document.querySelector('[data-nav]');
    if (nav) {
        const updateNav = () => nav.classList.toggle('is-scrolled', window.scrollY > 48);
        updateNav();
        window.addEventListener('scroll', updateNav, { passive: true });
    }

    const revealElements = document.querySelectorAll('.reveal');
    if (revealElements.length) {
        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealElements.forEach((element) => element.classList.add('is-visible'));
        } else {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.14 });
            revealElements.forEach((element) => observer.observe(element));
        }
    }

    const parallaxElements = document.querySelectorAll('[data-parallax]');
    if (!reduceMotion && parallaxElements.length) {
        let pointerX = 0;
        let pointerY = 0;
        let frame = null;

        const renderParallax = () => {
            parallaxElements.forEach((element) => {
                const strength = Number(element.dataset.parallax || 0.03);
                element.style.transform = `translate3d(${pointerX * strength}px, ${pointerY * strength}px, 0)`;
            });
            frame = null;
        };

        window.addEventListener('pointermove', (event) => {
            pointerX = event.clientX - window.innerWidth / 2;
            pointerY = event.clientY - window.innerHeight / 2;
            if (!frame) frame = requestAnimationFrame(renderParallax);
        }, { passive: true });
    }

    const dialog = document.querySelector('[data-login-dialog]');
    if (dialog) {
        const openButtons = document.querySelectorAll('.js-open-login');
        const closeButtons = dialog.querySelectorAll('.js-close-login');
        const firstInput = () => dialog.querySelector('.auth-panel:not([hidden]) input:not([type="hidden"])');

        const openDialog = () => {
            if (!dialog.open) dialog.showModal();
            window.setTimeout(() => firstInput()?.focus(), 120);
        };

        const closeDialog = () => dialog.close();
        if (dialog.dataset.openOnLoad === 'true') openDialog();
        openButtons.forEach((button) => button.addEventListener('click', openDialog));
        closeButtons.forEach((button) => button.addEventListener('click', closeDialog));
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) closeDialog();
        });

        dialog.querySelectorAll('.toggle-password').forEach((passwordButton) => {
            const passwordInput = passwordButton.parentElement.querySelector('input');
            passwordButton.addEventListener('click', () => {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                passwordButton.textContent = isHidden ? 'OCULTAR' : 'VER';
                passwordButton.setAttribute('aria-label', isHidden ? 'Ocultar senha' : 'Mostrar senha');
            });
        });

        const switchAuth = (target) => {
            dialog.querySelectorAll('[data-auth-panel]').forEach((panel) => { panel.hidden = panel.dataset.authPanel !== target; });
            dialog.querySelectorAll('[role="tab"][data-auth-target]').forEach((tab) => tab.setAttribute('aria-selected', String(tab.dataset.authTarget === target)));
            dialog.querySelector(`[data-auth-panel="${target}"] input:not([type="hidden"])`)?.focus();
        };
        dialog.querySelectorAll('.js-auth-switch').forEach((button) => button.addEventListener('click', () => switchAuth(button.dataset.authTarget)));

        dialog.querySelectorAll('[data-login-form]').forEach((loginForm) => loginForm.addEventListener('submit', () => {
            if (!loginForm.checkValidity()) return;
            const submit = loginForm.querySelector('.login-submit');
            submit.classList.add('is-loading');
            submit.setAttribute('aria-disabled', 'true');
        }));
    }

    const sidebar = document.querySelector('[data-sidebar]');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    sidebarToggle?.addEventListener('click', () => sidebar?.classList.toggle('is-open'));
    document.addEventListener('click', (event) => {
        if (!sidebar?.classList.contains('is-open')) return;
        if (sidebar.contains(event.target) || sidebarToggle?.contains(event.target)) return;
        sidebar.classList.remove('is-open');
    });

    const search = document.querySelector('[data-table-search]');
    const rows = document.querySelectorAll('[data-product-table] tr:not(.empty-row)');
    search?.addEventListener('input', () => {
        const query = search.value.trim().toLocaleLowerCase('pt-BR');
        rows.forEach((row) => {
            row.hidden = query !== '' && !row.textContent.toLocaleLowerCase('pt-BR').includes(query);
        });
    });

    document.querySelectorAll('[data-positive-integer]').forEach((input) => {
        input.addEventListener('beforeinput', (event) => {
            if (event.data && !/^\d+$/.test(event.data)) event.preventDefault();
        });

        input.addEventListener('input', () => {
            if (input.value !== '' && !/^[1-9]\d*$/.test(input.value)) {
                input.value = '';
            }
        });
    });

    document.querySelectorAll('[data-image-upload]').forEach((upload) => {
        const input = upload.querySelector('[data-image-input]');
        const image = upload.querySelector('[data-image-preview]');
        const fileName = upload.querySelector('[data-file-name]');
        const fileStatus = upload.querySelector('[data-file-status]');
        let objectUrl = null;

        input?.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);

            image.src = objectUrl;
            image.alt = `Preview de ${file.name}`;
            image.hidden = false;
            fileName.textContent = file.name;
            fileStatus.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB · pronta para salvar`;
        });

        window.addEventListener('beforeunload', () => {
            if (objectUrl) URL.revokeObjectURL(objectUrl);
        }, { once: true });
    });

    document.querySelectorAll('[data-delete-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.deleteForm || 'Remover esta aeronave do inventário? Esta ação não pode ser desfeita.')) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.app-toast button').forEach((button) => {
        button.addEventListener('click', () => button.closest('.app-toast')?.remove());
    });
});
