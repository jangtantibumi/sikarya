(() => {
    'use strict';

    let deferredInstallPrompt = null;
    const installButtons = () => Array.from(document.querySelectorAll('.install-app-btn, #install-app-btn'));
    const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;

    function setInstallButtonsVisible(visible) {
        installButtons().forEach((button) => {
            button.style.display = visible && !isStandalone() ? 'inline-flex' : 'none';
        });
    }

    function notify(title, message) {
        if (typeof window.showPremiumNotice === 'function') {
            window.showPremiumNotice(title, message);
            return;
        }

        window.alert(`${title}\n\n${message}`);
    }

    async function installApplication() {
        if (isStandalone()) {
            notify('Aplikasi Sudah Terpasang', 'SubaArch ERP sedang berjalan sebagai aplikasi di perangkat ini.');
            return;
        }

        if (!deferredInstallPrompt) {
            notify(
                'Instalasi Belum Tersedia',
                'Buka menu browser lalu pilih “Instal aplikasi” atau “Tambahkan ke layar utama”. Pastikan Anda menggunakan Chrome/Edge versi terbaru.',
            );
            return;
        }

        deferredInstallPrompt.prompt();
        const choice = await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        setInstallButtonsVisible(false);

        if (choice.outcome === 'accepted') {
            notify('Instalasi Dimulai', 'SubaArch ERP sedang ditambahkan ke perangkat Anda.');
        }
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        setInstallButtonsVisible(true);
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        setInstallButtonsVisible(false);
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.install-app-btn, #install-app-btn');
        if (!button) return;

        event.preventDefault();
        installApplication();
    });

    if ('serviceWorker' in navigator && window.isSecureContext) {
        let controllerRefreshing = false;
        navigator.serviceWorker.addEventListener('controllerchange', () => {
            if (controllerRefreshing) return;
            controllerRefreshing = true;
            window.location.reload();
        });

        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js', {
                scope: '/',
                updateViaCache: 'none'
            })
                .then((registration) => registration.update())
                .catch((error) => console.error('PWA service worker gagal didaftarkan:', error));
        });
    }

    setInstallButtonsVisible(false);

    window.ERP_PWA = {
        install: installApplication,
        isStandalone,
    };
})();
