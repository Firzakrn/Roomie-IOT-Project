{{--
    Shell behaviour shared by every layout: sidebar, theme toggle, user menu,
    flash/live toasts, and the polite live region for dashboard announcements.

    Kept out of the markup so the three layouts stay declarative, and out of
    any @vite bundle because this repo has no build step (AGENTS.md).
--}}
<div id="roomie-toast-host" class="no-print pointer-events-none fixed bottom-4 right-4 z-[60] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-2">
    {{-- Flash messages are server-rendered; JS toasts clone the same component
         below, so both share one look and one dismissal path. --}}
    @if (session('success'))
        <x-ui.toast tone="success" :message="session('success')" />
    @endif
    @if (session('error'))
        <x-ui.toast tone="danger" :message="session('error')" />
    @endif
</div>

<template id="roomie-toast-template">
    <x-ui.toast />
</template>

<div id="roomie-live" class="sr-only" role="status" aria-live="polite" aria-atomic="true"></div>

<script>
    (() => {
        const root = document.documentElement;

        /* ---------------- Theme ---------------- */

        const themeToggle = document.querySelector('[data-theme-toggle]');

        const syncThemeToggle = () => {
            if (!themeToggle) return;
            const dark = root.classList.contains('dark');
            themeToggle.setAttribute('aria-pressed', String(dark));
            themeToggle.setAttribute(
                'aria-label',
                dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'
            );
        };

        syncThemeToggle();

        themeToggle?.addEventListener('click', () => {
            const dark = !root.classList.contains('dark');
            root.classList.toggle('dark', dark);
            try {
                localStorage.setItem('roomie-theme', dark ? 'dark' : 'light');
            } catch (e) { /* private mode: theme stays for this page only */ }
            syncThemeToggle();
        });

        /* ---------------- Sidebar ---------------- */

        const sidebar = document.getElementById('roomie-sidebar');
        const backdrop = document.getElementById('roomie-nav-backdrop');
        const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

        const setDrawer = (open) => {
            if (!sidebar) return;
            sidebar.dataset.open = String(open);
            if (backdrop) backdrop.hidden = !open || isDesktop();
            document.body.classList.toggle('overflow-hidden', open && !isDesktop());
            document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
                button.setAttribute('aria-expanded', String(open));
            });
        };

        const applyCollapse = (collapsed) => {
            if (!sidebar) return;
            sidebar.dataset.collapsed = String(collapsed);
            root.dataset.sidebar = collapsed ? 'collapsed' : 'expanded';
        };

        if (sidebar && isDesktop()) {
            let collapsed = false;
            try {
                collapsed = localStorage.getItem('roomie-sidebar') === 'collapsed';
            } catch (e) { /* ignore */ }
            applyCollapse(collapsed);
        }

        document.querySelectorAll('[data-nav-toggle]').forEach((button) => {
            button.addEventListener('click', () => setDrawer(sidebar?.dataset.open !== 'true'));
        });

        backdrop?.addEventListener('click', () => setDrawer(false));

        document.querySelectorAll('[data-nav-collapse]').forEach((button) => {
            button.addEventListener('click', () => {
                const collapsed = sidebar?.dataset.collapsed !== 'true';
                applyCollapse(collapsed);
                try {
                    localStorage.setItem('roomie-sidebar', collapsed ? 'collapsed' : 'expanded');
                } catch (e) { /* ignore */ }
            });
        });

        window.addEventListener('resize', () => {
            if (isDesktop()) setDrawer(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && sidebar?.dataset.open === 'true') setDrawer(false);
        });

        /* ---------------- Menus ---------------- */

        document.querySelectorAll('[data-menu]').forEach((menu) => {
            const trigger = menu.querySelector('[data-menu-trigger]');
            const panel = menu.querySelector('[data-menu-panel]');
            if (!trigger || !panel) return;

            const open = () => {
                panel.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
            };
            const close = (refocus = false) => {
                panel.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                if (refocus) trigger.focus();
            };

            trigger.addEventListener('click', (event) => {
                event.stopPropagation();
                panel.hidden ? open() : close();
            });

            menu.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !panel.hidden) close(true);
            });

            document.addEventListener('click', (event) => {
                if (!menu.contains(event.target)) close();
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !panel.hidden) close(true);
            });
        });

        /* ---------------- Dialogs ---------------- */

        const dialogOpener = new WeakMap();

        window.roomieOpenDialog = (id) => {
            const dialog = typeof id === 'string' ? document.getElementById(id) : id;
            if (!dialog || dialog.open) return;
            dialogOpener.set(dialog, document.activeElement);
            dialog.showModal();
            document.body.classList.add('overflow-hidden');
        };

        const closeDialog = (dialog) => {
            if (!dialog?.open) return;
            dialog.close();
            document.body.classList.remove('overflow-hidden');
            const opener = dialogOpener.get(dialog);
            if (opener && typeof opener.focus === 'function') opener.focus();
        };

        document.addEventListener('click', (event) => {
            const dialog = event.target.closest('dialog.roomie-dialog');
            if (!dialog) return;

            if (event.target.closest('[data-dialog-close]')) {
                closeDialog(dialog);
                return;
            }

            // A backdrop click targets the <dialog> itself but lands outside its
            // box, so compare against the bounding rect rather than the target.
            if (event.target === dialog) {
                const rect = dialog.getBoundingClientRect();
                const inside = event.clientX >= rect.left && event.clientX <= rect.right
                    && event.clientY >= rect.top && event.clientY <= rect.bottom;
                if (!inside) closeDialog(dialog);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') return;
            document.querySelectorAll('dialog.roomie-dialog[open]').forEach(closeDialog);
        });

        /* ---------------- Password visibility ---------------- */

        document.addEventListener('click', (event) => {
            const toggle = event.target.closest('[data-password-toggle]');
            if (!toggle) return;

            const input = document.getElementById(toggle.dataset.passwordToggle);
            if (!input) return;

            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';

            toggle.querySelector('[data-password-icon="eye"]')?.classList.toggle('hidden', show);
            toggle.querySelector('[data-password-icon="eye-off"]')?.classList.toggle('hidden', !show);
            toggle.querySelector('.sr-only').textContent = show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';

            input.focus();
        });

        /* ---------------- Toasts ---------------- */

        const toastHost = document.getElementById('roomie-toast-host');
        const toastTemplate = document.getElementById('roomie-toast-template');

        const dismissToast = (element) => {
            if (!element || element.dataset.leaving) return;
            element.dataset.leaving = 'true';
            window.setTimeout(() => element.remove(), 200);
        };

        window.roomieToast = (tone, message) => {
            if (!toastHost || !toastTemplate || !message) return;

            const fragment = toastTemplate.content.cloneNode(true);
            const node = fragment.querySelector('[data-toast]');
            node.dataset.tone = tone;
            node.querySelector('[data-toast-message]').textContent = message;

            toastHost.appendChild(fragment);
            window.setTimeout(() => dismissToast(toastHost.lastElementChild), 5000);
        };

        // Flash toasts rendered server-side follow the same 5s life.
        Array.from(toastHost?.children ?? []).forEach((element) => {
            window.setTimeout(() => dismissToast(element), 5000);
        });

        window.roomieAnnounce = (message) => {
            const live = document.getElementById('roomie-live');
            if (!live || !message) return;
            // Clearing first forces screen readers to re-announce identical text.
            live.textContent = '';
            window.setTimeout(() => { live.textContent = message; }, 80);
        };
    })();
</script>