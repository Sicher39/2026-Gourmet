(() => {
    if (window.__gourmetAdminPwaLoaded) return;
    window.__gourmetAdminPwaLoaded = true;

    const panelPath = '/admin';
    const isPanel = location.pathname === panelPath || location.pathname.startsWith(`${panelPath}/`);
    const isStandalone = () => matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    let installPrompt = null;

    const refreshInstallAction = () => {
        const container = document.querySelector('[data-pwa-install-container]');
        if (!container) return;
        container.hidden = isStandalone();
        if (isStandalone()) {
            container.querySelector('[data-pwa-install-hint]').hidden = true;
            container.querySelector('[data-pwa-install-dismiss]').hidden = true;
        }
    };

    if (isStandalone()) document.documentElement.classList.add('g-pwa-standalone');

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        installPrompt = event;
        refreshInstallAction();
    });
    window.addEventListener('appinstalled', () => {
        installPrompt = null;
        document.documentElement.classList.add('g-pwa-standalone');
        refreshInstallAction();
    });
    document.addEventListener('click', async (event) => {
        const container = document.querySelector('[data-pwa-install-container]');
        if (!container) return;
        const hint = container.querySelector('[data-pwa-install-hint]');
        const dismiss = container.querySelector('[data-pwa-install-dismiss]');
        if (event.target.closest('[data-pwa-install-dismiss]')) {
            hint.hidden = true;
            dismiss.hidden = true;
            return;
        }
        if (!event.target.closest('[data-pwa-install-button]') || isStandalone()) return;
        if (installPrompt) {
            const prompt = installPrompt;
            installPrompt = null;
            await prompt.prompt();
            await prompt.userChoice;
            return;
        }
        hint.textContent = isIos
            ? 'V Safari klepněte na Sdílet → Přidat na plochu.'
            : 'V nabídce prohlížeče vyberte Instalovat aplikaci nebo Přidat na plochu. Pokud možnost chybí, zkuste Chrome v zabezpečeném připojení.';
        hint.hidden = false;
        dismiss.hidden = false;
    });
    document.addEventListener('DOMContentLoaded', refreshInstallAction);
    document.addEventListener('livewire:navigated', refreshInstallAction);
    if (document.readyState !== 'loading') refreshInstallAction();

    if (isPanel && 'serviceWorker' in navigator && window.isSecureContext) {
        navigator.serviceWorker.register('/service-worker.js', { scope: panelPath, updateViaCache: 'none' })
            .catch(() => { /* Optional enhancement; Filament remains network-only. */ });
    }
})();
