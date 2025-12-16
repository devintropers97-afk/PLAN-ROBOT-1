/**
 * ZYN Trade System - UI Components
 * Toast Notifications, Loading Skeletons, Cookie Consent
 *
 * CARA PAKAI:
 * Toast: ZYN.toast.success('Berhasil!');
 * Skeleton: <div class="skeleton skeleton-text"></div>
 * Cookie: Otomatis muncul jika belum di-accept
 */

// Global namespace
window.ZYN = window.ZYN || {};

// ===========================================
// TOAST NOTIFICATIONS
// ===========================================
ZYN.toast = {
    container: null,

    init: function() {
        // Create toast container if not exists
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    /**
     * Show toast notification
     * @param {string} message - Message to display
     * @param {string} type - success, error, warning, info
     * @param {number} duration - Duration in ms (default 4000)
     */
    show: function(message, type = 'info', duration = 4000) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type} toast-enter`;

        const icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
            warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
        };

        toast.innerHTML = `
            <span class="toast-icon">${icons[type]}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="ZYN.toast.close(this.parentElement)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;

        this.container.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.remove('toast-enter'), 10);

        // Auto close
        if (duration > 0) {
            setTimeout(() => this.close(toast), duration);
        }

        return toast;
    },

    close: function(toast) {
        if (!toast) return;
        toast.classList.add('toast-exit');
        setTimeout(() => toast.remove(), 300);
    },

    // Shorthand methods
    success: function(message, duration) { return this.show(message, 'success', duration); },
    error: function(message, duration) { return this.show(message, 'error', duration); },
    warning: function(message, duration) { return this.show(message, 'warning', duration); },
    info: function(message, duration) { return this.show(message, 'info', duration); }
};

// ===========================================
// LOADING SKELETONS
// ===========================================
ZYN.skeleton = {
    /**
     * Create skeleton element
     * @param {string} type - text, avatar, card, table-row
     * @param {object} options - width, height, count
     */
    create: function(type, options = {}) {
        const el = document.createElement('div');
        el.className = `skeleton skeleton-${type}`;

        if (options.width) el.style.width = options.width;
        if (options.height) el.style.height = options.height;

        return el;
    },

    /**
     * Replace element with skeleton, return restore function
     */
    replace: function(element) {
        const skeleton = document.createElement('div');
        skeleton.className = 'skeleton';
        skeleton.style.width = element.offsetWidth + 'px';
        skeleton.style.height = element.offsetHeight + 'px';

        element.style.display = 'none';
        element.parentNode.insertBefore(skeleton, element);

        return function restore() {
            skeleton.remove();
            element.style.display = '';
        };
    },

    /**
     * Show skeleton in container
     */
    showIn: function(container, count = 3, type = 'card') {
        const fragment = document.createDocumentFragment();
        for (let i = 0; i < count; i++) {
            fragment.appendChild(this.create(type));
        }
        container.innerHTML = '';
        container.appendChild(fragment);
    }
};

// ===========================================
// COOKIE CONSENT
// ===========================================
ZYN.cookie = {
    CONSENT_KEY: 'zyn_cookie_consent',
    banner: null,

    init: function() {
        // Check if already consented
        if (this.hasConsent()) {
            return;
        }

        // Show banner after short delay
        setTimeout(() => this.showBanner(), 1500);
    },

    hasConsent: function() {
        return localStorage.getItem(this.CONSENT_KEY) === 'accepted';
    },

    showBanner: function() {
        if (this.banner) return;

        this.banner = document.createElement('div');
        this.banner.id = 'cookie-consent';
        this.banner.className = 'cookie-consent cookie-consent-enter';
        this.banner.innerHTML = `
            <div class="cookie-content">
                <div class="cookie-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="8" cy="9" r="1" fill="currentColor"/>
                        <circle cx="15" cy="8" r="1" fill="currentColor"/>
                        <circle cx="10" cy="14" r="1" fill="currentColor"/>
                        <circle cx="16" cy="13" r="1" fill="currentColor"/>
                        <circle cx="13" cy="17" r="1" fill="currentColor"/>
                    </svg>
                </div>
                <div class="cookie-text">
                    <h4>Kami Menggunakan Cookies</h4>
                    <p>Website ini menggunakan cookies untuk meningkatkan pengalaman Anda. Dengan melanjutkan, Anda menyetujui penggunaan cookies kami.</p>
                </div>
                <div class="cookie-buttons">
                    <button class="btn-cookie-accept" onclick="ZYN.cookie.accept()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Terima Semua
                    </button>
                    <button class="btn-cookie-settings" onclick="ZYN.cookie.showSettings()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Pengaturan
                    </button>
                </div>
            </div>
            <a href="/privacy.php" class="cookie-privacy">Kebijakan Privasi</a>
        `;

        document.body.appendChild(this.banner);
        setTimeout(() => this.banner.classList.remove('cookie-consent-enter'), 10);
    },

    accept: function(preferences = {}) {
        const consent = {
            accepted: true,
            timestamp: new Date().toISOString(),
            preferences: {
                necessary: true,
                analytics: preferences.analytics !== false,
                marketing: preferences.marketing !== false
            }
        };

        localStorage.setItem(this.CONSENT_KEY, 'accepted');
        localStorage.setItem('zyn_cookie_preferences', JSON.stringify(consent));

        this.hideBanner();

        // Initialize analytics if accepted
        if (consent.preferences.analytics && window.gtag) {
            gtag('consent', 'update', {
                'analytics_storage': 'granted'
            });
        }

        ZYN.toast.success('Preferensi cookie disimpan!');
    },

    showSettings: function() {
        // Show settings modal
        const modal = document.createElement('div');
        modal.className = 'cookie-modal';
        modal.innerHTML = `
            <div class="cookie-modal-content">
                <h3>Pengaturan Cookie</h3>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" checked disabled> Cookies Penting
                    </label>
                    <small>Diperlukan untuk fungsi dasar website</small>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="cookie-analytics" checked> Cookies Analitik
                    </label>
                    <small>Membantu kami memahami cara penggunaan website</small>
                </div>
                <div class="cookie-option">
                    <label>
                        <input type="checkbox" id="cookie-marketing" checked> Cookies Marketing
                    </label>
                    <small>Untuk menampilkan iklan yang relevan</small>
                </div>
                <div class="cookie-modal-buttons">
                    <button onclick="ZYN.cookie.saveSettings()">Simpan Pengaturan</button>
                    <button onclick="this.closest('.cookie-modal').remove()">Batal</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    },

    saveSettings: function() {
        const analytics = document.getElementById('cookie-analytics').checked;
        const marketing = document.getElementById('cookie-marketing').checked;

        this.accept({ analytics, marketing });

        document.querySelector('.cookie-modal').remove();
    },

    hideBanner: function() {
        if (!this.banner) return;
        this.banner.classList.add('cookie-consent-exit');
        setTimeout(() => {
            this.banner.remove();
            this.banner = null;
        }, 300);
    }
};

// ===========================================
// PWA INSTALL PROMPT
// ===========================================
ZYN.pwa = {
    deferredPrompt: null,

    init: function() {
        // Catch the install prompt
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallButton();
        });

        // Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('SW registered:', reg.scope))
                .catch(err => console.log('SW registration failed:', err));
        }
    },

    showInstallButton: function() {
        // Add install button to navbar or show banner
        const btn = document.createElement('button');
        btn.id = 'pwa-install-btn';
        btn.className = 'pwa-install-btn';
        btn.innerHTML = '<i class="fas fa-download"></i> Install App';
        btn.onclick = () => this.install();

        // Add to navbar if exists
        const navbar = document.querySelector('.navbar-nav');
        if (navbar) {
            const li = document.createElement('li');
            li.className = 'nav-item';
            li.appendChild(btn);
            navbar.appendChild(li);
        }
    },

    install: async function() {
        if (!this.deferredPrompt) return;

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;

        if (outcome === 'accepted') {
            ZYN.toast.success('Aplikasi berhasil diinstall!');
        }

        this.deferredPrompt = null;
        document.getElementById('pwa-install-btn')?.remove();
    }
};

// ===========================================
// PUSH NOTIFICATIONS
// ===========================================
ZYN.push = {
    init: async function() {
        if (!('Notification' in window) || !('serviceWorker' in navigator)) {
            console.log('Push notifications not supported');
            return;
        }

        // Check permission
        if (Notification.permission === 'granted') {
            await this.subscribe();
        }
    },

    async requestPermission() {
        const permission = await Notification.requestPermission();

        if (permission === 'granted') {
            await this.subscribe();
            ZYN.toast.success('Notifikasi diaktifkan!');
            return true;
        }

        ZYN.toast.warning('Izin notifikasi ditolak');
        return false;
    },

    async subscribe() {
        try {
            const registration = await navigator.serviceWorker.ready;

            // Check for existing subscription
            let subscription = await registration.pushManager.getSubscription();

            if (!subscription) {
                // Create new subscription
                // Note: You need to generate VAPID keys for production
                const vapidPublicKey = 'YOUR_VAPID_PUBLIC_KEY';

                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey)
                });
            }

            // Send subscription to server
            await fetch('/api/push-subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(subscription)
            });

            console.log('Push subscription successful');
        } catch (error) {
            console.error('Push subscription failed:', error);
        }
    },

    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
};

// ===========================================
// INITIALIZE ON DOM READY
// ===========================================
document.addEventListener('DOMContentLoaded', function() {
    ZYN.cookie.init();
    ZYN.pwa.init();
    ZYN.push.init();
});

// Make functions globally available
window.showToast = (msg, type) => ZYN.toast.show(msg, type);
