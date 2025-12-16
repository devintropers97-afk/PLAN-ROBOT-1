/**
 * ZYN Trade System - Main JavaScript
 * Version: 3.0 - Premium Edition
 * "Precision Over Emotion"
 */

(function() {
    'use strict';

    // ===== Configuration =====
    const CONFIG = {
        scrollOffset: 100,
        animationThreshold: 0.15,
        counterDuration: 2000,
        debounceDelay: 100
    };

    // ===== Utility Functions =====
    const debounce = (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    const throttle = (func, limit) => {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };

    // ===== Navbar Handler =====
    const initNavbar = () => {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;

        const handleScroll = () => {
            if (window.scrollY > CONFIG.scrollOffset) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        };

        window.addEventListener('scroll', throttle(handleScroll, 50));
        handleScroll();

        // Close mobile menu on click
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
        const navbarCollapse = document.querySelector('.navbar-collapse');

        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    };

    // ===== Scroll Animations =====
    const initScrollAnimations = () => {
        const animatedElements = document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right, .scale-in');
        if (!animatedElements.length) return;

        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -50px 0px',
            threshold: CONFIG.animationThreshold
        };

        const animationObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    animationObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        animatedElements.forEach(element => {
            animationObserver.observe(element);
        });
    };

    // ===== Counter Animation =====
    const initCounters = () => {
        const counters = document.querySelectorAll('[data-target], .counter-value');
        if (!counters.length) return;

        const animateCounter = (counter) => {
            const target = parseInt(counter.getAttribute('data-target'));
            if (isNaN(target)) return;

            const duration = CONFIG.counterDuration;
            const startTime = performance.now();
            const startValue = 0;

            const easeOutQuart = (t) => 1 - Math.pow(1 - t, 4);

            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easedProgress = easeOutQuart(progress);
                const currentValue = Math.floor(startValue + (target - startValue) * easedProgress);

                counter.textContent = currentValue.toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            };

            requestAnimationFrame(updateCounter);
        };

        const observerOptions = {
            threshold: 0.5
        };

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    counterObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    };

    // ===== Form Validation =====
    const initForms = () => {
        const forms = document.querySelectorAll('.needs-validation, form.needs-validation');

        forms.forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });

        // Password visibility toggle
        const passwordToggles = document.querySelectorAll('.password-toggle');
        passwordToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.closest('.input-group')?.querySelector('input') || this.previousElementSibling;
                const icon = this.querySelector('i');

                if (input && input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else if (input) {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // Country phone code auto-fill
        const countrySelect = document.getElementById('country');
        const phoneInput = document.getElementById('phone');

        if (countrySelect && phoneInput) {
            const phoneCodes = {
                'ID': '+62', 'MY': '+60', 'PH': '+63', 'TH': '+66',
                'VN': '+84', 'IN': '+91', 'PK': '+92', 'BD': '+880',
                'BR': '+55', 'MX': '+52', 'US': '+1', 'GB': '+44'
            };

            countrySelect.addEventListener('change', function() {
                const code = phoneCodes[this.value] || '';
                if (code && !phoneInput.value.startsWith('+')) {
                    phoneInput.value = code;
                }
            });
        }
    };

    // ===== Tooltips & Popovers =====
    const initTooltips = () => {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(tooltipTriggerEl => {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        popoverTriggerList.forEach(popoverTriggerEl => {
            new bootstrap.Popover(popoverTriggerEl);
        });
    };

    // ===== Smooth Scroll =====
    const initSmoothScroll = () => {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const navbarHeight = document.querySelector('.navbar')?.offsetHeight || 80;
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - navbarHeight;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    };

    // ===== Particle Animation Enhancement =====
    const initParticles = () => {
        const particlesContainer = document.querySelector('.hero-particles');
        if (!particlesContainer) return;

        const additionalParticles = 12;
        for (let i = 0; i < additionalParticles; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = `${Math.random() * 100}%`;
            particle.style.animationDelay = `${Math.random() * 10}s`;
            particle.style.animationDuration = `${10 + Math.random() * 10}s`;
            particlesContainer.appendChild(particle);
        }
    };

    // ===== Chart Line Animation =====
    const initChartAnimation = () => {
        const chartLine = document.querySelector('.chart-line');
        if (!chartLine) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    chartLine.style.animation = 'none';
                    chartLine.offsetHeight;
                    chartLine.style.animation = 'drawChart 3s ease-out forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        observer.observe(chartLine);
    };

    // ===== Mouse Follow Effect =====
    const initMouseFollow = () => {
        const heroSection = document.querySelector('.hero-section');
        if (!heroSection) return;

        heroSection.addEventListener('mousemove', throttle((e) => {
            const rect = heroSection.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width;
            const y = (e.clientY - rect.top) / rect.height;

            const moveX = (x - 0.5) * 20;
            const moveY = (y - 0.5) * 20;

            const heroChart = heroSection.querySelector('.hero-chart');
            if (heroChart) {
                heroChart.style.transform = `translate(${moveX}px, ${moveY}px)`;
            }
        }, 50));

        heroSection.addEventListener('mouseleave', () => {
            const heroChart = heroSection.querySelector('.hero-chart');
            if (heroChart) {
                heroChart.style.transform = 'translate(0, 0)';
                heroChart.style.transition = 'transform 0.5s ease';
            }
        });
    };

    // ===== Scroll Progress =====
    const initScrollProgress = () => {
        const progressBar = document.createElement('div');
        progressBar.className = 'scroll-progress';
        progressBar.innerHTML = '<div class="scroll-progress-bar"></div>';
        document.body.appendChild(progressBar);

        const progressBarInner = progressBar.querySelector('.scroll-progress-bar');

        window.addEventListener('scroll', throttle(() => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            progressBarInner.style.width = `${scrolled}%`;
        }, 16));

        const style = document.createElement('style');
        style.textContent = `
            .scroll-progress {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 3px;
                background: transparent;
                z-index: 9999;
            }
            .scroll-progress-bar {
                height: 100%;
                background: linear-gradient(90deg, #00d4ff, #7c3aed);
                width: 0%;
                transition: width 0.1s ease;
            }
        `;
        document.head.appendChild(style);
    };

    // ===== Back to Top Button =====
    const initBackToTop = () => {
        const btn = document.createElement('button');
        btn.className = 'back-to-top';
        btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
        btn.setAttribute('aria-label', 'Back to top');
        document.body.appendChild(btn);

        window.addEventListener('scroll', throttle(() => {
            if (window.scrollY > 300) {
                btn.classList.add('show');
            } else {
                btn.classList.remove('show');
            }
        }, 100));

        btn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        const style = document.createElement('style');
        style.textContent = `
            .back-to-top {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 50px;
                height: 50px;
                border: none;
                border-radius: 50%;
                background: linear-gradient(135deg, #00d4ff, #7c3aed);
                color: #0a0a0f;
                font-size: 1.25rem;
                cursor: pointer;
                opacity: 0;
                visibility: hidden;
                transform: translateY(20px);
                transition: all 0.3s ease;
                z-index: 999;
                box-shadow: 0 4px 20px rgba(0, 212, 255, 0.4);
            }
            .back-to-top:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 30px rgba(0, 212, 255, 0.5);
            }
            .back-to-top.show {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }
            @media (max-width: 768px) {
                .back-to-top {
                    bottom: 20px;
                    right: 20px;
                    width: 45px;
                    height: 45px;
                }
            }
        `;
        document.head.appendChild(style);
    };

    // ===== Global Functions =====
    window.copyToClipboard = async (text, button) => {
        try {
            await navigator.clipboard.writeText(text);
            if (button) {
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                button.classList.add('btn-success');

                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('btn-success');
                }, 2000);
            }
            return true;
        } catch (err) {
            console.error('Failed to copy: ', err);
            return false;
        }
    };

    window.showLoading = (element) => {
        if (!element) return;
        const originalContent = element.innerHTML;
        element.setAttribute('data-original-content', originalContent);
        element.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Loading...';
        element.disabled = true;
    };

    window.hideLoading = (element) => {
        if (!element) return;
        const originalContent = element.getAttribute('data-original-content');
        if (originalContent) {
            element.innerHTML = originalContent;
            element.disabled = false;
        }
    };

    window.formatCurrency = (amount, currency = 'USD') => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    };

    window.formatDate = (date, options = {}) => {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        return new Date(date).toLocaleDateString('en-US', { ...defaultOptions, ...options });
    };

    window.showToast = (message, type = 'info') => {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    };

    const createToastContainer = () => {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    };

    window.confirmAction = (message, callback) => {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="confirmBtn">Confirm</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bsModal = new bootstrap.Modal(modal);

        modal.querySelector('#confirmBtn').addEventListener('click', () => {
            callback();
            bsModal.hide();
        });

        modal.addEventListener('hidden.bs.modal', () => modal.remove());
        bsModal.show();
    };

    window.scrollToElement = (selector) => {
        const element = document.querySelector(selector);
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    };

    // Export ZYN namespace
    window.ZYN = {
        copyToClipboard: window.copyToClipboard,
        showLoading: window.showLoading,
        hideLoading: window.hideLoading,
        formatCurrency: window.formatCurrency,
        formatDate: window.formatDate,
        showToast: window.showToast,
        confirmAction: window.confirmAction,
        scrollToElement: window.scrollToElement,
        debounce,
        throttle
    };

    // ===== Initialize All =====
    const init = () => {
        initNavbar();
        initScrollAnimations();
        initCounters();
        initForms();
        initTooltips();
        initSmoothScroll();
        initParticles();
        initChartAnimation();
        initMouseFollow();
        initScrollProgress();
        initBackToTop();
    };

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
