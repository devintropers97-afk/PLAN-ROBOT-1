/**
 * ZYN Trade System - PREMIUM Edition JavaScript
 * Tier 1-2 Level Premium Animations & Effects
 * Features: GSAP, Three.js, Custom Cursor, Smooth Scroll, Parallax
 */

'use strict';

// ===== PREMIUM PRELOADER =====
class PremiumPreloader {
    constructor() {
        this.preloader = document.querySelector('.premium-preloader');
        this.progressBar = document.querySelector('.preloader-progress-bar');
        this.counter = document.querySelector('.preloader-counter');
        this.progress = 0;
        this.loaded = false;

        if (this.preloader) {
            this.init();
        }
    }

    init() {
        // Simulate loading progress
        const interval = setInterval(() => {
            this.progress += Math.random() * 15;
            if (this.progress >= 100) {
                this.progress = 100;
                clearInterval(interval);
                this.complete();
            }
            this.updateProgress();
        }, 100);

        // Ensure completion on window load
        window.addEventListener('load', () => {
            if (!this.loaded) {
                this.progress = 100;
                this.updateProgress();
                setTimeout(() => this.complete(), 300);
            }
        });

        // Fallback timeout
        setTimeout(() => {
            if (!this.loaded) {
                this.complete();
            }
        }, 5000);
    }

    updateProgress() {
        if (this.progressBar) {
            this.progressBar.style.width = `${this.progress}%`;
        }
        if (this.counter) {
            this.counter.textContent = `${Math.round(this.progress)}%`;
        }
    }

    complete() {
        this.loaded = true;
        if (this.preloader) {
            this.preloader.classList.add('loaded');
            setTimeout(() => {
                this.preloader.style.display = 'none';
                document.body.classList.add('loaded');
                // Trigger animations after preloader
                window.dispatchEvent(new CustomEvent('preloaderComplete'));
            }, 1000);
        }
    }
}

// ===== CUSTOM CURSOR =====
class CustomCursor {
    constructor() {
        this.dot = null;
        this.outline = null;
        this.cursorX = 0;
        this.cursorY = 0;
        this.outlineX = 0;
        this.outlineY = 0;
        this.isHovering = false;
        this.isClicking = false;

        // Only enable on desktop
        if (window.innerWidth > 768 && !('ontouchstart' in window)) {
            this.init();
        }
    }

    init() {
        // Create cursor elements
        this.dot = document.createElement('div');
        this.dot.className = 'cursor-dot';
        document.body.appendChild(this.dot);

        this.outline = document.createElement('div');
        this.outline.className = 'cursor-outline';
        document.body.appendChild(this.outline);

        // Add premium cursor class to body
        document.body.classList.add('premium-cursor-active');

        // Event listeners
        document.addEventListener('mousemove', (e) => this.onMouseMove(e));
        document.addEventListener('mousedown', () => this.onMouseDown());
        document.addEventListener('mouseup', () => this.onMouseUp());

        // Hover effects for interactive elements
        const hoverElements = document.querySelectorAll('a, button, .btn, input, textarea, select, .card, .nav-link, [data-cursor="hover"]');
        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => this.onHoverEnter());
            el.addEventListener('mouseleave', () => this.onHoverLeave());
        });

        // Animation loop
        this.animate();
    }

    onMouseMove(e) {
        this.cursorX = e.clientX;
        this.cursorY = e.clientY;
    }

    onMouseDown() {
        this.isClicking = true;
        this.dot.classList.add('click');
        this.outline.classList.add('click');
    }

    onMouseUp() {
        this.isClicking = false;
        this.dot.classList.remove('click');
        this.outline.classList.remove('click');
    }

    onHoverEnter() {
        this.isHovering = true;
        this.dot.classList.add('hover');
        this.outline.classList.add('hover');
    }

    onHoverLeave() {
        this.isHovering = false;
        this.dot.classList.remove('hover');
        this.outline.classList.remove('hover');
    }

    animate() {
        // Dot follows cursor directly
        this.dot.style.left = `${this.cursorX}px`;
        this.dot.style.top = `${this.cursorY}px`;

        // Outline follows with easing
        this.outlineX += (this.cursorX - this.outlineX) * 0.15;
        this.outlineY += (this.cursorY - this.outlineY) * 0.15;
        this.outline.style.left = `${this.outlineX}px`;
        this.outline.style.top = `${this.outlineY}px`;

        requestAnimationFrame(() => this.animate());
    }
}

// ===== SMOOTH SCROLL (LENIS ALTERNATIVE) =====
class SmoothScroll {
    constructor() {
        this.currentY = 0;
        this.targetY = 0;
        this.ease = 0.1;
        this.scrolling = false;

        this.init();
    }

    init() {
        // Add scroll progress bar
        this.progressBar = document.createElement('div');
        this.progressBar.className = 'scroll-progress';
        document.body.appendChild(this.progressBar);

        // Update scroll progress
        window.addEventListener('scroll', () => this.updateProgress());

        // Smooth anchor scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => this.smoothScrollTo(e));
        });
    }

    updateProgress() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrollTop / docHeight) * 100;
        this.progressBar.style.width = `${progress}%`;
    }

    smoothScrollTo(e) {
        const href = e.currentTarget.getAttribute('href');
        if (href === '#') return;

        const target = document.querySelector(href);
        if (target) {
            e.preventDefault();
            const targetPosition = target.getBoundingClientRect().top + window.scrollY - 80;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    }
}

// ===== GSAP ANIMATIONS =====
class GSAPAnimations {
    constructor() {
        this.init();
    }

    init() {
        // Wait for GSAP to load
        if (typeof gsap === 'undefined') {
            console.warn('GSAP not loaded');
            return;
        }

        // Register ScrollTrigger
        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
        }

        // Initialize animations after preloader
        window.addEventListener('preloaderComplete', () => {
            this.initHeroAnimations();
            this.initScrollAnimations();
            this.initParallax();
            this.initTextAnimations();
            this.initCardAnimations();
            this.initCounterAnimations();
        });

        // Fallback if preloader doesn't exist
        if (!document.querySelector('.premium-preloader')) {
            window.addEventListener('load', () => {
                setTimeout(() => {
                    this.initHeroAnimations();
                    this.initScrollAnimations();
                    this.initParallax();
                    this.initTextAnimations();
                    this.initCardAnimations();
                    this.initCounterAnimations();
                }, 100);
            });
        }
    }

    initHeroAnimations() {
        const heroContent = document.querySelector('.hero-content');
        if (!heroContent) return;

        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });

        // Hero badge
        tl.from('.hero-badge', {
            y: 30,
            opacity: 0,
            duration: 0.8
        });

        // Hero title - character by character
        const heroTitle = document.querySelector('.hero-title');
        if (heroTitle) {
            tl.from('.hero-title', {
                y: 50,
                opacity: 0,
                duration: 1,
                ease: 'power4.out'
            }, '-=0.4');
        }

        // Tagline and subtitle
        tl.from('.hero-tagline', {
            y: 30,
            opacity: 0,
            duration: 0.8
        }, '-=0.6');

        tl.from('.hero-subtitle', {
            y: 30,
            opacity: 0,
            duration: 0.8
        }, '-=0.6');

        // Buttons with stagger
        tl.from('.hero-buttons .btn', {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.2
        }, '-=0.4');

        // Stats
        tl.from('.hero-stats .stat-item', {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.15
        }, '-=0.3');

        // Hero visual
        tl.from('.hero-visual', {
            x: 100,
            opacity: 0,
            duration: 1,
            ease: 'power2.out'
        }, '-=1');

        // Scroll indicator
        tl.from('.scroll-indicator', {
            y: 20,
            opacity: 0,
            duration: 0.5
        }, '-=0.3');
    }

    initScrollAnimations() {
        if (typeof ScrollTrigger === 'undefined') return;

        // Section headers
        gsap.utils.toArray('.section-header').forEach(header => {
            gsap.from(header, {
                y: 60,
                opacity: 0,
                duration: 1,
                scrollTrigger: {
                    trigger: header,
                    start: 'top 80%',
                    end: 'bottom 20%',
                    toggleActions: 'play none none reverse'
                }
            });
        });

        // Cards with stagger
        gsap.utils.toArray('.row.g-4').forEach(row => {
            const cards = row.querySelectorAll('.col-lg-4, .col-lg-3, .col-md-6');
            if (cards.length > 0) {
                gsap.from(cards, {
                    y: 80,
                    opacity: 0,
                    duration: 0.8,
                    stagger: 0.15,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: row,
                        start: 'top 75%',
                        toggleActions: 'play none none reverse'
                    }
                });
            }
        });

        // Process steps
        gsap.utils.toArray('.process-step').forEach((step, i) => {
            gsap.from(step, {
                x: i % 2 === 0 ? -50 : 50,
                opacity: 0,
                duration: 0.8,
                scrollTrigger: {
                    trigger: step,
                    start: 'top 80%',
                    toggleActions: 'play none none reverse'
                }
            });
        });

        // Stats banner
        const statsBanner = document.querySelector('.stats-banner');
        if (statsBanner) {
            gsap.from('.stat-big', {
                scale: 0.8,
                opacity: 0,
                duration: 0.6,
                stagger: 0.1,
                scrollTrigger: {
                    trigger: statsBanner,
                    start: 'top 80%',
                    toggleActions: 'play none none reverse'
                }
            });
        }
    }

    initParallax() {
        if (typeof ScrollTrigger === 'undefined') return;

        // Parallax for hero visual
        const heroVisual = document.querySelector('.hero-visual');
        if (heroVisual) {
            gsap.to(heroVisual, {
                y: 100,
                scrollTrigger: {
                    trigger: '.hero-section',
                    start: 'top top',
                    end: 'bottom top',
                    scrub: 1
                }
            });
        }

        // Parallax for floating shapes
        gsap.utils.toArray('.shape').forEach((shape, i) => {
            gsap.to(shape, {
                y: (i + 1) * 50,
                scrollTrigger: {
                    trigger: 'body',
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 1
                }
            });
        });

        // Section parallax backgrounds
        gsap.utils.toArray('.bg-darker').forEach(section => {
            gsap.from(section, {
                backgroundPosition: '50% 0%',
                scrollTrigger: {
                    trigger: section,
                    start: 'top bottom',
                    end: 'bottom top',
                    scrub: 1
                }
            });
        });
    }

    initTextAnimations() {
        // Split text animation for section titles
        gsap.utils.toArray('.section-title').forEach(title => {
            const words = title.textContent.split(' ');
            title.innerHTML = words.map(word =>
                `<span class="word"><span class="word-inner">${word}</span></span>`
            ).join(' ');

            gsap.from(title.querySelectorAll('.word-inner'), {
                y: '100%',
                opacity: 0,
                duration: 0.8,
                stagger: 0.05,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: title,
                    start: 'top 80%',
                    toggleActions: 'play none none reverse'
                }
            });
        });
    }

    initCardAnimations() {
        // 3D tilt effect for cards
        const cards = document.querySelectorAll('.card, .pricing-card, .feature-card, .strategy-card');

        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;

                gsap.to(card, {
                    rotateX: rotateX,
                    rotateY: rotateY,
                    transformPerspective: 1000,
                    duration: 0.3,
                    ease: 'power2.out'
                });
            });

            card.addEventListener('mouseleave', () => {
                gsap.to(card, {
                    rotateX: 0,
                    rotateY: 0,
                    duration: 0.5,
                    ease: 'power2.out'
                });
            });
        });
    }

    initCounterAnimations() {
        if (typeof ScrollTrigger === 'undefined') return;

        const counters = document.querySelectorAll('.stat-number, .stat-value[data-target]');

        counters.forEach(counter => {
            const target = counter.getAttribute('data-target') || counter.textContent;
            const numericValue = parseFloat(target.replace(/[^0-9.]/g, ''));
            const prefix = target.match(/^[^0-9]*/)[0] || '';
            const suffix = target.match(/[^0-9]*$/)[0] || '';

            if (!isNaN(numericValue)) {
                const originalText = counter.textContent;

                ScrollTrigger.create({
                    trigger: counter,
                    start: 'top 85%',
                    onEnter: () => {
                        gsap.fromTo(counter,
                            { innerHTML: prefix + '0' + suffix },
                            {
                                innerHTML: prefix + numericValue + suffix,
                                duration: 2,
                                ease: 'power2.out',
                                snap: { innerHTML: 1 },
                                onUpdate: function() {
                                    const current = parseFloat(counter.textContent.replace(/[^0-9.]/g, ''));
                                    counter.textContent = prefix + Math.round(current).toLocaleString() + suffix;
                                },
                                onComplete: function() {
                                    counter.textContent = originalText;
                                }
                            }
                        );
                    },
                    once: true
                });
            }
        });
    }
}

// ===== MAGNETIC BUTTONS =====
class MagneticButtons {
    constructor() {
        this.buttons = document.querySelectorAll('.btn-primary, .btn-lg, [data-magnetic]');
        this.init();
    }

    init() {
        if (window.innerWidth <= 768) return;

        this.buttons.forEach(btn => {
            btn.addEventListener('mousemove', (e) => this.onMouseMove(e, btn));
            btn.addEventListener('mouseleave', (e) => this.onMouseLeave(e, btn));
        });
    }

    onMouseMove(e, btn) {
        const rect = btn.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;

        gsap.to(btn, {
            x: x * 0.3,
            y: y * 0.3,
            duration: 0.3,
            ease: 'power2.out'
        });
    }

    onMouseLeave(e, btn) {
        gsap.to(btn, {
            x: 0,
            y: 0,
            duration: 0.5,
            ease: 'elastic.out(1, 0.3)'
        });
    }
}

// ===== THREE.JS 3D BACKGROUND =====
class ThreeBackground {
    constructor() {
        this.container = document.querySelector('.hero-3d-canvas');
        if (!this.container || typeof THREE === 'undefined') return;

        this.init();
    }

    init() {
        // Scene setup
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        this.renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });

        this.renderer.setSize(window.innerWidth, window.innerHeight);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.container.appendChild(this.renderer.domElement);

        // Create particles
        this.createParticles();

        // Create floating geometry
        this.createGeometry();

        // Position camera
        this.camera.position.z = 30;

        // Mouse interaction
        this.mouse = new THREE.Vector2();
        document.addEventListener('mousemove', (e) => {
            this.mouse.x = (e.clientX / window.innerWidth) * 2 - 1;
            this.mouse.y = -(e.clientY / window.innerHeight) * 2 + 1;
        });

        // Resize handler
        window.addEventListener('resize', () => this.onResize());

        // Animation loop
        this.animate();
    }

    createParticles() {
        const geometry = new THREE.BufferGeometry();
        const count = 500;
        const positions = new Float32Array(count * 3);

        for (let i = 0; i < count * 3; i += 3) {
            positions[i] = (Math.random() - 0.5) * 100;
            positions[i + 1] = (Math.random() - 0.5) * 100;
            positions[i + 2] = (Math.random() - 0.5) * 100;
        }

        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const material = new THREE.PointsMaterial({
            color: 0x00d4ff,
            size: 0.3,
            transparent: true,
            opacity: 0.6
        });

        this.particles = new THREE.Points(geometry, material);
        this.scene.add(this.particles);
    }

    createGeometry() {
        // Floating icosahedron
        const icoGeometry = new THREE.IcosahedronGeometry(5, 1);
        const icoMaterial = new THREE.MeshBasicMaterial({
            color: 0x7c3aed,
            wireframe: true,
            transparent: true,
            opacity: 0.3
        });
        this.icosahedron = new THREE.Mesh(icoGeometry, icoMaterial);
        this.icosahedron.position.set(15, 5, -10);
        this.scene.add(this.icosahedron);

        // Floating torus
        const torusGeometry = new THREE.TorusGeometry(3, 1, 16, 50);
        const torusMaterial = new THREE.MeshBasicMaterial({
            color: 0x00d4ff,
            wireframe: true,
            transparent: true,
            opacity: 0.3
        });
        this.torus = new THREE.Mesh(torusGeometry, torusMaterial);
        this.torus.position.set(-15, -5, -10);
        this.scene.add(this.torus);

        // Floating octahedron
        const octGeometry = new THREE.OctahedronGeometry(4, 0);
        const octMaterial = new THREE.MeshBasicMaterial({
            color: 0x00d4ff,
            wireframe: true,
            transparent: true,
            opacity: 0.2
        });
        this.octahedron = new THREE.Mesh(octGeometry, octMaterial);
        this.octahedron.position.set(0, 10, -15);
        this.scene.add(this.octahedron);
    }

    onResize() {
        this.camera.aspect = window.innerWidth / window.innerHeight;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(window.innerWidth, window.innerHeight);
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        // Rotate particles
        if (this.particles) {
            this.particles.rotation.x += 0.0005;
            this.particles.rotation.y += 0.0005;
        }

        // Animate geometry
        if (this.icosahedron) {
            this.icosahedron.rotation.x += 0.005;
            this.icosahedron.rotation.y += 0.005;
            this.icosahedron.position.y += Math.sin(Date.now() * 0.001) * 0.02;
        }

        if (this.torus) {
            this.torus.rotation.x += 0.003;
            this.torus.rotation.y += 0.007;
            this.torus.position.y += Math.sin(Date.now() * 0.001 + 1) * 0.02;
        }

        if (this.octahedron) {
            this.octahedron.rotation.x += 0.004;
            this.octahedron.rotation.z += 0.006;
            this.octahedron.position.y += Math.sin(Date.now() * 0.001 + 2) * 0.02;
        }

        // Mouse parallax
        if (this.camera) {
            this.camera.position.x += (this.mouse.x * 5 - this.camera.position.x) * 0.02;
            this.camera.position.y += (this.mouse.y * 5 - this.camera.position.y) * 0.02;
            this.camera.lookAt(this.scene.position);
        }

        this.renderer.render(this.scene, this.camera);
    }
}

// ===== FLOATING SHAPES =====
class FloatingShapes {
    constructor() {
        this.container = document.createElement('div');
        this.container.className = 'floating-shapes';
        document.body.appendChild(this.container);

        this.createShapes();
        this.animateShapes();
    }

    createShapes() {
        const shapes = [
            { class: 'shape-circle', x: '10%', y: '20%' },
            { class: 'shape-circle', x: '80%', y: '60%' },
            { class: 'shape-square', x: '70%', y: '15%' },
            { class: 'shape-square', x: '15%', y: '70%' },
            { class: 'shape-dots', x: '85%', y: '30%' },
            { class: 'shape-dots', x: '5%', y: '50%' }
        ];

        shapes.forEach((shape, i) => {
            const el = document.createElement('div');
            el.className = `shape ${shape.class}`;
            el.style.left = shape.x;
            el.style.top = shape.y;
            el.dataset.speed = (i + 1) * 0.2;
            this.container.appendChild(el);
        });
    }

    animateShapes() {
        const shapes = this.container.querySelectorAll('.shape');

        window.addEventListener('scroll', () => {
            const scrollY = window.scrollY;

            shapes.forEach(shape => {
                const speed = parseFloat(shape.dataset.speed);
                const y = scrollY * speed;
                const rotation = scrollY * speed * 0.1;

                shape.style.transform = `translateY(${y}px) rotate(${rotation}deg)`;
            });
        });
    }
}

// ===== REVEAL ON SCROLL (Fallback without GSAP) =====
class RevealOnScroll {
    constructor() {
        this.elements = document.querySelectorAll('.fade-in, [data-reveal]');
        this.init();
    }

    init() {
        // Use Intersection Observer
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        this.elements.forEach(el => {
            el.classList.add('reveal-up');
            observer.observe(el);
        });
    }
}

// ===== NOISE OVERLAY =====
class NoiseOverlay {
    constructor() {
        const noise = document.createElement('div');
        noise.className = 'noise-overlay';
        document.body.appendChild(noise);
    }
}

// ===== INITIALIZE EVERYTHING =====
document.addEventListener('DOMContentLoaded', () => {
    // Initialize core components
    new PremiumPreloader();
    new CustomCursor();
    new SmoothScroll();
    new FloatingShapes();
    new NoiseOverlay();

    // Initialize GSAP animations if available
    if (typeof gsap !== 'undefined') {
        new GSAPAnimations();
        new MagneticButtons();
    } else {
        // Fallback animations
        new RevealOnScroll();
    }

    // Initialize Three.js if available
    if (typeof THREE !== 'undefined') {
        new ThreeBackground();
    }

    // Initialize premium features
    new PageTransition();
    new BackToTop();
    new CookieConsent();
    new LiveNotifications();
    new FloatingWhatsApp();

    console.log('%c ZYN Trade Premium Edition ',
        'background: linear-gradient(135deg, #00d4ff, #7c3aed); color: white; padding: 10px 20px; font-size: 16px; font-weight: bold; border-radius: 5px;');
});

// ===== PAGE TRANSITION =====
class PageTransition {
    constructor() {
        this.createTransition();
        this.init();
    }

    createTransition() {
        const transition = document.createElement('div');
        transition.className = 'page-transition';

        for (let i = 0; i < 4; i++) {
            const panel = document.createElement('div');
            panel.className = 'page-transition-panel';
            transition.appendChild(panel);
        }

        document.body.appendChild(transition);
        this.transition = transition;
    }

    init() {
        // Intercept internal links
        document.querySelectorAll('a[href]').forEach(link => {
            const href = link.getAttribute('href');

            // Skip external links, anchors, and special links
            if (!href ||
                href.startsWith('#') ||
                href.startsWith('http') ||
                href.startsWith('mailto:') ||
                href.startsWith('tel:') ||
                href.startsWith('javascript:') ||
                link.target === '_blank') {
                return;
            }

            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.navigateTo(href);
            });
        });
    }

    navigateTo(url) {
        this.transition.classList.add('active');

        setTimeout(() => {
            window.location.href = url;
        }, 800);
    }
}

// ===== BACK TO TOP BUTTON =====
class BackToTop {
    constructor() {
        this.createButton();
        this.init();
    }

    createButton() {
        const button = document.createElement('button');
        button.className = 'back-to-top';
        button.innerHTML = `
            <svg class="back-to-top-progress" viewBox="0 0 44 44">
                <circle cx="22" cy="22" r="20"></circle>
                <circle class="progress-circle" cx="22" cy="22" r="20"></circle>
            </svg>
            <i class="fas fa-chevron-up"></i>
        `;
        document.body.appendChild(button);
        this.button = button;
        this.progressCircle = button.querySelector('.progress-circle');
    }

    init() {
        // Scroll listener
        window.addEventListener('scroll', () => this.onScroll());

        // Click handler
        this.button.addEventListener('click', () => this.scrollToTop());
    }

    onScroll() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = scrollTop / docHeight;

        // Show/hide button
        if (scrollTop > 300) {
            this.button.classList.add('visible');
        } else {
            this.button.classList.remove('visible');
        }

        // Update progress ring
        if (this.progressCircle) {
            const circumference = 126; // 2 * PI * 20
            const offset = circumference - (progress * circumference);
            this.progressCircle.style.strokeDashoffset = offset;
        }
    }

    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
}

// ===== COOKIE CONSENT =====
class CookieConsent {
    constructor() {
        this.storageKey = 'zyn_cookie_consent';

        // Check if already accepted
        if (!this.hasConsent()) {
            this.createBanner();
            setTimeout(() => this.show(), 2000);
        }
    }

    hasConsent() {
        return localStorage.getItem(this.storageKey) !== null;
    }

    createBanner() {
        const banner = document.createElement('div');
        banner.className = 'cookie-consent';
        banner.innerHTML = `
            <div class="cookie-consent-inner">
                <div class="cookie-consent-text">
                    <h4><i class="fas fa-cookie-bite me-2"></i>Cookie Notice</h4>
                    <p>We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies. <a href="privacy.php">Learn more</a></p>
                </div>
                <div class="cookie-consent-buttons">
                    <button class="cookie-btn cookie-btn-accept">Accept All</button>
                    <button class="cookie-btn cookie-btn-decline">Decline</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);
        this.banner = banner;

        // Event listeners
        banner.querySelector('.cookie-btn-accept').addEventListener('click', () => this.accept());
        banner.querySelector('.cookie-btn-decline').addEventListener('click', () => this.decline());
    }

    show() {
        if (this.banner) {
            this.banner.classList.add('show');
        }
    }

    hide() {
        if (this.banner) {
            this.banner.classList.remove('show');
            setTimeout(() => {
                this.banner.remove();
            }, 500);
        }
    }

    accept() {
        localStorage.setItem(this.storageKey, 'accepted');
        this.hide();
    }

    decline() {
        localStorage.setItem(this.storageKey, 'declined');
        this.hide();
    }
}

// ===== LIVE ACTIVITY NOTIFICATIONS =====
class LiveNotifications {
    constructor() {
        this.notifications = [
            { icon: 'fa-user-plus', title: 'New Member Joined', text: 'Someone from Jakarta just joined ZYN Trade', time: '2 minutes ago' },
            { icon: 'fa-chart-line', title: 'Trade Success', text: 'User made +$127 profit on EUR/USD', time: '5 minutes ago' },
            { icon: 'fa-download', title: 'New Download', text: 'ZYN Bot downloaded by new member', time: '3 minutes ago' },
            { icon: 'fa-star', title: '5-Star Review', text: '"Amazing system, very profitable!" - Budi S.', time: '8 minutes ago' },
            { icon: 'fa-fire', title: 'Hot Streak', text: '15 consecutive wins on GBP/USD', time: '12 minutes ago' },
            { icon: 'fa-trophy', title: 'Achievement Unlocked', text: 'Member reached $1,000 profit milestone', time: '15 minutes ago' },
            { icon: 'fa-rocket', title: 'Strategy Activated', text: 'Dragon Power strategy activated by user', time: '4 minutes ago' },
            { icon: 'fa-shield-alt', title: 'Account Verified', text: 'New premium member verified', time: '6 minutes ago' }
        ];

        this.currentNotification = null;
        this.notificationElement = null;
        this.isShowing = false;

        this.createNotificationElement();
        this.startNotifications();
    }

    createNotificationElement() {
        const notification = document.createElement('div');
        notification.className = 'live-notification';
        notification.innerHTML = `
            <button class="live-notification-close"><i class="fas fa-times"></i></button>
            <div class="live-notification-icon"><i class="fas"></i></div>
            <div class="live-notification-content">
                <div class="live-notification-title"><span class="live-dot"></span><span class="title-text"></span></div>
                <div class="live-notification-text"></div>
                <div class="live-notification-time"></div>
            </div>
        `;
        document.body.appendChild(notification);
        this.notificationElement = notification;

        // Close button
        notification.querySelector('.live-notification-close').addEventListener('click', () => this.hide());
    }

    startNotifications() {
        // Initial delay before first notification
        setTimeout(() => this.showRandomNotification(), 5000);
    }

    showRandomNotification() {
        if (this.isShowing) return;

        // Random notification
        const notification = this.notifications[Math.floor(Math.random() * this.notifications.length)];

        // Update content
        const iconEl = this.notificationElement.querySelector('.live-notification-icon i');
        iconEl.className = `fas ${notification.icon}`;

        this.notificationElement.querySelector('.title-text').textContent = notification.title;
        this.notificationElement.querySelector('.live-notification-text').textContent = notification.text;
        this.notificationElement.querySelector('.live-notification-time').textContent = notification.time;

        // Show
        this.isShowing = true;
        this.notificationElement.classList.add('show');

        // Auto hide after 5 seconds
        setTimeout(() => {
            this.hide();

            // Schedule next notification (random interval 15-30 seconds)
            const nextInterval = Math.random() * 15000 + 15000;
            setTimeout(() => this.showRandomNotification(), nextInterval);
        }, 5000);
    }

    hide() {
        this.notificationElement.classList.remove('show');
        this.isShowing = false;
    }
}

// ===== FLOATING WHATSAPP HANDLER =====
class FloatingWhatsApp {
    constructor() {
        this.init();
    }

    init() {
        const whatsappBtn = document.querySelector('.floating-whatsapp .whatsapp-btn');
        if (!whatsappBtn) return;

        // Track click for analytics
        whatsappBtn.addEventListener('click', () => {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'whatsapp_click', {
                    event_category: 'engagement',
                    event_label: 'WhatsApp Contact'
                });
            }
        });
    }
}

// ===== UTILITY FUNCTIONS =====
window.ZYNPremium = {
    // Animate number counter
    animateCounter: function(element, target, duration = 2000) {
        const start = 0;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(start + (target - start) * easeProgress);

            element.textContent = current.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    },

    // Smooth scroll to element
    scrollTo: function(target, offset = 80) {
        const element = typeof target === 'string' ? document.querySelector(target) : target;
        if (element) {
            const top = element.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    },

    // Add ripple effect to element
    addRipple: function(element) {
        element.classList.add('ripple');
    },

    // Trigger glitch effect
    triggerGlitch: function(element, duration = 300) {
        element.classList.add('glitch-text');
        element.setAttribute('data-text', element.textContent);
        setTimeout(() => element.classList.remove('glitch-text'), duration);
    }
};

// ===== BATCH 3: ADVANCED PREMIUM FEATURES =====

// ===== TYPEWRITER EFFECT =====
class TypewriterEffect {
    constructor(element, texts, options = {}) {
        this.element = element;
        this.texts = texts;
        this.options = {
            typeSpeed: options.typeSpeed || 100,
            deleteSpeed: options.deleteSpeed || 50,
            pauseTime: options.pauseTime || 2000,
            loop: options.loop !== false
        };
        this.textIndex = 0;
        this.charIndex = 0;
        this.isDeleting = false;

        if (this.element && this.texts.length > 0) {
            this.init();
        }
    }

    init() {
        this.element.classList.add('typewriter-text');
        this.type();
    }

    type() {
        const currentText = this.texts[this.textIndex];

        if (this.isDeleting) {
            this.element.textContent = currentText.substring(0, this.charIndex - 1);
            this.charIndex--;
            this.element.classList.add('deleting');
        } else {
            this.element.textContent = currentText.substring(0, this.charIndex + 1);
            this.charIndex++;
            this.element.classList.remove('deleting');
        }

        let delay = this.isDeleting ? this.options.deleteSpeed : this.options.typeSpeed;

        if (!this.isDeleting && this.charIndex === currentText.length) {
            delay = this.options.pauseTime;
            this.isDeleting = true;
        } else if (this.isDeleting && this.charIndex === 0) {
            this.isDeleting = false;
            this.textIndex = (this.textIndex + 1) % this.texts.length;
            delay = 500;
        }

        setTimeout(() => this.type(), delay);
    }
}

// ===== TEXT SCRAMBLE EFFECT =====
class TextScramble {
    constructor(element) {
        this.element = element;
        this.chars = '!<>-_\\/[]{}—=+*^?#________';
        this.originalText = element.textContent;
        this.queue = [];
        this.frame = 0;
        this.frameRequest = null;
        this.resolve = null;
    }

    setText(newText) {
        const oldText = this.element.textContent;
        const length = Math.max(oldText.length, newText.length);
        const promise = new Promise(resolve => this.resolve = resolve);

        this.queue = [];
        for (let i = 0; i < length; i++) {
            const from = oldText[i] || '';
            const to = newText[i] || '';
            const start = Math.floor(Math.random() * 40);
            const end = start + Math.floor(Math.random() * 40);
            this.queue.push({ from, to, start, end });
        }

        cancelAnimationFrame(this.frameRequest);
        this.frame = 0;
        this.update();
        return promise;
    }

    update() {
        let output = '';
        let complete = 0;

        for (let i = 0; i < this.queue.length; i++) {
            let { from, to, start, end, char } = this.queue[i];

            if (this.frame >= end) {
                complete++;
                output += to;
            } else if (this.frame >= start) {
                if (!char || Math.random() < 0.28) {
                    char = this.randomChar();
                    this.queue[i].char = char;
                }
                output += `<span class="char scrambling">${char}</span>`;
            } else {
                output += from;
            }
        }

        this.element.innerHTML = output;

        if (complete === this.queue.length) {
            this.resolve();
        } else {
            this.frameRequest = requestAnimationFrame(() => this.update());
            this.frame++;
        }
    }

    randomChar() {
        return this.chars[Math.floor(Math.random() * this.chars.length)];
    }
}

// ===== PARTICLE MOUSE TRAIL =====
class ParticleTrail {
    constructor(options = {}) {
        this.options = {
            particleCount: options.particleCount || 10,
            colors: options.colors || ['#00d4ff', '#7c3aed', '#00ff88'],
            size: options.size || 8,
            fadeTime: options.fadeTime || 1000
        };

        this.particles = [];
        this.mouseX = 0;
        this.mouseY = 0;
        this.lastMouseX = 0;
        this.lastMouseY = 0;
        this.isMoving = false;
        this.moveTimeout = null;

        // Only enable on desktop
        if (window.innerWidth > 768 && !('ontouchstart' in window)) {
            this.init();
        }
    }

    init() {
        // Create container
        this.container = document.createElement('div');
        this.container.className = 'cursor-trail-container';
        document.body.appendChild(this.container);

        // Create particle pool
        for (let i = 0; i < this.options.particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle-trail';
            particle.style.width = this.options.size + 'px';
            particle.style.height = this.options.size + 'px';
            this.container.appendChild(particle);
            this.particles.push({
                element: particle,
                active: false
            });
        }

        // Mouse move listener
        document.addEventListener('mousemove', (e) => this.onMouseMove(e));
    }

    onMouseMove(e) {
        this.mouseX = e.clientX;
        this.mouseY = e.clientY;

        // Check if mouse actually moved
        const dx = this.mouseX - this.lastMouseX;
        const dy = this.mouseY - this.lastMouseY;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > 5) {
            this.spawnParticle();
            this.lastMouseX = this.mouseX;
            this.lastMouseY = this.mouseY;
        }
    }

    spawnParticle() {
        // Find inactive particle
        const particle = this.particles.find(p => !p.active);
        if (!particle) return;

        const color = this.options.colors[Math.floor(Math.random() * this.options.colors.length)];

        particle.element.style.left = this.mouseX + 'px';
        particle.element.style.top = this.mouseY + 'px';
        particle.element.style.background = `radial-gradient(circle, ${color}, transparent)`;
        particle.element.classList.add('active');
        particle.active = true;

        // Deactivate after animation
        setTimeout(() => {
            particle.element.classList.remove('active');
            particle.active = false;
        }, this.options.fadeTime);
    }
}

// ===== SPOTLIGHT EFFECT =====
class SpotlightEffect {
    constructor(selector = '.spotlight-container') {
        this.containers = document.querySelectorAll(selector);

        if (this.containers.length > 0) {
            this.init();
        }
    }

    init() {
        this.containers.forEach(container => {
            // Create spotlight element
            const spotlight = document.createElement('div');
            spotlight.className = 'spotlight';
            container.appendChild(spotlight);

            // Mouse move handler
            container.addEventListener('mousemove', (e) => {
                const rect = container.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                spotlight.style.left = x + 'px';
                spotlight.style.top = y + 'px';
            });
        });
    }
}

// ===== ENHANCED COUNTER =====
class EnhancedCounter {
    constructor() {
        this.counters = document.querySelectorAll('[data-counter]');
        this.observed = new Set();

        if (this.counters.length > 0) {
            this.init();
        }
    }

    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.observed.has(entry.target)) {
                    this.observed.add(entry.target);
                    this.animateCounter(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        this.counters.forEach(counter => observer.observe(counter));
    }

    animateCounter(element) {
        const target = parseInt(element.getAttribute('data-counter'), 10);
        const duration = parseInt(element.getAttribute('data-duration'), 10) || 2000;
        const prefix = element.getAttribute('data-prefix') || '';
        const suffix = element.getAttribute('data-suffix') || '';

        element.classList.add('counter-animated', 'counting');

        const startTime = performance.now();
        const startValue = 0;

        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out-cubic)
            const easeProgress = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(startValue + (target - startValue) * easeProgress);

            element.textContent = prefix + current.toLocaleString() + suffix;

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.classList.remove('counting');
                element.classList.add('counted');
            }
        };

        requestAnimationFrame(update);
    }
}

// ===== TEXT ROTATE =====
class TextRotate {
    constructor(element, texts, options = {}) {
        this.element = element;
        this.texts = texts;
        this.options = {
            interval: options.interval || 3000,
            animationDuration: options.animationDuration || 500
        };
        this.currentIndex = 0;

        if (this.element && this.texts.length > 0) {
            this.init();
        }
    }

    init() {
        this.element.classList.add('text-rotate-wrapper');

        // Create text items
        this.texts.forEach((text, index) => {
            const item = document.createElement('span');
            item.className = 'text-rotate-item';
            item.textContent = text;
            if (index === 0) item.classList.add('active');
            this.element.appendChild(item);
        });

        this.items = this.element.querySelectorAll('.text-rotate-item');

        // Start rotation
        setInterval(() => this.rotate(), this.options.interval);
    }

    rotate() {
        this.items[this.currentIndex].classList.remove('active');
        this.currentIndex = (this.currentIndex + 1) % this.items.length;
        this.items[this.currentIndex].classList.add('active');
    }
}

// ===== ANIMATED ICONS HANDLER =====
class AnimatedIcons {
    constructor() {
        this.init();
    }

    init() {
        // Add animation classes to feature icons
        document.querySelectorAll('.feature-icon i').forEach((icon, index) => {
            const animations = ['icon-bounce', 'icon-pulse', 'icon-float'];
            icon.classList.add('icon-animated', animations[index % animations.length]);
        });

        // Add hover effects
        document.querySelectorAll('[data-icon-animation]').forEach(icon => {
            const animation = icon.getAttribute('data-icon-animation');
            icon.classList.add('icon-animated', `icon-${animation}`);
        });
    }
}

// ===== INITIALIZE BATCH 3 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    // Initialize after a short delay to ensure DOM is ready
    setTimeout(() => {
        // Particle Trail - Disabled (too heavy)
        // new ParticleTrail({ ... });

        // Spotlight Effect on cards
        new SpotlightEffect('.feature-card, .pricing-card, .strategy-card');

        // Enhanced Counters
        new EnhancedCounter();

        // Animated Icons
        new AnimatedIcons();

        // DISABLED: TypewriterEffect on hero tagline
        // This was changing the original hero text - keep text static

        // DISABLED: TextScramble on section titles
        // This was making text unreadable on hover

        console.log('%c Batch 3 Loaded (Clean) ', 'background: #00ff88; color: #000; padding: 3px 8px;');
    }, 1000);
});

// ============================================================
// BATCH 4: ADVANCED INTERACTIVE COMPONENTS
// ============================================================

// ===== TESTIMONIAL CAROUSEL =====
class TestimonialCarousel {
    constructor(selector = '.testimonial-carousel') {
        this.carousel = document.querySelector(selector);
        if (!this.carousel) return;

        this.track = this.carousel.querySelector('.testimonial-track');
        this.slides = this.carousel.querySelectorAll('.testimonial-slide');
        this.currentIndex = 0;
        this.autoplayInterval = null;
        this.autoplayDelay = 5000;

        if (this.slides.length > 0) {
            this.init();
        }
    }

    init() {
        // Create navigation
        this.createNavigation();

        // Set initial state
        this.updateSlides();

        // Start autoplay
        this.startAutoplay();

        // Pause on hover
        this.carousel.addEventListener('mouseenter', () => this.stopAutoplay());
        this.carousel.addEventListener('mouseleave', () => this.startAutoplay());

        // Touch support
        this.addTouchSupport();
    }

    createNavigation() {
        // Navigation buttons
        const nav = document.createElement('div');
        nav.className = 'carousel-nav';
        nav.innerHTML = `
            <button class="carousel-btn prev"><i class="fas fa-chevron-left"></i></button>
            <button class="carousel-btn next"><i class="fas fa-chevron-right"></i></button>
        `;
        this.carousel.appendChild(nav);

        nav.querySelector('.prev').addEventListener('click', () => this.prev());
        nav.querySelector('.next').addEventListener('click', () => this.next());

        // Dots
        const dots = document.createElement('div');
        dots.className = 'carousel-dots';
        this.slides.forEach((_, index) => {
            const dot = document.createElement('div');
            dot.className = 'carousel-dot' + (index === 0 ? ' active' : '');
            dot.addEventListener('click', () => this.goTo(index));
            dots.appendChild(dot);
        });
        this.carousel.appendChild(dots);
        this.dots = dots.querySelectorAll('.carousel-dot');
    }

    updateSlides() {
        // Update slide classes
        this.slides.forEach((slide, index) => {
            slide.classList.remove('active', 'prev', 'next');
            if (index === this.currentIndex) {
                slide.classList.add('active');
            } else if (index === this.currentIndex - 1 ||
                      (this.currentIndex === 0 && index === this.slides.length - 1)) {
                slide.classList.add('prev');
            } else if (index === this.currentIndex + 1 ||
                      (this.currentIndex === this.slides.length - 1 && index === 0)) {
                slide.classList.add('next');
            }
        });

        // Update dots
        if (this.dots) {
            this.dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === this.currentIndex);
            });
        }

        // Move track
        const slideWidth = this.slides[0].offsetWidth + 30; // gap
        const offset = -this.currentIndex * slideWidth + (this.carousel.offsetWidth / 2) - (slideWidth / 2);
        if (this.track) {
            this.track.style.transform = `translateX(${offset}px)`;
        }
    }

    next() {
        this.currentIndex = (this.currentIndex + 1) % this.slides.length;
        this.updateSlides();
    }

    prev() {
        this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.updateSlides();
    }

    goTo(index) {
        this.currentIndex = index;
        this.updateSlides();
    }

    startAutoplay() {
        this.stopAutoplay();
        this.autoplayInterval = setInterval(() => this.next(), this.autoplayDelay);
    }

    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }

    addTouchSupport() {
        let startX = 0;
        let endX = 0;

        this.carousel.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            this.stopAutoplay();
        }, { passive: true });

        this.carousel.addEventListener('touchend', (e) => {
            endX = e.changedTouches[0].clientX;
            const diff = startX - endX;

            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    this.next();
                } else {
                    this.prev();
                }
            }
            this.startAutoplay();
        }, { passive: true });
    }
}

// ===== FAQ ACCORDION =====
class FAQAccordion {
    constructor(selector = '.faq-accordion') {
        this.container = document.querySelector(selector);
        if (!this.container) return;

        this.items = this.container.querySelectorAll('.faq-item');
        if (this.items.length > 0) {
            this.init();
        }
    }

    init() {
        this.items.forEach(item => {
            const question = item.querySelector('.faq-question');
            if (question) {
                question.addEventListener('click', () => this.toggle(item));
            }
        });

        // Open first item by default
        if (this.items.length > 0) {
            this.items[0].classList.add('active');
        }
    }

    toggle(item) {
        const isActive = item.classList.contains('active');

        // Close all items
        this.items.forEach(i => i.classList.remove('active'));

        // Open clicked item if it was closed
        if (!isActive) {
            item.classList.add('active');
        }
    }
}

// ===== ANIMATED TIMELINE =====
class AnimatedTimeline {
    constructor(selector = '.timeline') {
        this.timeline = document.querySelector(selector);
        if (!this.timeline) return;

        this.items = this.timeline.querySelectorAll('.timeline-item');
        if (this.items.length > 0) {
            this.init();
        }
    }

    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -50px 0px'
        });

        this.items.forEach(item => {
            observer.observe(item);
        });
    }
}

// ===== FLOATING DECORATIONS =====
class FloatingDecorations {
    constructor() {
        this.decoratedSections = document.querySelectorAll('.section-decorated');

        if (this.decoratedSections.length > 0) {
            this.init();
        }
    }

    init() {
        this.decoratedSections.forEach(section => {
            this.addDecorations(section);
        });
    }

    addDecorations(section) {
        const decorations = [
            { type: 'floating-circle', top: '10%', left: '-5%' },
            { type: 'floating-circle-2', top: '60%', right: '-8%' },
            { type: 'floating-square', top: '30%', right: '5%' },
            { type: 'floating-ring', bottom: '20%', left: '10%' },
            { type: 'floating-triangle', top: '70%', left: '5%' }
        ];

        decorations.forEach(deco => {
            const element = document.createElement('div');
            element.className = `floating-decoration ${deco.type}`;

            if (deco.top) element.style.top = deco.top;
            if (deco.bottom) element.style.bottom = deco.bottom;
            if (deco.left) element.style.left = deco.left;
            if (deco.right) element.style.right = deco.right;

            section.appendChild(element);
        });
    }
}

// ===== SKELETON LOADER =====
class SkeletonLoader {
    constructor() {
        this.skeletons = document.querySelectorAll('[data-skeleton]');
    }

    show(element) {
        element.classList.add('skeleton');
    }

    hide(element) {
        element.classList.remove('skeleton');
    }

    createSkeleton(type = 'card') {
        const skeleton = document.createElement('div');
        skeleton.className = `skeleton skeleton-${type}`;
        return skeleton;
    }

    // Replace content with skeleton while loading
    startLoading(container, type = 'card', count = 3) {
        container.dataset.originalContent = container.innerHTML;
        container.innerHTML = '';

        for (let i = 0; i < count; i++) {
            const skeleton = this.createSkeleton(type);
            container.appendChild(skeleton);
        }
    }

    // Restore original content
    finishLoading(container) {
        if (container.dataset.originalContent) {
            container.innerHTML = container.dataset.originalContent;
            delete container.dataset.originalContent;
        }
    }
}

// ===== PRICING TOGGLE =====
class PricingToggle {
    constructor(selector = '.pricing-toggle') {
        this.toggle = document.querySelector(selector);
        if (!this.toggle) return;

        this.switchBtn = this.toggle.querySelector('.pricing-toggle-switch');
        this.labels = this.toggle.querySelectorAll('.pricing-label');
        this.pricingCards = document.querySelectorAll('.pricing-card');
        this.isYearly = false;

        if (this.switchBtn) {
            this.init();
        }
    }

    init() {
        this.switchBtn.addEventListener('click', () => this.togglePricing());
    }

    togglePricing() {
        this.isYearly = !this.isYearly;
        this.switchBtn.classList.toggle('yearly', this.isYearly);

        // Update labels
        this.labels.forEach(label => {
            const isMonthly = label.dataset.period === 'monthly';
            label.classList.toggle('active', this.isYearly ? !isMonthly : isMonthly);
        });

        // Update prices
        this.pricingCards.forEach(card => {
            const priceElement = card.querySelector('.pricing-price');
            if (priceElement) {
                const monthly = priceElement.dataset.monthly;
                const yearly = priceElement.dataset.yearly;

                if (monthly && yearly) {
                    priceElement.classList.add('price-changing');
                    setTimeout(() => {
                        priceElement.textContent = this.isYearly ? yearly : monthly;
                        priceElement.classList.remove('price-changing');
                    }, 200);
                }
            }
        });
    }
}

// ===== COMPARISON TABLE HOVER =====
class ComparisonTable {
    constructor(selector = '.comparison-table') {
        this.table = document.querySelector(selector);
        if (!this.table) return;

        this.init();
    }

    init() {
        const rows = this.table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.querySelectorAll('td').forEach(cell => {
                    cell.style.transform = 'scale(1.02)';
                });
            });

            row.addEventListener('mouseleave', () => {
                row.querySelectorAll('td').forEach(cell => {
                    cell.style.transform = 'scale(1)';
                });
            });
        });
    }
}

// ===== INITIALIZE BATCH 4 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Testimonial Carousel
        new TestimonialCarousel();

        // FAQ Accordion
        new FAQAccordion();

        // Animated Timeline
        new AnimatedTimeline();

        // Floating Decorations
        new FloatingDecorations();

        // Pricing Toggle
        new PricingToggle();

        // Comparison Table
        new ComparisonTable();

        console.log('%c Batch 4 Premium Features Loaded ',
            'background: linear-gradient(135deg, #7c3aed, #00d4ff); color: #fff; padding: 5px 10px; font-size: 12px; border-radius: 3px;');
    }, 1200);
});

// ============================================================
// BATCH 5: ADVANCED VISUAL EFFECTS & ANIMATIONS
// ============================================================

// ===== SCROLL PROGRESS INDICATOR =====
class ScrollProgress {
    constructor() {
        this.createProgressBar();
        this.init();
    }

    createProgressBar() {
        this.progressBar = document.createElement('div');
        this.progressBar.className = 'scroll-progress';
        document.body.appendChild(this.progressBar);
    }

    init() {
        window.addEventListener('scroll', () => this.updateProgress(), { passive: true });
        this.updateProgress();
    }

    updateProgress() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = (scrollTop / docHeight) * 100;
        this.progressBar.style.width = `${progress}%`;
    }
}

// ===== MOUSE GLOW EFFECT =====
class MouseGlow {
    constructor() {
        if (window.innerWidth <= 768 || 'ontouchstart' in window) return;

        this.createGlow();
        this.init();
    }

    createGlow() {
        this.glow = document.createElement('div');
        this.glow.className = 'mouse-glow';
        document.body.appendChild(this.glow);
    }

    init() {
        document.addEventListener('mousemove', (e) => {
            this.glow.style.left = e.clientX + 'px';
            this.glow.style.top = e.clientY + 'px';
            this.glow.classList.add('active');
        });

        document.addEventListener('mouseleave', () => {
            this.glow.classList.remove('active');
        });
    }
}

// ===== PARALLAX SHAPES =====
class ParallaxShapes {
    constructor(containerSelector = '.parallax-container') {
        this.containers = document.querySelectorAll(containerSelector);
        if (this.containers.length === 0 || window.innerWidth <= 768) return;

        this.shapes = [
            { type: 'shape-circle', positions: [{ top: '10%', left: '5%' }, { top: '60%', right: '10%' }] },
            { type: 'shape-square', positions: [{ top: '30%', right: '5%' }, { bottom: '20%', left: '8%' }] },
            { type: 'shape-dot', positions: [{ top: '20%', left: '15%' }, { top: '70%', left: '20%' }, { top: '40%', right: '15%' }] },
            { type: 'shape-plus', positions: [{ top: '50%', left: '3%' }, { bottom: '30%', right: '5%' }] }
        ];

        this.init();
    }

    init() {
        this.containers.forEach(container => {
            this.addShapes(container);
        });

        window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
    }

    addShapes(container) {
        this.shapes.forEach(shape => {
            shape.positions.forEach(pos => {
                const element = document.createElement('div');
                element.className = `parallax-shape ${shape.type}`;
                element.dataset.speed = (Math.random() * 0.3 + 0.1).toFixed(2);

                Object.keys(pos).forEach(key => {
                    element.style[key] = pos[key];
                });

                container.appendChild(element);
            });
        });
    }

    handleScroll() {
        const shapes = document.querySelectorAll('.parallax-shape');
        const scrollY = window.scrollY;

        shapes.forEach(shape => {
            const speed = parseFloat(shape.dataset.speed) || 0.2;
            const yOffset = scrollY * speed;
            shape.style.transform = `translateY(${yOffset}px)`;
        });
    }
}

// ===== FLOATING ACTION MENU =====
class FloatingActionMenu {
    constructor() {
        this.createMenu();
        this.init();
    }

    createMenu() {
        const menu = document.createElement('div');
        menu.className = 'floating-action-menu';
        menu.innerHTML = `
            <button class="fab-main"><i class="fas fa-plus"></i></button>
            <div class="fab-options">
                <button class="fab-option" data-action="telegram">
                    <i class="fab fa-telegram"></i>
                    <span class="fab-option-tooltip">Telegram Support</span>
                </button>
                <button class="fab-option" data-action="calculator">
                    <i class="fas fa-calculator"></i>
                    <span class="fab-option-tooltip">Calculator</span>
                </button>
                <button class="fab-option" data-action="top">
                    <i class="fas fa-arrow-up"></i>
                    <span class="fab-option-tooltip">Back to Top</span>
                </button>
            </div>
        `;
        document.body.appendChild(menu);
        this.menu = menu;
        this.mainBtn = menu.querySelector('.fab-main');
    }

    init() {
        this.mainBtn.addEventListener('click', () => {
            this.menu.classList.toggle('active');
            this.mainBtn.classList.toggle('active');
        });

        this.menu.querySelectorAll('.fab-option').forEach(btn => {
            btn.addEventListener('click', () => this.handleAction(btn.dataset.action));
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.menu.contains(e.target)) {
                this.menu.classList.remove('active');
                this.mainBtn.classList.remove('active');
            }
        });
    }

    handleAction(action) {
        switch(action) {
            case 'telegram':
                const telegramLink = document.querySelector('a[href*="t.me"]');
                if (telegramLink) window.open(telegramLink.href, '_blank');
                break;
            case 'calculator':
                window.location.href = 'calculator.php';
                break;
            case 'top':
                window.scrollTo({ top: 0, behavior: 'smooth' });
                break;
        }
        this.menu.classList.remove('active');
        this.mainBtn.classList.remove('active');
    }
}

// ===== TOAST NOTIFICATION SYSTEM =====
class ToastNotification {
    constructor() {
        this.createContainer();
    }

    createContainer() {
        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    }

    show(options = {}) {
        const {
            title = 'Notification',
            message = '',
            type = 'info', // success, error, warning, info
            duration = 5000
        } = options;

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <div class="toast-icon ${type}">
                <i class="fas fa-${this.getIcon(type)}"></i>
            </div>
            <div class="toast-content">
                <h4>${title}</h4>
                <p>${message}</p>
            </div>
            <button class="toast-close"><i class="fas fa-times"></i></button>
            <div class="toast-progress" style="animation-duration: ${duration}ms;"></div>
        `;

        this.container.appendChild(toast);

        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            this.hide(toast);
        });

        // Auto hide
        setTimeout(() => {
            this.hide(toast);
        }, duration);

        return toast;
    }

    hide(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 400);
    }

    getIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// ===== REVEAL ON SCROLL (DUPLICATE REMOVED - Using original at line 776) =====

// ===== MORPHING BLOBS =====
class MorphingBlobs {
    constructor(containerSelector = '.hero-section, .cta-section') {
        this.containers = document.querySelectorAll(containerSelector);
        if (this.containers.length === 0 || window.innerWidth <= 768) return;

        this.init();
    }

    init() {
        this.containers.forEach(container => {
            // Only add if container has position relative/absolute
            const position = getComputedStyle(container).position;
            if (position === 'static') {
                container.style.position = 'relative';
            }

            this.addBlobs(container);
        });
    }

    addBlobs(container) {
        const blobsHTML = `
            <div class="morphing-blob morphing-blob-1"></div>
            <div class="morphing-blob morphing-blob-2"></div>
            <div class="morphing-blob morphing-blob-3"></div>
        `;
        container.insertAdjacentHTML('afterbegin', blobsHTML);
    }
}

// ===== ANIMATED GRADIENT BACKGROUND =====
class AnimatedGradientBg {
    constructor(selector = '.hero-section') {
        this.container = document.querySelector(selector);
        if (!this.container) return;

        this.init();
    }

    init() {
        // Add gradient background
        const gradientBg = document.createElement('div');
        gradientBg.className = 'hero-gradient-bg';
        this.container.insertAdjacentElement('afterbegin', gradientBg);

        // Add mesh gradient
        const meshGradient = document.createElement('div');
        meshGradient.className = 'mesh-gradient';
        this.container.insertAdjacentElement('afterbegin', meshGradient);
    }
}

// ===== TEXT SPLIT ANIMATION =====
class TextSplitAnimation {
    constructor(selector = '.split-text') {
        this.elements = document.querySelectorAll(selector);
        if (this.elements.length === 0) return;

        this.init();
    }

    init() {
        this.elements.forEach(element => {
            const text = element.textContent;
            element.innerHTML = '';

            text.split('').forEach((char, index) => {
                const span = document.createElement('span');
                span.className = 'char';
                span.style.animationDelay = `${index * 0.03}s`;
                span.textContent = char === ' ' ? '\u00A0' : char;
                element.appendChild(span);
            });
        });
    }
}

// ===== BUTTON RIPPLE EFFECT =====
class ButtonRipple {
    constructor(selector = '.btn-ripple') {
        this.buttons = document.querySelectorAll(selector);
        if (this.buttons.length === 0) return;

        this.init();
    }

    init() {
        this.buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                const ripple = document.createElement('span');
                ripple.className = 'ripple-effect';

                const rect = button.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                `;

                button.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });
    }
}

// ===== DEMO TRADING ANIMATION =====
class DemoTrading {
    constructor(selector = '.demo-preview-screen') {
        this.container = document.querySelector(selector);
        if (!this.container) return;

        this.init();
    }

    init() {
        this.createChart();
        this.startAnimation();
    }

    createChart() {
        const chartHTML = `
            <div class="demo-chart">
                <svg width="100%" height="200" viewBox="0 0 400 200" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="chartGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:#00d4ff;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#7c3aed;stop-opacity:1" />
                        </linearGradient>
                        <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#00d4ff;stop-opacity:0.3" />
                            <stop offset="100%" style="stop-color:#00d4ff;stop-opacity:0" />
                        </linearGradient>
                    </defs>
                    <path class="demo-chart-area" d="M0,150 Q50,120 100,130 T200,100 T300,110 T400,80 L400,200 L0,200 Z"></path>
                    <path class="demo-chart-line-path" d="M0,150 Q50,120 100,130 T200,100 T300,110 T400,80" fill="none" stroke="url(#chartGradient)" stroke-width="3"></path>
                </svg>
            </div>
            <div class="demo-controls">
                <button class="demo-btn demo-btn-call"><i class="fas fa-arrow-up"></i> CALL</button>
                <button class="demo-btn demo-btn-put"><i class="fas fa-arrow-down"></i> PUT</button>
            </div>
            <div class="demo-stats">
                <div class="demo-stat">
                    <div class="demo-stat-value" data-counter="85" data-suffix="%">0%</div>
                    <div class="demo-stat-label">Win Rate</div>
                </div>
                <div class="demo-stat">
                    <div class="demo-stat-value" data-counter="247" data-prefix="$">$0</div>
                    <div class="demo-stat-label">Profit Today</div>
                </div>
                <div class="demo-stat">
                    <div class="demo-stat-value" data-counter="15">0</div>
                    <div class="demo-stat-label">Trades</div>
                </div>
            </div>
        `;
        this.container.innerHTML = chartHTML;
    }

    startAnimation() {
        // Animate chart line
        const path = this.container.querySelector('.demo-chart-line-path');
        if (path) {
            const length = path.getTotalLength();
            path.style.strokeDasharray = length;
            path.style.strokeDashoffset = length;

            const animatePath = () => {
                path.style.transition = 'none';
                path.style.strokeDashoffset = length;

                requestAnimationFrame(() => {
                    path.style.transition = 'stroke-dashoffset 3s ease-out';
                    path.style.strokeDashoffset = '0';
                });
            };

            animatePath();
            setInterval(animatePath, 5000);
        }

        // Button click effects
        this.container.querySelectorAll('.demo-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    btn.style.transform = '';
                }, 150);

                // Show toast notification
                if (window.toastSystem) {
                    const isCall = btn.classList.contains('demo-btn-call');
                    window.toastSystem.show({
                        title: isCall ? 'CALL Trade Placed' : 'PUT Trade Placed',
                        message: 'Demo trade executed successfully!',
                        type: 'success',
                        duration: 3000
                    });
                }
            });
        });
    }
}

// ===== INITIALIZE BATCH 5 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Scroll Progress Indicator
        new ScrollProgress();

        // Mouse Glow Effect (desktop only)
        new MouseGlow();

        // Parallax Shapes
        new ParallaxShapes('.hero-section');

        // Floating Action Menu
        new FloatingActionMenu();

        // Toast Notification System (make global)
        window.toastSystem = new ToastNotification();

        // Reveal on Scroll
        new RevealOnScroll();

        // Morphing Blobs
        new MorphingBlobs();

        // Animated Gradient Background
        new AnimatedGradientBg();

        // Text Split Animation
        new TextSplitAnimation();

        // Button Ripple Effect
        new ButtonRipple();

        // Demo Trading (if exists)
        new DemoTrading();

        // Show welcome toast after 3 seconds
        setTimeout(() => {
            if (window.toastSystem && window.location.pathname === '/' || window.location.pathname.includes('index')) {
                window.toastSystem.show({
                    title: 'Welcome to ZYN Trade!',
                    message: 'Experience automated trading with AI-powered strategies.',
                    type: 'info',
                    duration: 6000
                });
            }
        }, 3000);

        console.log('%c Batch 5 Premium Features Loaded ',
            'background: linear-gradient(135deg, #00ff88, #7c3aed); color: #fff; padding: 5px 10px; font-size: 12px; border-radius: 3px;');
    }, 1500);
});

// ============================================================
// BATCH 6: FINAL POLISH & ADVANCED FEATURES
// ============================================================

// ===== THEME TOGGLE (Dark/Light Mode) =====
class ThemeToggle {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'dark';
        this.createToggle();
        this.init();
    }

    createToggle() {
        const toggle = document.createElement('div');
        toggle.className = 'theme-toggle';
        toggle.innerHTML = `
            <button class="theme-toggle-btn" title="Toggle Theme (T)">
                <i class="fas fa-sun icon-sun"></i>
                <i class="fas fa-moon icon-moon"></i>
            </button>
            <button class="theme-toggle-btn sound-toggle" title="Toggle Sound (M)">
                <i class="fas fa-volume-up icon-sound-on"></i>
                <i class="fas fa-volume-mute icon-sound-off"></i>
            </button>
        `;
        document.body.appendChild(toggle);
        this.toggleBtn = toggle.querySelector('.theme-toggle-btn:first-child');
        this.soundBtn = toggle.querySelector('.sound-toggle');
    }

    init() {
        // Apply saved theme
        document.documentElement.setAttribute('data-theme', this.theme);

        // Theme toggle click
        this.toggleBtn.addEventListener('click', () => this.toggle());

        // Sound toggle
        const soundEnabled = localStorage.getItem('soundEnabled') !== 'false';
        if (!soundEnabled) this.soundBtn.classList.add('muted');
        this.soundBtn.addEventListener('click', () => this.toggleSound());
    }

    toggle() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', this.theme);
        localStorage.setItem('theme', this.theme);

        // Play sound
        if (window.soundSystem) {
            window.soundSystem.play('switch');
        }

        // Show toast
        if (window.toastSystem) {
            window.toastSystem.show({
                title: `${this.theme === 'light' ? 'Light' : 'Dark'} Mode`,
                message: `Theme changed to ${this.theme} mode`,
                type: 'info',
                duration: 2000
            });
        }
    }

    toggleSound() {
        this.soundBtn.classList.toggle('muted');
        const enabled = !this.soundBtn.classList.contains('muted');
        localStorage.setItem('soundEnabled', enabled);

        if (window.soundSystem) {
            window.soundSystem.enabled = enabled;
            if (enabled) window.soundSystem.play('click');
        }
    }
}

// ===== SOUND EFFECTS SYSTEM =====
class SoundEffects {
    constructor() {
        this.enabled = localStorage.getItem('soundEnabled') !== 'false';
        this.sounds = {};
        this.audioContext = null;
        this.init();
    }

    init() {
        // Create audio context on first user interaction
        document.addEventListener('click', () => {
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
                this.createSounds();
            }
        }, { once: true });
    }

    createSounds() {
        // Generate simple sounds using Web Audio API
        this.sounds = {
            click: { freq: 800, duration: 0.05, type: 'sine' },
            hover: { freq: 600, duration: 0.03, type: 'sine' },
            success: { freq: [523, 659, 784], duration: 0.15, type: 'sine' },
            error: { freq: [300, 200], duration: 0.2, type: 'square' },
            switch: { freq: [400, 600], duration: 0.1, type: 'sine' },
            notification: { freq: [880, 1100], duration: 0.1, type: 'sine' }
        };
    }

    play(soundName) {
        if (!this.enabled || !this.audioContext || !this.sounds[soundName]) return;

        const sound = this.sounds[soundName];
        const frequencies = Array.isArray(sound.freq) ? sound.freq : [sound.freq];

        frequencies.forEach((freq, index) => {
            setTimeout(() => {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.value = freq;
                oscillator.type = sound.type;

                gainNode.gain.setValueAtTime(0.1, this.audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + sound.duration);

                oscillator.start();
                oscillator.stop(this.audioContext.currentTime + sound.duration);
            }, index * 100);
        });
    }
}

// ===== KEYBOARD SHORTCUTS =====
class KeyboardShortcuts {
    constructor() {
        this.shortcuts = [
            { key: 'h', desc: 'Go to Home', action: () => window.location.href = 'index.php' },
            { key: 'p', desc: 'Go to Pricing', action: () => window.location.href = 'pricing.php' },
            { key: 's', desc: 'Go to Strategies', action: () => window.location.href = 'strategies.php' },
            { key: 'c', desc: 'Go to Calculator', action: () => window.location.href = 'calculator.php' },
            { key: 't', desc: 'Toggle Theme', action: () => document.querySelector('.theme-toggle-btn')?.click() },
            { key: 'm', desc: 'Toggle Sound', action: () => document.querySelector('.sound-toggle')?.click() },
            { key: '/', desc: 'Open Command Palette', action: () => window.commandPalette?.open() },
            { key: '?', desc: 'Show Shortcuts', action: () => this.showModal() },
            { key: 'Escape', desc: 'Close Modals', action: () => this.closeAllModals() }
        ];

        this.createModal();
        this.init();
    }

    createModal() {
        const modal = document.createElement('div');
        modal.className = 'shortcuts-modal';
        modal.innerHTML = `
            <div class="shortcuts-content">
                <div class="shortcuts-header">
                    <h3><i class="fas fa-keyboard me-2"></i>Keyboard Shortcuts</h3>
                    <button class="shortcuts-close">&times;</button>
                </div>
                <div class="shortcuts-list">
                    ${this.shortcuts.map(s => `
                        <div class="shortcut-item">
                            <span class="shortcut-desc">${s.desc}</span>
                            <div class="shortcut-keys">
                                <span class="shortcut-key">${s.key === '?' ? 'Shift + /' : s.key.toUpperCase()}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        this.modal = modal;

        // Close button
        modal.querySelector('.shortcuts-close').addEventListener('click', () => this.hideModal());
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this.hideModal();
        });
    }

    init() {
        document.addEventListener('keydown', (e) => {
            // Don't trigger if typing in input
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            const key = e.key.toLowerCase();
            const shortcut = this.shortcuts.find(s => s.key.toLowerCase() === key);

            if (shortcut) {
                e.preventDefault();
                shortcut.action();
                if (window.soundSystem) window.soundSystem.play('click');
            }
        });
    }

    showModal() {
        this.modal.classList.add('active');
    }

    hideModal() {
        this.modal.classList.remove('active');
    }

    closeAllModals() {
        document.querySelectorAll('.shortcuts-modal, .command-palette').forEach(m => {
            m.classList.remove('active');
        });
    }
}

// ===== COMMAND PALETTE =====
class CommandPalette {
    constructor() {
        this.commands = [
            { icon: 'fa-home', title: 'Go to Home', desc: 'Navigate to homepage', action: () => window.location.href = 'index.php' },
            { icon: 'fa-dollar-sign', title: 'View Pricing', desc: 'Check our pricing plans', action: () => window.location.href = 'pricing.php' },
            { icon: 'fa-chess', title: 'View Strategies', desc: 'Explore trading strategies', action: () => window.location.href = 'strategies.php' },
            { icon: 'fa-calculator', title: 'Open Calculator', desc: 'Calculate profits & risk', action: () => window.location.href = 'calculator.php' },
            { icon: 'fa-user-plus', title: 'Register', desc: 'Create a new account', action: () => window.location.href = 'register.php' },
            { icon: 'fa-sign-in-alt', title: 'Login', desc: 'Sign in to your account', action: () => window.location.href = 'login.php' },
            { icon: 'fa-moon', title: 'Toggle Theme', desc: 'Switch dark/light mode', action: () => document.querySelector('.theme-toggle-btn')?.click() },
            { icon: 'fa-volume-up', title: 'Toggle Sound', desc: 'Enable/disable sounds', action: () => document.querySelector('.sound-toggle')?.click() },
            { icon: 'fa-arrow-up', title: 'Back to Top', desc: 'Scroll to top of page', action: () => window.scrollTo({ top: 0, behavior: 'smooth' }) },
            { icon: 'fa-keyboard', title: 'Show Shortcuts', desc: 'View keyboard shortcuts', action: () => window.keyboardShortcuts?.showModal() }
        ];

        this.selectedIndex = 0;
        this.filteredCommands = [...this.commands];
        this.createPalette();
        this.init();
    }

    createPalette() {
        const palette = document.createElement('div');
        palette.className = 'command-palette';
        palette.innerHTML = `
            <div class="command-palette-box">
                <div class="command-input-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" class="command-input" placeholder="Type a command or search...">
                    <span class="command-shortcut">ESC to close</span>
                </div>
                <div class="command-results"></div>
            </div>
        `;
        document.body.appendChild(palette);
        this.palette = palette;
        this.input = palette.querySelector('.command-input');
        this.results = palette.querySelector('.command-results');

        // Close on backdrop click
        palette.addEventListener('click', (e) => {
            if (e.target === palette) this.close();
        });

        // Input handling
        this.input.addEventListener('input', () => this.filterCommands());
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
    }

    init() {
        // Ctrl/Cmd + K to open
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                this.open();
            }
        });
    }

    open() {
        this.palette.classList.add('active');
        this.input.value = '';
        this.filterCommands();
        setTimeout(() => this.input.focus(), 100);
    }

    close() {
        this.palette.classList.remove('active');
    }

    filterCommands() {
        const query = this.input.value.toLowerCase();
        this.filteredCommands = this.commands.filter(cmd =>
            cmd.title.toLowerCase().includes(query) ||
            cmd.desc.toLowerCase().includes(query)
        );
        this.selectedIndex = 0;
        this.renderResults();
    }

    renderResults() {
        this.results.innerHTML = this.filteredCommands.map((cmd, index) => `
            <div class="command-item${index === this.selectedIndex ? ' selected' : ''}" data-index="${index}">
                <i class="fas ${cmd.icon}"></i>
                <div class="command-item-text">
                    <h4>${cmd.title}</h4>
                    <span>${cmd.desc}</span>
                </div>
            </div>
        `).join('');

        // Click handlers
        this.results.querySelectorAll('.command-item').forEach(item => {
            item.addEventListener('click', () => {
                const index = parseInt(item.dataset.index);
                this.executeCommand(index);
            });
        });
    }

    handleKeydown(e) {
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.filteredCommands.length - 1);
                this.renderResults();
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, 0);
                this.renderResults();
                break;
            case 'Enter':
                e.preventDefault();
                this.executeCommand(this.selectedIndex);
                break;
            case 'Escape':
                this.close();
                break;
        }
    }

    executeCommand(index) {
        if (this.filteredCommands[index]) {
            this.filteredCommands[index].action();
            this.close();
            if (window.soundSystem) window.soundSystem.play('click');
        }
    }
}

// ===== PAGE TRANSITIONS =====
class PageTransitions {
    constructor() {
        this.createTransition();
        this.init();
    }

    createTransition() {
        const transition = document.createElement('div');
        transition.className = 'page-transition';
        transition.innerHTML = `
            <div class="page-transition-panel"></div>
            <div class="page-transition-panel"></div>
            <div class="page-transition-panel"></div>
            <div class="page-transition-panel"></div>
        `;
        document.body.appendChild(transition);
        this.transition = transition;
    }

    init() {
        // Intercept internal links
        document.querySelectorAll('a[href]').forEach(link => {
            const href = link.getAttribute('href');
            // Only for internal links, not external or anchor links
            if (href && !href.startsWith('http') && !href.startsWith('#') && !href.startsWith('mailto') && !href.startsWith('tel')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.navigate(href);
                });
            }
        });
    }

    navigate(url) {
        this.transition.classList.add('active');
        if (window.soundSystem) window.soundSystem.play('switch');

        setTimeout(() => {
            window.location.href = url;
        }, 400);
    }
}

// ===== PWA INSTALL PROMPT =====
class PWAInstall {
    constructor() {
        this.deferredPrompt = null;
        this.createPrompt();
        this.init();
    }

    createPrompt() {
        const prompt = document.createElement('div');
        prompt.className = 'pwa-install-prompt';
        prompt.innerHTML = `
            <div class="pwa-install-icon">
                <i class="fas fa-download"></i>
            </div>
            <div class="pwa-install-text">
                <h4>Install ZYN Trade</h4>
                <p>Add to home screen for quick access</p>
            </div>
            <div class="pwa-install-actions">
                <button class="pwa-install-btn secondary" data-action="later">Later</button>
                <button class="pwa-install-btn primary" data-action="install">Install</button>
            </div>
        `;
        document.body.appendChild(prompt);
        this.prompt = prompt;

        prompt.querySelector('[data-action="install"]').addEventListener('click', () => this.install());
        prompt.querySelector('[data-action="later"]').addEventListener('click', () => this.hide());
    }

    init() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;

            // Show prompt after 30 seconds if not dismissed before
            const dismissed = localStorage.getItem('pwaPromptDismissed');
            if (!dismissed) {
                setTimeout(() => this.show(), 30000);
            }
        });

        // Hide if already installed
        window.addEventListener('appinstalled', () => {
            this.hide();
            if (window.toastSystem) {
                window.toastSystem.show({
                    title: 'App Installed!',
                    message: 'ZYN Trade has been added to your home screen',
                    type: 'success'
                });
            }
        });
    }

    show() {
        this.prompt.classList.add('show');
    }

    hide() {
        this.prompt.classList.remove('show');
        localStorage.setItem('pwaPromptDismissed', 'true');
    }

    async install() {
        if (!this.deferredPrompt) return;

        this.deferredPrompt.prompt();
        const { outcome } = await this.deferredPrompt.userChoice;

        if (outcome === 'accepted') {
            if (window.soundSystem) window.soundSystem.play('success');
        }

        this.deferredPrompt = null;
        this.hide();
    }
}

// ===== ANIMATION SEQUENCER =====
class AnimationSequencer {
    constructor() {
        this.sequences = document.querySelectorAll('.sequence-container');
        if (this.sequences.length === 0) return;

        this.init();
    }

    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2 });

        this.sequences.forEach(seq => observer.observe(seq));
    }
}

// ===== PERFORMANCE MONITOR =====
class PerformanceMonitor {
    constructor() {
        this.init();
    }

    init() {
        // Lazy load images
        this.lazyLoadImages();

        // Reduce animations for low-end devices
        this.checkPerformance();

        // Prefetch links on hover
        this.prefetchLinks();
    }

    lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    checkPerformance() {
        // Check if device prefers reduced motion
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.body.classList.add('reduce-motion');
        }

        // Check device memory (if available)
        if (navigator.deviceMemory && navigator.deviceMemory < 4) {
            document.body.classList.add('reduce-motion');
        }
    }

    prefetchLinks() {
        document.querySelectorAll('a[href]').forEach(link => {
            const href = link.getAttribute('href');
            if (href && href.endsWith('.php') && !href.startsWith('http')) {
                link.addEventListener('mouseenter', () => {
                    const prefetch = document.createElement('link');
                    prefetch.rel = 'prefetch';
                    prefetch.href = href;
                    document.head.appendChild(prefetch);
                }, { once: true });
            }
        });
    }
}

// ===== INTERACTIVE HINTS =====
class InteractiveHints {
    constructor() {
        this.hints = [
            { selector: '.theme-toggle-btn:first-child', message: 'Press T to toggle theme' },
            { selector: '.fab-main', message: 'Quick actions menu' },
            { selector: '.scroll-progress', message: 'Scroll progress indicator' }
        ];

        this.shown = new Set(JSON.parse(localStorage.getItem('hintsShown') || '[]'));
        this.init();
    }

    init() {
        // Show hints for first-time users
        if (this.shown.size < this.hints.length) {
            setTimeout(() => this.showNextHint(), 5000);
        }
    }

    showNextHint() {
        const hint = this.hints.find((h, i) => !this.shown.has(i));
        if (!hint) return;

        const element = document.querySelector(hint.selector);
        if (!element) return;

        const index = this.hints.indexOf(hint);
        this.shown.add(index);
        localStorage.setItem('hintsShown', JSON.stringify([...this.shown]));

        if (window.toastSystem) {
            window.toastSystem.show({
                title: 'Tip',
                message: hint.message,
                type: 'info',
                duration: 4000
            });
        }
    }
}

// ===== INITIALIZE BATCH 6 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Sound Effects System (initialize first)
        window.soundSystem = new SoundEffects();

        // Theme Toggle
        new ThemeToggle();

        // Keyboard Shortcuts
        window.keyboardShortcuts = new KeyboardShortcuts();

        // Command Palette
        window.commandPalette = new CommandPalette();

        // Page Transitions
        new PageTransitions();

        // PWA Install Prompt
        new PWAInstall();

        // Animation Sequencer
        new AnimationSequencer();

        // Performance Monitor
        new PerformanceMonitor();

        // Interactive Hints
        new InteractiveHints();

        console.log('%c Batch 6 Final Polish Loaded ',
            'background: linear-gradient(135deg, #ff6b6b, #ffd700); color: #000; padding: 5px 10px; font-size: 12px; border-radius: 3px;');
    }, 2000);
});

// ========================================
// BATCH 7: ULTIMATE LEGENDARY PREMIUM
// The Most Expensive & Perfect Features
// ========================================

// ===== AI CHAT WIDGET =====
class AIChatWidget {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.isTyping = false;
        this.responses = [
            "Saya siap membantu Anda! Ada yang bisa saya bantu dengan trading?",
            "Trading Plan Robot adalah solusi terbaik untuk trading otomatis Anda.",
            "Dengan AI kami, Anda bisa trading 24/7 tanpa perlu monitoring manual.",
            "Profit konsisten dengan manajemen risiko yang terukur!",
            "Sistem kami sudah digunakan oleh ribuan trader profesional.",
            "Mari saya jelaskan fitur unggulan robot trading kami.",
            "Anda bisa mulai dengan modal minimal dan scale up seiring profit.",
            "Tim support kami siap membantu 24 jam sehari, 7 hari seminggu."
        ];
        this.init();
    }

    init() {
        this.createWidget();
        this.bindEvents();
        this.addWelcomeMessage();
    }

    createWidget() {
        const widget = document.createElement('div');
        widget.className = 'ai-chat-widget';
        widget.innerHTML = `
            <button class="ai-chat-trigger" aria-label="Open AI Chat">
                <div class="ai-avatar">🤖</div>
            </button>
            <div class="ai-chat-window">
                <div class="ai-chat-header">
                    <div class="ai-chat-header-avatar">🤖</div>
                    <div class="ai-chat-header-info">
                        <h4>AI Trading Assistant</h4>
                        <span>● Online - Siap membantu</span>
                    </div>
                    <button class="ai-chat-close">✕</button>
                </div>
                <div class="ai-chat-messages"></div>
                <div class="ai-chat-input-container">
                    <div class="ai-chat-input-wrapper">
                        <input type="text" class="ai-chat-input" placeholder="Ketik pesan Anda..." />
                        <button class="ai-chat-send">➤</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(widget);

        this.widget = widget;
        this.trigger = widget.querySelector('.ai-chat-trigger');
        this.window = widget.querySelector('.ai-chat-window');
        this.messagesContainer = widget.querySelector('.ai-chat-messages');
        this.input = widget.querySelector('.ai-chat-input');
        this.sendBtn = widget.querySelector('.ai-chat-send');
        this.closeBtn = widget.querySelector('.ai-chat-close');
    }

    bindEvents() {
        this.trigger.addEventListener('click', () => this.toggle());
        this.closeBtn.addEventListener('click', () => this.close());
        this.sendBtn.addEventListener('click', () => this.sendMessage());
        this.input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    }

    toggle() {
        this.isOpen = !this.isOpen;
        this.window.classList.toggle('open', this.isOpen);
        if (this.isOpen) {
            this.input.focus();
            window.soundSystem?.play('pop');
        }
    }

    close() {
        this.isOpen = false;
        this.window.classList.remove('open');
    }

    addWelcomeMessage() {
        setTimeout(() => {
            this.addMessage('ai', 'Halo! 👋 Saya AI Trading Assistant. Ada yang bisa saya bantu tentang robot trading kami?');
        }, 500);
    }

    addMessage(type, content) {
        const messageEl = document.createElement('div');
        messageEl.className = `ai-message ${type}`;
        messageEl.innerHTML = `
            <div class="ai-message-avatar">${type === 'ai' ? '🤖' : '👤'}</div>
            <div class="ai-message-content">${content}</div>
        `;
        this.messagesContainer.appendChild(messageEl);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        this.messages.push({ type, content });
    }

    showTypingIndicator() {
        const typing = document.createElement('div');
        typing.className = 'ai-message ai-typing';
        typing.innerHTML = `
            <div class="ai-message-avatar">🤖</div>
            <div class="ai-typing-indicator">
                <span></span><span></span><span></span>
            </div>
        `;
        this.messagesContainer.appendChild(typing);
        this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        return typing;
    }

    sendMessage() {
        const text = this.input.value.trim();
        if (!text || this.isTyping) return;

        this.addMessage('user', text);
        this.input.value = '';
        this.isTyping = true;

        window.soundSystem?.play('click');

        const typing = this.showTypingIndicator();

        setTimeout(() => {
            typing.remove();
            const response = this.responses[Math.floor(Math.random() * this.responses.length)];
            this.addMessage('ai', response);
            this.isTyping = false;
            window.soundSystem?.play('success');
        }, 1500 + Math.random() * 1000);
    }
}

// ===== 3D CARD TILT EFFECT =====
class TiltEffect {
    constructor() {
        this.cards = [];
        this.init();
    }

    init() {
        const elements = document.querySelectorAll('.feature-card, .strategy-card, .pricing-card, .tilt-card');
        elements.forEach(el => {
            el.classList.add('tilt-card');
            this.addTiltEffect(el);
        });
    }

    addTiltEffect(element) {
        const glare = document.createElement('div');
        glare.className = 'tilt-glare';
        element.appendChild(glare);

        element.addEventListener('mousemove', (e) => {
            const rect = element.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;

            element.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;

            // Update glare position
            const glareX = (x / rect.width) * 100;
            const glareY = (y / rect.height) * 100;
            glare.style.background = `radial-gradient(circle at ${glareX}% ${glareY}%, rgba(255,255,255,0.3) 0%, transparent 60%)`;
            glare.style.opacity = '1';
        });

        element.addEventListener('mouseleave', () => {
            element.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
            glare.style.opacity = '0';
        });
    }
}

// ===== CONFETTI CELEBRATION =====
class ConfettiCelebration {
    constructor() {
        this.colors = ['#667eea', '#764ba2', '#f093fb', '#00d4ff', '#00ff88', '#ffd700', '#ff6b6b'];
        this.shapes = ['square', 'circle', 'ribbon'];
    }

    burst(x = window.innerWidth / 2, y = window.innerHeight / 2, count = 100) {
        const container = document.createElement('div');
        container.className = 'confetti-container';
        document.body.appendChild(container);

        for (let i = 0; i < count; i++) {
            setTimeout(() => {
                const piece = document.createElement('div');
                const shape = this.shapes[Math.floor(Math.random() * this.shapes.length)];
                piece.className = `confetti-piece ${shape}`;
                piece.style.left = `${x + (Math.random() - 0.5) * 200}px`;
                piece.style.backgroundColor = this.colors[Math.floor(Math.random() * this.colors.length)];
                piece.style.animationDuration = `${2 + Math.random() * 2}s`;
                piece.style.animationDelay = `${Math.random() * 0.5}s`;
                container.appendChild(piece);
            }, i * 10);
        }

        setTimeout(() => container.remove(), 5000);
    }

    rain(duration = 3000) {
        const container = document.createElement('div');
        container.className = 'confetti-container';
        document.body.appendChild(container);

        const interval = setInterval(() => {
            for (let i = 0; i < 5; i++) {
                const piece = document.createElement('div');
                const shape = this.shapes[Math.floor(Math.random() * this.shapes.length)];
                piece.className = `confetti-piece ${shape}`;
                piece.style.left = `${Math.random() * 100}%`;
                piece.style.backgroundColor = this.colors[Math.floor(Math.random() * this.colors.length)];
                piece.style.animationDuration = `${2 + Math.random() * 2}s`;
                container.appendChild(piece);
            }
        }, 100);

        setTimeout(() => {
            clearInterval(interval);
            setTimeout(() => container.remove(), 3000);
        }, duration);
    }
}

// ===== FIREWORKS EFFECT =====
class FireworksEffect {
    constructor() {
        this.colors = ['#667eea', '#764ba2', '#00d4ff', '#00ff88', '#ffd700', '#ff6b6b', '#f093fb'];
    }

    launch(x, y) {
        const container = document.createElement('div');
        container.className = 'fireworks-container';
        document.body.appendChild(container);

        const color = this.colors[Math.floor(Math.random() * this.colors.length)];
        const particleCount = 30;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'firework-particle';
            particle.style.backgroundColor = color;
            particle.style.left = `${x}px`;
            particle.style.top = `${y}px`;

            const angle = (i / particleCount) * Math.PI * 2;
            const velocity = 100 + Math.random() * 100;
            const endX = Math.cos(angle) * velocity;
            const endY = Math.sin(angle) * velocity;

            particle.style.setProperty('--end-x', `${endX}px`);
            particle.style.setProperty('--end-y', `${endY}px`);
            particle.animate([
                { transform: 'translate(0, 0) scale(1)', opacity: 1 },
                { transform: `translate(${endX}px, ${endY}px) scale(0)`, opacity: 0 }
            ], {
                duration: 1000 + Math.random() * 500,
                easing: 'cubic-bezier(0, 0.9, 0.57, 1)',
                fill: 'forwards'
            });

            container.appendChild(particle);
        }

        setTimeout(() => container.remove(), 2000);
    }

    show(count = 5) {
        for (let i = 0; i < count; i++) {
            setTimeout(() => {
                const x = Math.random() * window.innerWidth;
                const y = Math.random() * (window.innerHeight * 0.6);
                this.launch(x, y);
            }, i * 300);
        }
    }
}

// ===== DYNAMIC ISLAND NOTIFICATIONS =====
class DynamicIsland {
    constructor() {
        this.queue = [];
        this.isShowing = false;
        this.init();
    }

    init() {
        const island = document.createElement('div');
        island.className = 'dynamic-island';
        island.innerHTML = `<div class="dynamic-island-content"></div>`;
        document.body.appendChild(island);
        this.island = island;
        this.content = island.querySelector('.dynamic-island-content');
    }

    show(options) {
        const {
            icon = '🔔',
            title = 'Notification',
            message = '',
            type = 'info',
            duration = 4000,
            actions = [],
            large = false
        } = options;

        if (this.isShowing) {
            this.queue.push(options);
            return;
        }

        this.isShowing = true;
        this.island.classList.add('expanded');
        if (large) this.island.classList.add('large');

        let actionsHTML = '';
        if (actions.length > 0) {
            actionsHTML = `<div class="dynamic-island-actions">
                ${actions.map(a => `<button class="dynamic-island-btn ${a.type || 'secondary'}">${a.label}</button>`).join('')}
            </div>`;
        }

        this.content.innerHTML = `
            <div class="dynamic-island-icon ${type}">${icon}</div>
            <div class="dynamic-island-text">
                <h4>${title}</h4>
                ${message ? `<p>${message}</p>` : ''}
            </div>
            ${actionsHTML}
        `;

        // Bind action buttons
        actions.forEach((action, index) => {
            const btn = this.content.querySelectorAll('.dynamic-island-btn')[index];
            if (btn && action.onClick) {
                btn.addEventListener('click', () => {
                    action.onClick();
                    this.hide();
                });
            }
        });

        window.soundSystem?.play('notification');

        setTimeout(() => this.hide(), duration);
    }

    hide() {
        this.island.classList.remove('expanded', 'large');
        this.isShowing = false;

        setTimeout(() => {
            if (this.queue.length > 0) {
                this.show(this.queue.shift());
            }
        }, 500);
    }
}

// ===== VOICE COMMAND SYSTEM =====
class VoiceCommands {
    constructor() {
        this.isListening = false;
        this.recognition = null;
        this.commands = {
            'scroll down': () => window.scrollBy({ top: 500, behavior: 'smooth' }),
            'scroll up': () => window.scrollBy({ top: -500, behavior: 'smooth' }),
            'go to top': () => window.scrollTo({ top: 0, behavior: 'smooth' }),
            'go to bottom': () => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }),
            'dark mode': () => document.body.classList.add('light-mode'),
            'light mode': () => document.body.classList.remove('light-mode'),
            'open chat': () => document.querySelector('.ai-chat-trigger')?.click(),
            'close chat': () => document.querySelector('.ai-chat-close')?.click(),
            'celebrate': () => window.confetti?.burst(),
            'fireworks': () => window.fireworks?.show()
        };
        this.init();
    }

    init() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.log('Voice commands not supported');
            return;
        }

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.interimResults = true;
        this.recognition.lang = 'en-US';

        this.createIndicator();
        this.bindEvents();

        // Add keyboard shortcut to toggle voice
        document.addEventListener('keydown', (e) => {
            if (e.key === 'v' && e.altKey) {
                e.preventDefault();
                this.toggle();
            }
        });
    }

    createIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'voice-command-indicator';
        indicator.innerHTML = `
            <div class="voice-waves">
                <span></span><span></span><span></span><span></span><span></span>
            </div>
            <div class="voice-text">Listening... <span>say a command</span></div>
        `;
        document.body.appendChild(indicator);
        this.indicator = indicator;
    }

    bindEvents() {
        if (!this.recognition) return;

        this.recognition.onresult = (e) => {
            const transcript = Array.from(e.results)
                .map(result => result[0].transcript.toLowerCase())
                .join('');

            this.indicator.querySelector('.voice-text span').textContent = `"${transcript}"`;

            if (e.results[0].isFinal) {
                this.processCommand(transcript);
            }
        };

        this.recognition.onend = () => {
            if (this.isListening) {
                this.stop();
            }
        };

        this.recognition.onerror = () => {
            this.stop();
        };
    }

    processCommand(transcript) {
        for (const [command, action] of Object.entries(this.commands)) {
            if (transcript.includes(command)) {
                action();
                window.dynamicIsland?.show({
                    icon: '🎤',
                    title: 'Voice Command',
                    message: `Executed: "${command}"`,
                    type: 'success',
                    duration: 2000
                });
                break;
            }
        }
    }

    toggle() {
        if (this.isListening) {
            this.stop();
        } else {
            this.start();
        }
    }

    start() {
        if (!this.recognition) return;
        this.isListening = true;
        this.indicator.classList.add('active');
        this.recognition.start();
        window.soundSystem?.play('pop');
    }

    stop() {
        if (!this.recognition) return;
        this.isListening = false;
        this.indicator.classList.remove('active');
        try {
            this.recognition.stop();
        } catch (e) {}
    }
}

// ===== CURSOR MAGIC TRAIL =====
class CursorMagicTrail {
    constructor() {
        this.particles = [];
        this.lastX = 0;
        this.lastY = 0;
        this.colors = ['#667eea', '#764ba2', '#f093fb', '#00d4ff', '#00ff88'];
        this.init();
    }

    init() {
        // Only on desktop
        if (window.innerWidth < 768) return;

        const container = document.createElement('div');
        container.className = 'cursor-trail';
        document.body.appendChild(container);
        this.container = container;

        document.addEventListener('mousemove', (e) => this.onMove(e));

        // Add sparkle on click
        document.addEventListener('click', (e) => this.sparkle(e.clientX, e.clientY));
    }

    onMove(e) {
        const x = e.clientX;
        const y = e.clientY;

        // Calculate distance
        const dx = x - this.lastX;
        const dy = y - this.lastY;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > 10) {
            this.createParticle(x, y);
            this.lastX = x;
            this.lastY = y;
        }
    }

    createParticle(x, y) {
        const particle = document.createElement('div');
        particle.className = 'cursor-trail-particle';
        particle.style.left = `${x}px`;
        particle.style.top = `${y}px`;
        particle.style.backgroundColor = this.colors[Math.floor(Math.random() * this.colors.length)];

        this.container.appendChild(particle);

        setTimeout(() => particle.remove(), 1000);
    }

    sparkle(x, y) {
        for (let i = 0; i < 4; i++) {
            const sparkle = document.createElement('div');
            sparkle.className = 'cursor-sparkle';
            sparkle.style.left = `${x + (Math.random() - 0.5) * 30}px`;
            sparkle.style.top = `${y + (Math.random() - 0.5) * 30}px`;
            this.container.appendChild(sparkle);
            setTimeout(() => sparkle.remove(), 600);
        }
    }
}

// ===== HAPTIC FEEDBACK =====
class HapticFeedback {
    constructor() {
        this.init();
    }

    init() {
        if (!('vibrate' in navigator)) return;

        // Add haptic to buttons
        document.querySelectorAll('button, .btn, a').forEach(el => {
            el.addEventListener('click', () => this.light());
        });
    }

    light() {
        navigator.vibrate?.(10);
    }

    medium() {
        navigator.vibrate?.(25);
    }

    heavy() {
        navigator.vibrate?.(50);
    }

    pattern(pattern) {
        navigator.vibrate?.(pattern);
    }

    success() {
        navigator.vibrate?.([10, 50, 10]);
    }

    error() {
        navigator.vibrate?.([50, 30, 50, 30, 50]);
    }
}

// ===== HOLOGRAPHIC EFFECT ENHANCER =====
class HolographicEnhancer {
    constructor() {
        this.init();
    }

    init() {
        // Add holographic effect to hero title
        const heroTitle = document.querySelector('.hero h1');
        if (heroTitle) {
            heroTitle.classList.add('holographic');
        }

        // Add holographic cards to pricing
        document.querySelectorAll('.pricing-card.featured').forEach(card => {
            card.classList.add('holographic-card', 'iridescent-border');
        });
    }
}

// ===== LIQUID BACKGROUND =====
class LiquidBackground {
    constructor() {
        this.init();
    }

    init() {
        const hero = document.querySelector('.hero');
        if (!hero) return;

        // Create liquid blobs
        for (let i = 0; i < 3; i++) {
            const blob = document.createElement('div');
            blob.className = 'liquid-blob';
            blob.style.width = `${200 + Math.random() * 200}px`;
            blob.style.height = `${200 + Math.random() * 200}px`;
            blob.style.left = `${Math.random() * 100}%`;
            blob.style.top = `${Math.random() * 100}%`;
            blob.style.animationDelay = `${i * 2}s`;
            hero.appendChild(blob);
        }
    }
}

// ===== SCROLL CELEBRATIONS =====
class ScrollCelebrations {
    constructor() {
        this.milestones = [25, 50, 75, 100];
        this.reached = new Set();
        this.init();
    }

    init() {
        window.addEventListener('scroll', () => this.checkMilestone());
    }

    checkMilestone() {
        const scrollPercent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;

        this.milestones.forEach(milestone => {
            if (scrollPercent >= milestone && !this.reached.has(milestone)) {
                this.reached.add(milestone);
                this.celebrate(milestone);
            }
        });
    }

    celebrate(milestone) {
        if (milestone === 100) {
            window.fireworks?.show(3);
            window.dynamicIsland?.show({
                icon: '🎉',
                title: 'Congratulations!',
                message: 'You explored the entire page!',
                type: 'success',
                duration: 3000
            });
        }
    }
}

// ===== EASTER EGGS =====
class EasterEggs {
    constructor() {
        this.konamiCode = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];
        this.konamiIndex = 0;
        this.init();
    }

    init() {
        document.addEventListener('keydown', (e) => this.checkKonami(e));
    }

    checkKonami(e) {
        if (e.key === this.konamiCode[this.konamiIndex]) {
            this.konamiIndex++;
            if (this.konamiIndex === this.konamiCode.length) {
                this.triggerKonami();
                this.konamiIndex = 0;
            }
        } else {
            this.konamiIndex = 0;
        }
    }

    triggerKonami() {
        window.confetti?.rain(5000);
        window.fireworks?.show(10);
        window.dynamicIsland?.show({
            icon: '🎮',
            title: 'KONAMI CODE!',
            message: 'You found the secret! Enjoy the celebration!',
            type: 'success',
            duration: 5000,
            large: true
        });
        window.soundSystem?.play('success');
    }
}

// ===== PREMIUM WELCOME ANIMATION =====
class PremiumWelcome {
    constructor() {
        this.init();
    }

    init() {
        // Show welcome notification after page load
        setTimeout(() => {
            window.dynamicIsland?.show({
                icon: '👋',
                title: 'Welcome to Trading Plan Robot!',
                message: 'Explore our premium features',
                type: 'info',
                duration: 4000,
                actions: [
                    {
                        label: 'Start Tour',
                        type: 'primary',
                        onClick: () => this.startTour()
                    }
                ]
            });
        }, 3000);
    }

    startTour() {
        const sections = ['Features', 'Strategies', 'Pricing'];
        sections.forEach((section, index) => {
            setTimeout(() => {
                const el = document.querySelector(`#${section.toLowerCase()}`);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                    window.dynamicIsland?.show({
                        icon: ['✨', '📊', '💰'][index],
                        title: section,
                        message: `Discover our ${section.toLowerCase()}`,
                        type: 'info',
                        duration: 2500
                    });
                }
            }, index * 3000);
        });
    }
}

// ===== NEON MODE =====
class NeonMode {
    constructor() {
        this.isActive = false;
        this.init();
    }

    init() {
        // Toggle with 'N' key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'n' && !e.ctrlKey && !e.metaKey && !e.altKey) {
                const target = e.target;
                if (target.tagName === 'INPUT' || target.tagName === 'TEXTAREA') return;
                this.toggle();
            }
        });
    }

    toggle() {
        this.isActive = !this.isActive;

        if (this.isActive) {
            document.body.classList.add('neon-mode');
            document.querySelectorAll('.btn-primary').forEach(btn => {
                btn.classList.add('neon-border');
            });
            window.dynamicIsland?.show({
                icon: '💜',
                title: 'Neon Mode Activated',
                message: 'Press N again to disable',
                type: 'info',
                duration: 2000
            });
        } else {
            document.body.classList.remove('neon-mode');
            document.querySelectorAll('.neon-border').forEach(el => {
                el.classList.remove('neon-border');
            });
        }

        window.soundSystem?.play('mode');
    }
}

// ===== SMART SCROLL DIRECTION =====
class SmartScrollDirection {
    constructor() {
        this.lastScroll = 0;
        this.header = document.querySelector('.navbar');
        this.init();
    }

    init() {
        if (!this.header) return;

        window.addEventListener('scroll', () => {
            const currentScroll = window.scrollY;

            if (currentScroll > this.lastScroll && currentScroll > 100) {
                this.header.style.transform = 'translateY(-100%)';
            } else {
                this.header.style.transform = 'translateY(0)';
            }

            this.lastScroll = currentScroll;
        });

        this.header.style.transition = 'transform 0.3s ease';
    }
}

// ===== INITIALIZE BATCH 7 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Core Features - Keep only essential
        window.aiChat = new AIChatWidget();
        new TiltEffect();
        // HolographicEnhancer and LiquidBackground disabled - too heavy

        // Celebrations - Available but not auto-triggered
        window.confetti = new ConfettiCelebration();

        // Notifications - Dynamic Island is nice for notifications
        window.dynamicIsland = new DynamicIsland();

        // Interactions - All disabled (mic permission, cursor conflicts, vibration)
        // VoiceCommands, CursorMagicTrail, HapticFeedback disabled

        // Enhancements - All disabled (too distracting)
        // ScrollCelebrations, EasterEggs, NeonMode, SmartScrollDirection disabled

        // PremiumWelcome disabled - annoying popup

        console.log('%c Batch 7 Loaded (Clean) ', 'background: #7928ca; color: #fff; padding: 3px 8px;');
    }, 2500);
});

// ========================================
// BATCH 8: BEYOND LEGENDARY - ULTIMATE PERFECTION
// The Absolute Peak of Premium Web Design
// ========================================

// ===== LIVE TRADING ACTIVITY TICKER =====
class LiveActivityTicker {
    constructor() {
        this.activities = [
            { user: 'Andi S.', action: 'profit', amount: '+$2,450', pair: 'EUR/USD' },
            { user: 'Budi K.', action: 'profit', amount: '+$1,890', pair: 'GBP/USD' },
            { user: 'Citra R.', action: 'profit', amount: '+$3,200', pair: 'XAU/USD' },
            { user: 'Dewi M.', action: 'profit', amount: '+$980', pair: 'USD/JPY' },
            { user: 'Erik P.', action: 'profit', amount: '+$4,100', pair: 'BTC/USD' },
            { user: 'Fajar H.', action: 'profit', amount: '+$1,560', pair: 'EUR/GBP' },
            { user: 'Gita L.', action: 'profit', amount: '+$2,780', pair: 'AUD/USD' },
            { user: 'Hendra W.', action: 'profit', amount: '+$5,320', pair: 'XAU/USD' },
            { user: 'Indah N.', action: 'profit', amount: '+$1,200', pair: 'NZD/USD' },
            { user: 'Joko T.', action: 'profit', amount: '+$3,890', pair: 'USD/CHF' },
            { user: 'Kartika S.', action: 'joined', amount: '', pair: '' },
            { user: 'Lukman A.', action: 'profit', amount: '+$2,100', pair: 'GBP/JPY' },
        ];
        this.emojis = ['👤', '💼', '🎯', '💎', '🚀', '⭐', '👨‍💻', '👩‍💼', '🔥', '💪'];
        this.init();
    }

    init() {
        const ticker = document.createElement('div');
        ticker.className = 'live-activity-ticker';
        ticker.innerHTML = `
            <div class="ticker-live-badge">LIVE</div>
            <div class="ticker-track"></div>
        `;
        document.body.appendChild(ticker);

        this.track = ticker.querySelector('.ticker-track');
        this.populateTicker();
    }

    populateTicker() {
        // Duplicate items for seamless loop
        const items = [...this.activities, ...this.activities];

        items.forEach((activity, index) => {
            const item = document.createElement('div');
            item.className = 'ticker-item';

            const emoji = this.emojis[index % this.emojis.length];
            const timeAgo = this.getRandomTime();

            let actionText = '';
            if (activity.action === 'profit') {
                actionText = `Earned <span class="profit">${activity.amount}</span> on ${activity.pair}`;
            } else if (activity.action === 'joined') {
                actionText = 'Just joined the platform! 🎉';
            }

            item.innerHTML = `
                <div class="ticker-avatar">${emoji}</div>
                <div class="ticker-content">
                    <span class="ticker-user">${activity.user}</span>
                    <span class="ticker-action">${actionText}</span>
                </div>
                <span class="ticker-time">${timeAgo}</span>
            `;

            this.track.appendChild(item);
        });
    }

    getRandomTime() {
        const times = ['Just now', '2m ago', '5m ago', '8m ago', '12m ago', '15m ago', '20m ago', '25m ago'];
        return times[Math.floor(Math.random() * times.length)];
    }
}

// ===== PARTICLE NETWORK BACKGROUND =====
class ParticleNetwork {
    constructor() {
        this.particles = [];
        this.connections = [];
        this.mouseX = 0;
        this.mouseY = 0;
        this.init();
    }

    init() {
        const container = document.createElement('div');
        container.className = 'particle-network';

        this.canvas = document.createElement('canvas');
        container.appendChild(this.canvas);
        document.body.appendChild(container);

        this.ctx = this.canvas.getContext('2d');
        this.resize();

        window.addEventListener('resize', () => this.resize());
        document.addEventListener('mousemove', (e) => {
            this.mouseX = e.clientX;
            this.mouseY = e.clientY;
        });

        this.createParticles();
        this.animate();
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
    }

    createParticles() {
        const particleCount = Math.min(80, Math.floor((window.innerWidth * window.innerHeight) / 15000));

        for (let i = 0; i < particleCount; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                radius: Math.random() * 2 + 1,
                color: `rgba(102, 126, 234, ${Math.random() * 0.5 + 0.3})`
            });
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Update and draw particles
        this.particles.forEach((particle, i) => {
            // Move particles
            particle.x += particle.vx;
            particle.y += particle.vy;

            // Bounce off edges
            if (particle.x < 0 || particle.x > this.canvas.width) particle.vx *= -1;
            if (particle.y < 0 || particle.y > this.canvas.height) particle.vy *= -1;

            // Draw particle
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            this.ctx.fillStyle = particle.color;
            this.ctx.fill();

            // Connect nearby particles
            this.particles.slice(i + 1).forEach(other => {
                const dx = particle.x - other.x;
                const dy = particle.y - other.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 150) {
                    this.ctx.beginPath();
                    this.ctx.moveTo(particle.x, particle.y);
                    this.ctx.lineTo(other.x, other.y);
                    this.ctx.strokeStyle = `rgba(102, 126, 234, ${0.2 * (1 - distance / 150)})`;
                    this.ctx.stroke();
                }
            });

            // Connect to mouse
            const mouseDistance = Math.sqrt(
                Math.pow(particle.x - this.mouseX, 2) +
                Math.pow(particle.y - this.mouseY, 2)
            );

            if (mouseDistance < 200) {
                this.ctx.beginPath();
                this.ctx.moveTo(particle.x, particle.y);
                this.ctx.lineTo(this.mouseX, this.mouseY);
                this.ctx.strokeStyle = `rgba(118, 75, 162, ${0.3 * (1 - mouseDistance / 200)})`;
                this.ctx.stroke();
            }
        });

        requestAnimationFrame(() => this.animate());
    }
}

// ===== ANIMATED STATS COUNTER =====
class AnimatedStatsCounter {
    constructor() {
        this.stats = [
            { icon: '👥', value: 15847, label: 'Active Traders', change: '+12%' },
            { icon: '💰', value: 2847650, label: 'Total Profit', prefix: '$', change: '+28%' },
            { icon: '📈', value: 94.7, label: 'Success Rate', suffix: '%', change: '+5%' },
            { icon: '🌍', value: 45, label: 'Countries', change: '+3' }
        ];
        this.observed = false;
        this.init();
    }

    init() {
        // Find a suitable container or create dashboard
        const container = document.querySelector('.stats-section') || this.createDashboard();
        if (!container) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.observed) {
                    this.observed = true;
                    this.animateCounters();
                }
            });
        }, { threshold: 0.3 });

        observer.observe(container);
    }

    createDashboard() {
        const heroSection = document.querySelector('.hero');
        if (!heroSection) return null;

        // We'll add stats to existing trust badges or create new section
        return heroSection;
    }

    animateCounters() {
        document.querySelectorAll('.stat-value[data-target]').forEach(el => {
            const target = parseFloat(el.dataset.target);
            const prefix = el.dataset.prefix || '';
            const suffix = el.dataset.suffix || '';
            const duration = 2000;
            const startTime = performance.now();

            const animate = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const easeProgress = 1 - Math.pow(1 - progress, 3); // Ease out cubic

                const currentValue = target * easeProgress;
                el.textContent = prefix + this.formatNumber(currentValue, target) + suffix;
                el.classList.add('counting');

                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    el.classList.remove('counting');
                }
            };

            requestAnimationFrame(animate);
        });
    }

    formatNumber(value, target) {
        if (target >= 1000000) {
            return (value / 1000000).toFixed(2) + 'M';
        } else if (target >= 1000) {
            return Math.floor(value).toLocaleString();
        } else if (target % 1 !== 0) {
            return value.toFixed(1);
        }
        return Math.floor(value).toString();
    }
}

// ===== MAGNETIC CURSOR EFFECT =====
class MagneticCursor {
    constructor() {
        this.elements = [];
        this.init();
    }

    init() {
        // Apply to buttons and interactive elements
        const selectors = '.btn-primary, .btn-secondary, .nav-link, .magnetic-element';
        document.querySelectorAll(selectors).forEach(el => {
            this.addMagneticEffect(el);
        });
    }

    addMagneticEffect(element) {
        const strength = 0.3;
        const triggerArea = 100;

        element.addEventListener('mousemove', (e) => {
            const rect = element.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            const deltaX = e.clientX - centerX;
            const deltaY = e.clientY - centerY;
            const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

            if (distance < triggerArea) {
                const pull = (1 - distance / triggerArea) * strength;
                element.style.transform = `translate(${deltaX * pull}px, ${deltaY * pull}px)`;
                element.classList.add('attracted');
            }
        });

        element.addEventListener('mouseleave', () => {
            element.style.transform = 'translate(0, 0)';
            element.classList.remove('attracted');
        });
    }
}

// ===== INTERACTIVE GLOBE =====
class InteractiveGlobe {
    constructor() {
        this.points = [];
        this.init();
    }

    init() {
        // Create globe container if a suitable section exists
        const heroSection = document.querySelector('.hero');
        if (!heroSection) return;

        // Add points for major trading centers
        this.tradingCenters = [
            { name: 'New York', lat: 40.7, lng: -74 },
            { name: 'London', lat: 51.5, lng: -0.1 },
            { name: 'Tokyo', lat: 35.7, lng: 139.7 },
            { name: 'Singapore', lat: 1.3, lng: 103.8 },
            { name: 'Sydney', lat: -33.9, lng: 151.2 },
            { name: 'Frankfurt', lat: 50.1, lng: 8.7 },
            { name: 'Hong Kong', lat: 22.3, lng: 114.2 },
            { name: 'Jakarta', lat: -6.2, lng: 106.8 }
        ];
    }
}

// ===== NOISE TEXTURE OVERLAY (DUPLICATE REMOVED - Using original at line 803) =====

// ===== GRADIENT MESH ENHANCER =====
class GradientMeshEnhancer {
    constructor() {
        this.init();
    }

    init() {
        const sections = document.querySelectorAll('.hero, #features, #pricing');
        sections.forEach(section => {
            if (!section.querySelector('.gradient-mesh')) {
                const mesh = document.createElement('div');
                mesh.className = 'gradient-mesh';
                section.style.position = 'relative';
                section.insertBefore(mesh, section.firstChild);
            }
        });
    }
}

// ===== MARQUEE TEXT =====
class MarqueeText {
    constructor() {
        this.texts = ['AUTOMATED TRADING', 'AI POWERED', 'PROFIT MAKER', '24/7 SUPPORT', 'SECURE PLATFORM'];
        this.init();
    }

    init() {
        // Find suitable location for marquee
        const pricingSection = document.querySelector('#pricing');
        if (!pricingSection) return;

        const marquee = document.createElement('div');
        marquee.className = 'marquee-container';
        marquee.style.marginTop = '60px';
        marquee.style.marginBottom = '-20px';

        const content = document.createElement('div');
        content.className = 'marquee-content';

        // Double the content for seamless loop
        const texts = [...this.texts, ...this.texts];
        texts.forEach(text => {
            const span = document.createElement('span');
            span.textContent = text;
            content.appendChild(span);
        });

        marquee.appendChild(content);
        pricingSection.parentNode.insertBefore(marquee, pricingSection);
    }
}

// ===== STAGGER ANIMATIONS =====
class StaggerAnimations {
    constructor() {
        this.init();
    }

    init() {
        const containers = document.querySelectorAll('.features-grid, .strategies-grid, .pricing-cards');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('stagger-fade');
                }
            });
        }, { threshold: 0.2 });

        containers.forEach(container => observer.observe(container));
    }
}

// ===== GLOW CARD ENHANCER =====
class GlowCardEnhancer {
    constructor() {
        this.init();
    }

    init() {
        const cards = document.querySelectorAll('.feature-card, .strategy-card');
        cards.forEach(card => {
            card.classList.add('glow-card');
        });
    }
}

// ===== TEXT GRADIENT WAVE =====
class TextGradientWave {
    constructor() {
        this.init();
    }

    init() {
        const sectionTitles = document.querySelectorAll('.section-title, h2');
        sectionTitles.forEach(title => {
            if (!title.classList.contains('text-gradient-wave')) {
                title.classList.add('text-gradient-wave');
            }
        });
    }
}

// ===== BUTTON ENHANCER =====
class ButtonEnhancer {
    constructor() {
        this.init();
    }

    init() {
        const primaryBtns = document.querySelectorAll('.btn-primary');
        primaryBtns.forEach(btn => {
            btn.classList.add('btn-shine', 'btn-elastic');
        });

        const secondaryBtns = document.querySelectorAll('.btn-secondary');
        secondaryBtns.forEach(btn => {
            btn.classList.add('btn-liquid');
        });
    }
}

// ===== LIVE PROFIT NOTIFICATIONS =====
class LiveProfitNotifications {
    constructor() {
        this.profits = [
            { user: 'Andi S.', amount: '+$2,450', pair: 'EUR/USD' },
            { user: 'Budi K.', amount: '+$1,890', pair: 'GBP/USD' },
            { user: 'Citra R.', amount: '+$3,200', pair: 'XAU/USD' },
            { user: 'Dewi M.', amount: '+$980', pair: 'USD/JPY' },
            { user: 'Erik P.', amount: '+$4,100', pair: 'BTC/USD' },
        ];
        this.init();
    }

    init() {
        // Show random profit notification every 30-60 seconds
        this.scheduleNotification();
    }

    scheduleNotification() {
        const delay = 30000 + Math.random() * 30000; // 30-60 seconds

        setTimeout(() => {
            this.showNotification();
            this.scheduleNotification();
        }, delay);
    }

    showNotification() {
        const profit = this.profits[Math.floor(Math.random() * this.profits.length)];

        window.dynamicIsland?.show({
            icon: '💰',
            title: `${profit.user} just earned!`,
            message: `${profit.amount} profit on ${profit.pair}`,
            type: 'success',
            duration: 4000
        });
    }
}

// ===== SCROLL PROGRESS SECTIONS =====
class ScrollProgressSections {
    constructor() {
        this.sections = [];
        this.init();
    }

    init() {
        const allSections = document.querySelectorAll('section, .hero');

        allSections.forEach((section, index) => {
            this.sections.push({
                element: section,
                name: section.id || `section-${index}`
            });
        });
    }
}

// ===== SMOOTH SCROLL ENHANCER =====
class SmoothScrollEnhancer {
    constructor() {
        this.init();
    }

    init() {
        // Add smooth scroll to all anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                const targetId = anchor.getAttribute('href');
                if (targetId === '#') return;

                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });

                    // Update URL without jump
                    history.pushState(null, null, targetId);
                }
            });
        });
    }
}

// ===== TYPING EFFECT V2 =====
class TypingEffectV2 {
    constructor() {
        this.init();
    }

    init() {
        const elements = document.querySelectorAll('[data-typing]');

        elements.forEach(el => {
            const text = el.textContent;
            el.textContent = '';
            el.style.visibility = 'visible';

            this.typeText(el, text);
        });
    }

    typeText(element, text, index = 0) {
        if (index < text.length) {
            element.textContent += text.charAt(index);
            setTimeout(() => this.typeText(element, text, index + 1), 50);
        }
    }
}

// ===== PREMIUM BADGE ANIMATOR =====
class PremiumBadgeAnimator {
    constructor() {
        this.init();
    }

    init() {
        const badges = document.querySelectorAll('.trust-badge, .badge');

        badges.forEach((badge, index) => {
            badge.style.animationDelay = `${index * 0.1}s`;
            badge.classList.add('svg-icon-pulse');
        });
    }
}

// ===== AUTO SHOWCASE =====
class AutoShowcase {
    constructor() {
        this.init();
    }

    init() {
        // Automatically show key features to first-time visitors
        const hasVisited = localStorage.getItem('pr_visited');

        if (!hasVisited) {
            localStorage.setItem('pr_visited', 'true');

            setTimeout(() => {
                window.dynamicIsland?.show({
                    icon: '🎁',
                    title: 'Special Offer!',
                    message: 'Get 20% off on your first subscription',
                    type: 'warning',
                    duration: 6000,
                    actions: [
                        {
                            label: 'Claim Now',
                            type: 'primary',
                            onClick: () => {
                                document.querySelector('#pricing')?.scrollIntoView({ behavior: 'smooth' });
                            }
                        }
                    ],
                    large: true
                });
            }, 10000);
        }
    }
}

// ===== KEYBOARD NAVIGATION =====
class KeyboardNavigation {
    constructor() {
        this.sections = [];
        this.currentIndex = 0;
        this.init();
    }

    init() {
        this.sections = Array.from(document.querySelectorAll('section, .hero'));

        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

            if (e.key === 'ArrowDown' && e.altKey) {
                e.preventDefault();
                this.nextSection();
            } else if (e.key === 'ArrowUp' && e.altKey) {
                e.preventDefault();
                this.prevSection();
            } else if (e.key === 'Home' && e.altKey) {
                e.preventDefault();
                this.goToSection(0);
            } else if (e.key === 'End' && e.altKey) {
                e.preventDefault();
                this.goToSection(this.sections.length - 1);
            }
        });
    }

    nextSection() {
        if (this.currentIndex < this.sections.length - 1) {
            this.goToSection(this.currentIndex + 1);
        }
    }

    prevSection() {
        if (this.currentIndex > 0) {
            this.goToSection(this.currentIndex - 1);
        }
    }

    goToSection(index) {
        this.currentIndex = index;
        this.sections[index]?.scrollIntoView({ behavior: 'smooth' });
    }
}

// ===== INITIALIZE BATCH 8 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Core Visual Effects - NoiseOverlay already in main init
        new ParticleNetwork();
        new GradientMeshEnhancer();

        // Interactive Elements - MagneticCursor conflicts with CustomCursor
        window.activityTicker = new LiveActivityTicker();
        new InteractiveGlobe();

        // Animations
        new StaggerAnimations();
        new AnimatedStatsCounter();
        // MarqueeText disabled - too distracting

        // Enhancements
        new GlowCardEnhancer();
        // TextGradientWave disabled - causes text issues
        new ButtonEnhancer();
        new PremiumBadgeAnimator();

        // User Experience - Disabled intrusive features
        new SmoothScrollEnhancer();
        new KeyboardNavigation();

        console.log('%c Batch 8 Loaded (Clean) ', 'background: #00d4ff; color: #000; padding: 3px 8px;');
    }, 3000);
});

// ========================================
// BATCH 9: TRANSCENDENT PERFECTION
// Beyond All Limits - Infinite Premium
// ========================================

// ===== REAL-TIME MARKET CLOCK WIDGET =====
class MarketClockWidget {
    constructor() {
        this.markets = [
            { city: 'New York', flag: '🇺🇸', timezone: 'America/New_York', open: 9, close: 16 },
            { city: 'London', flag: '🇬🇧', timezone: 'Europe/London', open: 8, close: 16 },
            { city: 'Tokyo', flag: '🇯🇵', timezone: 'Asia/Tokyo', open: 9, close: 15 },
            { city: 'Sydney', flag: '🇦🇺', timezone: 'Australia/Sydney', open: 10, close: 16 },
            { city: 'Singapore', flag: '🇸🇬', timezone: 'Asia/Singapore', open: 9, close: 17 }
        ];
        this.isVisible = false;
        this.init();
    }

    init() {
        const widget = document.createElement('div');
        widget.className = 'market-clock-widget';
        widget.innerHTML = `
            <button class="market-clock-toggle">🕐</button>
            <div class="market-clock-header">
                <h4>🌍 Market Hours</h4>
            </div>
            <div class="market-clock-list"></div>
        `;
        document.body.appendChild(widget);

        this.widget = widget;
        this.list = widget.querySelector('.market-clock-list');
        this.toggle = widget.querySelector('.market-clock-toggle');

        this.toggle.addEventListener('click', () => this.toggleVisibility());

        this.populateMarkets();
        this.updateTimes();
        setInterval(() => this.updateTimes(), 1000);
    }

    toggleVisibility() {
        this.isVisible = !this.isVisible;
        this.widget.classList.toggle('visible', this.isVisible);
    }

    populateMarkets() {
        this.markets.forEach(market => {
            const item = document.createElement('div');
            item.className = 'market-clock-item';
            item.dataset.timezone = market.timezone;
            item.innerHTML = `
                <div class="market-clock-city">
                    <span class="market-clock-flag">${market.flag}</span>
                    <span class="market-clock-name">${market.city}</span>
                </div>
                <div style="display: flex; align-items: center;">
                    <span class="market-clock-time">--:--</span>
                    <span class="market-clock-status"></span>
                </div>
            `;
            this.list.appendChild(item);
        });
    }

    updateTimes() {
        const items = this.list.querySelectorAll('.market-clock-item');
        items.forEach((item, index) => {
            const market = this.markets[index];
            const time = new Date().toLocaleTimeString('en-US', {
                timeZone: market.timezone,
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            const hour = parseInt(time.split(':')[0]);
            const isOpen = hour >= market.open && hour < market.close;

            item.querySelector('.market-clock-time').textContent = time;
            const status = item.querySelector('.market-clock-status');
            status.className = `market-clock-status ${isOpen ? 'open' : 'closed'}`;
        });
    }
}

// ===== FLOATING SOCIAL PROOF BUBBLES =====
class SocialProofBubbles {
    constructor() {
        this.proofs = [
            { name: 'Ahmad R.', action: 'just subscribed to', item: 'Pro Plan', emoji: '🎉' },
            { name: 'Siti N.', action: 'earned', item: '+$1,250 profit', emoji: '💰' },
            { name: 'Budi W.', action: 'just joined', item: 'Trading Plan Robot', emoji: '🚀' },
            { name: 'Dewi S.', action: 'achieved', item: '95% win rate', emoji: '🏆' },
            { name: 'Rizki A.', action: 'started trading with', item: 'AI Robot', emoji: '🤖' },
            { name: 'Maya P.', action: 'upgraded to', item: 'Enterprise Plan', emoji: '⭐' },
            { name: 'Eko H.', action: 'made', item: '50 winning trades', emoji: '📈' },
            { name: 'Lisa M.', action: 'verified', item: 'account successfully', emoji: '✅' }
        ];
        this.locations = ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang', 'Makassar', 'Bali', 'Yogyakarta'];
        this.init();
    }

    init() {
        this.createBubble();
        this.scheduleNext();
    }

    createBubble() {
        const bubble = document.createElement('div');
        bubble.className = 'social-proof-bubble';
        bubble.innerHTML = `
            <div class="social-proof-avatar">👤</div>
            <div class="social-proof-content">
                <p class="social-proof-text"></p>
                <div class="social-proof-meta">
                    <span class="location"></span>
                    <span class="time"></span>
                </div>
            </div>
            <button class="social-proof-close">✕</button>
        `;
        document.body.appendChild(bubble);
        this.bubble = bubble;

        bubble.querySelector('.social-proof-close').addEventListener('click', () => {
            this.hide();
        });
    }

    show() {
        const proof = this.proofs[Math.floor(Math.random() * this.proofs.length)];
        const location = this.locations[Math.floor(Math.random() * this.locations.length)];
        const timeAgo = ['Just now', '2 min ago', '5 min ago'][Math.floor(Math.random() * 3)];

        this.bubble.querySelector('.social-proof-avatar').textContent = proof.emoji;
        this.bubble.querySelector('.social-proof-text').innerHTML =
            `<strong>${proof.name}</strong> ${proof.action} <strong>${proof.item}</strong>`;
        this.bubble.querySelector('.location').textContent = `📍 ${location}`;
        this.bubble.querySelector('.time').textContent = timeAgo;

        this.bubble.classList.add('show');
        window.soundSystem?.play('notification');

        setTimeout(() => this.hide(), 5000);
    }

    hide() {
        this.bubble.classList.remove('show');
    }

    scheduleNext() {
        const delay = 20000 + Math.random() * 40000; // 20-60 seconds
        setTimeout(() => {
            this.show();
            this.scheduleNext();
        }, delay);
    }
}

// ===== SCROLL-TO-TOP WITH PROGRESS RING =====
class ScrollToTop {
    constructor() {
        this.init();
    }

    init() {
        const container = document.createElement('div');
        container.className = 'scroll-to-top';
        container.innerHTML = `
            <button class="scroll-to-top-btn" aria-label="Scroll to top">
                <svg class="scroll-to-top-progress" viewBox="0 0 50 50">
                    <defs>
                        <linearGradient id="scrollProgressGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#667eea"/>
                            <stop offset="100%" stop-color="#764ba2"/>
                        </linearGradient>
                    </defs>
                    <circle class="bg" cx="25" cy="25" r="22"/>
                    <circle class="progress" cx="25" cy="25" r="22" stroke-dasharray="138.2" stroke-dashoffset="138.2"/>
                </svg>
                <span>↑</span>
            </button>
        `;
        document.body.appendChild(container);

        this.container = container;
        this.progress = container.querySelector('.progress');
        this.circumference = 2 * Math.PI * 22;

        container.querySelector('button').addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
            window.soundSystem?.play('swoosh');
        });

        window.addEventListener('scroll', () => this.update());
    }

    update() {
        const scrollTop = window.scrollY;
        const docHeight = document.body.scrollHeight - window.innerHeight;
        const progress = scrollTop / docHeight;

        // Show/hide button
        this.container.classList.toggle('visible', scrollTop > 300);

        // Update progress ring
        const offset = this.circumference * (1 - progress);
        this.progress.style.strokeDashoffset = offset;
    }
}

// ===== PARTNER LOGOS CAROUSEL =====
class PartnerLogos {
    constructor() {
        this.partners = [
            { name: 'MetaTrader', emoji: '📊' },
            { name: 'TradingView', emoji: '📈' },
            { name: 'Binance', emoji: '🪙' },
            { name: 'eToro', emoji: '💹' },
            { name: 'Forex.com', emoji: '💱' },
            { name: 'OANDA', emoji: '🌐' },
            { name: 'IC Markets', emoji: '📉' },
            { name: 'XM', emoji: '✨' }
        ];
        this.init();
    }

    init() {
        const footer = document.querySelector('footer') || document.querySelector('.footer');
        if (!footer) return;

        const section = document.createElement('div');
        section.className = 'partner-logos';
        section.innerHTML = `
            <div class="partner-logos-title">Trusted by Leading Platforms</div>
            <div class="partner-logos-track"></div>
        `;

        const track = section.querySelector('.partner-logos-track');
        const logos = [...this.partners, ...this.partners];

        logos.forEach(partner => {
            const logo = document.createElement('div');
            logo.className = 'partner-logo';
            logo.innerHTML = `
                <div class="partner-logo-placeholder">${partner.emoji} ${partner.name}</div>
            `;
            track.appendChild(logo);
        });

        footer.parentNode.insertBefore(section, footer);
    }
}

// ===== COOKIE CONSENT BANNER (DUPLICATE REMOVED - Using original at line 957) =====

// ===== EXIT INTENT NEWSLETTER POPUP =====
class NewsletterPopup {
    constructor() {
        if (localStorage.getItem('newsletter_shown')) return;
        this.init();
    }

    init() {
        this.createPopup();
        this.bindExitIntent();
    }

    createPopup() {
        const popup = document.createElement('div');
        popup.className = 'newsletter-popup';
        popup.innerHTML = `
            <div class="newsletter-popup-box">
                <button class="newsletter-popup-close">✕</button>
                <div class="newsletter-popup-icon">📧</div>
                <h3>Wait! Don't Miss Out!</h3>
                <p>Subscribe to get exclusive trading tips, market updates, and special offers delivered to your inbox.</p>
                <form class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Enter your email" required>
                    <button type="submit" class="newsletter-submit">Subscribe</button>
                </form>
                <p class="newsletter-disclaimer">No spam. Unsubscribe anytime.</p>
            </div>
        `;
        document.body.appendChild(popup);
        this.popup = popup;

        popup.querySelector('.newsletter-popup-close').addEventListener('click', () => this.close());
        popup.addEventListener('click', (e) => {
            if (e.target === popup) this.close();
        });

        popup.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.subscribe();
        });
    }

    bindExitIntent() {
        document.addEventListener('mouseleave', (e) => {
            if (e.clientY < 10 && !localStorage.getItem('newsletter_shown')) {
                this.show();
            }
        });

        // Also show after 30 seconds on page
        setTimeout(() => {
            if (!localStorage.getItem('newsletter_shown')) {
                this.show();
            }
        }, 30000);
    }

    show() {
        this.popup.classList.add('show');
        localStorage.setItem('newsletter_shown', 'true');
        window.soundSystem?.play('pop');
    }

    close() {
        this.popup.classList.remove('show');
    }

    subscribe() {
        const email = this.popup.querySelector('.newsletter-input').value;
        console.log('Newsletter subscription:', email);

        window.dynamicIsland?.show({
            icon: '✅',
            title: 'Subscribed!',
            message: 'Thanks for subscribing to our newsletter',
            type: 'success',
            duration: 3000
        });

        this.close();
    }
}

// ===== ACHIEVEMENT SYSTEM =====
class AchievementSystem {
    constructor() {
        this.achievements = [
            { id: 'first_visit', icon: '👋', title: 'Welcome!', desc: 'First visit to the platform', xp: 10 },
            { id: 'scroll_50', icon: '📜', title: 'Explorer', desc: 'Scrolled 50% of the page', xp: 20 },
            { id: 'scroll_100', icon: '🏁', title: 'Completionist', desc: 'Scrolled to the bottom', xp: 50 },
            { id: 'time_1min', icon: '⏱️', title: 'Engaged', desc: 'Spent 1 minute on site', xp: 15 },
            { id: 'time_5min', icon: '🎯', title: 'Interested', desc: 'Spent 5 minutes exploring', xp: 30 },
            { id: 'click_5', icon: '👆', title: 'Clicker', desc: 'Clicked 5 interactive elements', xp: 25 }
        ];
        this.unlocked = JSON.parse(localStorage.getItem('achievements') || '[]');
        this.clicks = 0;
        this.startTime = Date.now();
        this.init();
    }

    init() {
        this.createPopup();
        this.trackProgress();

        // First visit achievement
        if (!this.unlocked.includes('first_visit')) {
            setTimeout(() => this.unlock('first_visit'), 3000);
        }
    }

    createPopup() {
        const popup = document.createElement('div');
        popup.className = 'achievement-popup';
        popup.innerHTML = `
            <div class="achievement-icon"></div>
            <div class="achievement-content">
                <h4>Achievement Unlocked!</h4>
                <h3 class="achievement-title"></h3>
                <p class="achievement-desc"></p>
                <div class="achievement-xp">+<span></span> XP</div>
            </div>
        `;
        document.body.appendChild(popup);
        this.popup = popup;
    }

    trackProgress() {
        // Track scrolling
        window.addEventListener('scroll', () => {
            const percent = (window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100;
            if (percent >= 50 && !this.unlocked.includes('scroll_50')) {
                this.unlock('scroll_50');
            }
            if (percent >= 95 && !this.unlocked.includes('scroll_100')) {
                this.unlock('scroll_100');
            }
        });

        // Track clicks
        document.addEventListener('click', () => {
            this.clicks++;
            if (this.clicks >= 5 && !this.unlocked.includes('click_5')) {
                this.unlock('click_5');
            }
        });

        // Track time
        setInterval(() => {
            const elapsed = (Date.now() - this.startTime) / 1000;
            if (elapsed >= 60 && !this.unlocked.includes('time_1min')) {
                this.unlock('time_1min');
            }
            if (elapsed >= 300 && !this.unlocked.includes('time_5min')) {
                this.unlock('time_5min');
            }
        }, 5000);
    }

    unlock(id) {
        const achievement = this.achievements.find(a => a.id === id);
        if (!achievement || this.unlocked.includes(id)) return;

        this.unlocked.push(id);
        localStorage.setItem('achievements', JSON.stringify(this.unlocked));

        this.showPopup(achievement);
    }

    showPopup(achievement) {
        this.popup.querySelector('.achievement-icon').textContent = achievement.icon;
        this.popup.querySelector('.achievement-title').textContent = achievement.title;
        this.popup.querySelector('.achievement-desc').textContent = achievement.desc;
        this.popup.querySelector('.achievement-xp span').textContent = achievement.xp;

        this.popup.classList.add('show');
        window.soundSystem?.play('achievement');
        window.confetti?.burst(window.innerWidth - 100, 150, 30);

        setTimeout(() => this.popup.classList.remove('show'), 5000);
    }
}

// ===== ONLINE USERS INDICATOR =====
class OnlineUsersIndicator {
    constructor() {
        this.baseCount = 127;
        this.init();
    }

    init() {
        const indicator = document.createElement('div');
        indicator.className = 'online-users';
        indicator.innerHTML = `
            <div class="online-users-dot"></div>
            <span class="online-users-count">${this.baseCount} online</span>
            <div class="online-users-avatars">
                <div class="online-users-avatar">👤</div>
                <div class="online-users-avatar">👤</div>
                <div class="online-users-avatar">👤</div>
            </div>
        `;
        document.body.appendChild(indicator);
        this.indicator = indicator;
        this.countEl = indicator.querySelector('.online-users-count');

        this.updateCount();
        setInterval(() => this.updateCount(), 10000);
    }

    updateCount() {
        const variation = Math.floor(Math.random() * 20) - 10;
        const count = Math.max(80, this.baseCount + variation);
        this.countEl.textContent = `${count} online`;
    }
}

// ===== READING PROGRESS BAR =====
class ReadingProgress {
    constructor() {
        this.init();
    }

    init() {
        const bar = document.createElement('div');
        bar.className = 'reading-progress';
        document.body.appendChild(bar);
        this.bar = bar;

        window.addEventListener('scroll', () => this.update());
    }

    update() {
        const scrollTop = window.scrollY;
        const docHeight = document.body.scrollHeight - window.innerHeight;
        const progress = (scrollTop / docHeight) * 100;
        this.bar.style.width = `${progress}%`;
    }
}

// ===== SPEED DIAL MENU =====
class SpeedDial {
    constructor() {
        this.isOpen = false;
        this.init();
    }

    init() {
        const dial = document.createElement('div');
        dial.className = 'speed-dial';
        dial.innerHTML = `
            <button class="speed-dial-trigger">+</button>
            <div class="speed-dial-actions">
                <div class="speed-dial-action">
                    <span>Chat with AI</span>
                    <button data-action="chat">🤖</button>
                </div>
                <div class="speed-dial-action">
                    <span>Quick Contact</span>
                    <button data-action="contact">📧</button>
                </div>
                <div class="speed-dial-action">
                    <span>Share</span>
                    <button data-action="share">📤</button>
                </div>
                <div class="speed-dial-action">
                    <span>Celebrate!</span>
                    <button data-action="celebrate">🎉</button>
                </div>
            </div>
        `;
        document.body.appendChild(dial);

        this.dial = dial;
        this.trigger = dial.querySelector('.speed-dial-trigger');

        this.trigger.addEventListener('click', () => this.toggle());

        dial.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleAction(e.target.dataset.action));
        });
    }

    toggle() {
        this.isOpen = !this.isOpen;
        this.dial.classList.toggle('open', this.isOpen);
        this.trigger.classList.toggle('open', this.isOpen);
        window.soundSystem?.play('pop');
    }

    handleAction(action) {
        switch (action) {
            case 'chat':
                document.querySelector('.ai-chat-trigger')?.click();
                break;
            case 'contact':
                window.dynamicIsland?.show({
                    icon: '📧',
                    title: 'Contact Us',
                    message: 'support@tradingplanrobot.com',
                    type: 'info',
                    duration: 4000
                });
                break;
            case 'share':
                if (navigator.share) {
                    navigator.share({
                        title: 'Trading Plan Robot',
                        url: window.location.href
                    });
                }
                break;
            case 'celebrate':
                window.confetti?.burst();
                window.fireworks?.show(3);
                break;
        }
        this.toggle();
    }
}

// ===== CUSTOM CURSOR (DUPLICATE REMOVED - Using original at line 76) =====

// ===== SESSION TIME TRACKER =====
class SessionTimeTracker {
    constructor() {
        this.startTime = Date.now();
        this.init();
    }

    init() {
        // Show session time on specific trigger
        document.addEventListener('keydown', (e) => {
            if (e.key === 'i' && e.altKey) {
                const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;

                window.dynamicIsland?.show({
                    icon: '⏱️',
                    title: 'Session Time',
                    message: `You've been here for ${minutes}m ${seconds}s`,
                    type: 'info',
                    duration: 3000
                });
            }
        });
    }
}

// ===== INITIALIZE BATCH 9 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Widgets - Only essential ones
        new MarketClockWidget();
        new ReadingProgress();

        // Social Proof - Disabled (too intrusive)
        // new SocialProofBubbles();
        // new OnlineUsersIndicator();

        // Navigation
        new ScrollToTop();
        // SpeedDial disabled - overlaps with other buttons

        // Popups & Consent - Already initialized in main block
        // CookieConsent and NewsletterPopup disabled (duplicates)

        // Gamification
        window.achievements = new AchievementSystem();

        // Cursor already initialized in main block
        // Tracking disabled - not needed

        console.log('%c Batch 9 Loaded (Clean) ', 'background: #7c3aed; color: #fff; padding: 3px 8px;');
    }, 3500);
});

// ============================================
// BATCH 10: DIVINE ASCENSION - GODLIKE FEATURES
// ============================================

// ===== 1. MORPHING TEXT ANIMATION =====
class MorphingText {
    constructor(element, words, interval = 3000) {
        this.element = typeof element === 'string' ? document.querySelector(element) : element;
        if (!this.element) return;

        this.words = words;
        this.interval = interval;
        this.currentIndex = 0;

        this.init();
    }

    init() {
        this.element.classList.add('morph-text');
        this.element.setAttribute('data-text', this.words[0]);
        this.element.textContent = this.words[0];

        setInterval(() => this.morph(), this.interval);
    }

    morph() {
        this.element.classList.add('morphing');

        setTimeout(() => {
            this.currentIndex = (this.currentIndex + 1) % this.words.length;
            const newWord = this.words[this.currentIndex];
            this.element.textContent = newWord;
            this.element.setAttribute('data-text', newWord);
        }, 250);

        setTimeout(() => {
            this.element.classList.remove('morphing');
        }, 500);
    }
}

// ===== 2. SPOTLIGHT/FLASHLIGHT CURSOR EFFECT (DUPLICATE REMOVED - Using original at line 1376) =====

// ===== 3. GLITCH TEXT EFFECT =====
class GlitchText {
    constructor(selector) {
        this.elements = document.querySelectorAll(selector);
        this.init();
    }

    init() {
        this.elements.forEach(el => {
            el.classList.add('glitch-text');
            el.setAttribute('data-text', el.textContent);

            el.addEventListener('mouseenter', () => {
                el.classList.add('glitch-intense');
            });

            el.addEventListener('mouseleave', () => {
                el.classList.remove('glitch-intense');
            });
        });
    }

    static apply(element) {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (el) {
            el.classList.add('glitch-text');
            el.setAttribute('data-text', el.textContent);
        }
    }
}

// ===== 4. ANIMATED GEOMETRIC SHAPES BACKGROUND =====
class GeometricShapes {
    constructor() {
        this.container = null;
        this.shapes = ['triangle', 'circle', 'square', 'hexagon', 'ring', 'cross'];

        this.init();
    }

    init() {
        this.container = document.createElement('div');
        this.container.className = 'geometric-shapes-container';
        document.body.appendChild(this.container);

        // Create 15 random shapes
        for (let i = 0; i < 15; i++) {
            this.createShape();
        }
    }

    createShape() {
        const shape = document.createElement('div');
        const shapeType = this.shapes[Math.floor(Math.random() * this.shapes.length)];
        shape.className = `geo-shape ${shapeType}`;

        // Random position
        shape.style.left = Math.random() * 100 + '%';
        shape.style.top = Math.random() * 100 + '%';

        // Random animation duration
        shape.style.animationDuration = (15 + Math.random() * 20) + 's';
        shape.style.animationDelay = -Math.random() * 20 + 's';

        this.container.appendChild(shape);
    }
}

// ===== 5. 3D TESTIMONIAL CAROUSEL =====
class TestimonialCarousel3D {
    constructor(container, testimonials) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        this.testimonials = testimonials || this.getDefaultTestimonials();
        this.isPaused = false;

        if (this.container) {
            this.init();
        }
    }

    getDefaultTestimonials() {
        return [
            { quote: "This platform transformed my trading strategy completely. Returns increased by 340%!", author: "Michael R.", role: "Professional Trader", avatar: "👨‍💼" },
            { quote: "The AI predictions are incredibly accurate. Best investment I've ever made.", author: "Sarah K.", role: "Investment Manager", avatar: "👩‍💻" },
            { quote: "24/7 support and seamless withdrawals. This is how trading should be.", author: "David L.", role: "Hedge Fund Analyst", avatar: "👨‍🔬" },
            { quote: "From beginner to profitable in just 3 months. The educational resources are gold.", author: "Emma W.", role: "Retail Investor", avatar: "👩‍🎓" },
            { quote: "The most sophisticated trading tools I've used. Enterprise-grade quality.", author: "James C.", role: "Quantitative Analyst", avatar: "👨‍💻" },
            { quote: "Security and speed are unmatched. Executing trades in milliseconds.", author: "Lisa M.", role: "Day Trader", avatar: "👩‍💼" }
        ];
    }

    init() {
        this.container.innerHTML = `
            <div class="testimonial-carousel-3d">
                <div class="testimonial-carousel-track">
                    ${this.testimonials.map((t, i) => `
                        <div class="testimonial-card-3d">
                            <div class="testimonial-quote">"${t.quote}"</div>
                            <div class="testimonial-author">
                                <div class="testimonial-avatar">${t.avatar}</div>
                                <div>
                                    <div class="testimonial-name">${t.author}</div>
                                    <div class="testimonial-role">${t.role}</div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <button class="carousel-pause-btn">⏸ Pause</button>
            </div>
        `;

        const pauseBtn = this.container.querySelector('.carousel-pause-btn');
        const track = this.container.querySelector('.testimonial-carousel-track');

        pauseBtn.addEventListener('click', () => {
            this.isPaused = !this.isPaused;
            track.style.animationPlayState = this.isPaused ? 'paused' : 'running';
            pauseBtn.innerHTML = this.isPaused ? '▶ Play' : '⏸ Pause';
        });
    }
}

// ===== 6. COUNTDOWN TIMER WIDGET =====
class CountdownTimer {
    constructor(options = {}) {
        this.targetDate = options.targetDate || this.getNextMondayMidnight();
        this.eventName = options.eventName || 'Special Offer Ends';
        this.onComplete = options.onComplete || null;
        this.widget = null;

        this.init();
    }

    getNextMondayMidnight() {
        const now = new Date();
        const daysUntilMonday = (8 - now.getDay()) % 7 || 7;
        const nextMonday = new Date(now);
        nextMonday.setDate(now.getDate() + daysUntilMonday);
        nextMonday.setHours(0, 0, 0, 0);
        return nextMonday;
    }

    init() {
        // Check if already dismissed
        if (localStorage.getItem('countdownDismissed') === new Date().toDateString()) {
            return;
        }

        this.widget = document.createElement('div');
        this.widget.className = 'countdown-widget';
        this.widget.innerHTML = `
            <button class="countdown-close">×</button>
            <div class="countdown-header">
                <div class="countdown-title">⏰ Limited Time</div>
                <div class="countdown-event">${this.eventName}</div>
            </div>
            <div class="countdown-timer">
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-days">00</div>
                    <div class="countdown-label">Days</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-hours">00</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-mins">00</div>
                    <div class="countdown-label">Mins</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-value" id="countdown-secs">00</div>
                    <div class="countdown-label">Secs</div>
                </div>
            </div>
        `;

        document.body.appendChild(this.widget);

        // Close button
        this.widget.querySelector('.countdown-close').addEventListener('click', () => {
            this.widget.remove();
            localStorage.setItem('countdownDismissed', new Date().toDateString());
        });

        // Start countdown
        this.update();
        setInterval(() => this.update(), 1000);
    }

    update() {
        const now = new Date().getTime();
        const distance = this.targetDate.getTime() - now;

        if (distance < 0) {
            this.widget.querySelector('.countdown-timer').innerHTML = `
                <div class="countdown-ended">
                    <div class="countdown-ended-text">🎉 Event Started!</div>
                </div>
            `;
            if (this.onComplete) this.onComplete();
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById('countdown-days').textContent = String(days).padStart(2, '0');
        document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('countdown-mins').textContent = String(minutes).padStart(2, '0');
        document.getElementById('countdown-secs').textContent = String(seconds).padStart(2, '0');
    }
}

// ===== 7. THEME TOGGLE (DUPLICATE REMOVED - Using original at line 2519) =====

// ===== 8. AUDIO VISUALIZER BACKGROUND =====
class AudioVisualizer {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.audioContext = null;
        this.analyser = null;
        this.isPlaying = false;
        this.animationId = null;

        this.init();
    }

    init() {
        // Create container
        const container = document.createElement('div');
        container.className = 'audio-visualizer-container';
        container.innerHTML = `<canvas class="audio-visualizer-canvas"></canvas>`;
        document.body.appendChild(container);

        this.canvas = container.querySelector('canvas');
        this.ctx = this.canvas.getContext('2d');

        // Create controls
        const controls = document.createElement('div');
        controls.className = 'audio-controls';
        controls.innerHTML = `
            <button class="audio-btn" id="audio-toggle" title="Toggle Ambient Music">🎵</button>
            <div class="volume-slider">
                <input type="range" min="0" max="100" value="30" id="volume-slider">
            </div>
        `;
        document.body.appendChild(controls);

        // Event listeners
        document.getElementById('audio-toggle').addEventListener('click', () => this.toggleAudio());

        // Resize handler
        window.addEventListener('resize', () => this.resize());
        this.resize();

        // Start visualization (simulated without actual audio)
        this.startSimulatedVisualizer();
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = 150;
    }

    startSimulatedVisualizer() {
        const bars = 64;
        const barWidth = this.canvas.width / bars;

        const animate = () => {
            this.animationId = requestAnimationFrame(animate);

            this.ctx.fillStyle = 'rgba(10, 10, 15, 0.3)';
            this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

            for (let i = 0; i < bars; i++) {
                // Simulated frequency data
                const value = Math.sin(Date.now() * 0.002 + i * 0.5) * 50 +
                              Math.sin(Date.now() * 0.003 + i * 0.3) * 30 +
                              Math.random() * 20;

                const height = Math.abs(value) * (this.isPlaying ? 1.5 : 0.3);
                const x = i * barWidth;
                const y = this.canvas.height - height;

                // Gradient color
                const hue = (i / bars) * 120 + 240;
                this.ctx.fillStyle = `hsla(${hue}, 80%, 60%, 0.8)`;
                this.ctx.fillRect(x, y, barWidth - 2, height);
            }
        };

        animate();
    }

    toggleAudio() {
        this.isPlaying = !this.isPlaying;
        const btn = document.getElementById('audio-toggle');
        btn.classList.toggle('playing', this.isPlaying);
        btn.innerHTML = this.isPlaying ? '🔊' : '🎵';

        if (this.isPlaying) {
            window.dynamicIsland?.show({
                icon: '🎵',
                title: 'Ambient Music',
                message: 'Visualization mode active',
                type: 'info',
                duration: 2000
            });
        }
    }
}

// ===== 9. TYPEWRITER EFFECT (DUPLICATE REMOVED - Using original at line 1171) =====

// ===== 10. QR CODE SHARE WIDGET =====
class QRShareWidget {
    constructor() {
        this.widget = null;
        this.popup = null;
        this.isOpen = false;

        this.init();
    }

    init() {
        this.widget = document.createElement('div');
        this.widget.className = 'qr-share-widget';
        this.widget.innerHTML = `
            <button class="qr-share-btn" title="Share via QR Code">📱</button>
            <div class="qr-popup">
                <div class="qr-popup-title">Scan to Share</div>
                <div class="qr-code-container">
                    <canvas class="qr-code-canvas" id="qr-canvas"></canvas>
                </div>
                <div class="qr-share-buttons">
                    <button class="qr-social-btn twitter" title="Twitter">𝕏</button>
                    <button class="qr-social-btn facebook" title="Facebook">f</button>
                    <button class="qr-social-btn linkedin" title="LinkedIn">in</button>
                    <button class="qr-social-btn copy" title="Copy Link">📋</button>
                </div>
            </div>
        `;

        document.body.appendChild(this.widget);

        this.popup = this.widget.querySelector('.qr-popup');
        const btn = this.widget.querySelector('.qr-share-btn');

        btn.addEventListener('click', () => this.togglePopup());

        // Social share buttons
        this.widget.querySelector('.twitter').addEventListener('click', () => {
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}`);
        });

        this.widget.querySelector('.facebook').addEventListener('click', () => {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`);
        });

        this.widget.querySelector('.linkedin').addEventListener('click', () => {
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}`);
        });

        this.widget.querySelector('.copy').addEventListener('click', () => {
            navigator.clipboard.writeText(window.location.href).then(() => {
                window.dynamicIsland?.show({
                    icon: '📋',
                    title: 'Link Copied!',
                    message: 'URL copied to clipboard',
                    type: 'success',
                    duration: 2000
                });
            });
        });

        // Generate simple QR pattern
        this.generateSimpleQR();
    }

    togglePopup() {
        this.isOpen = !this.isOpen;
        this.popup.classList.toggle('active', this.isOpen);
    }

    generateSimpleQR() {
        const canvas = document.getElementById('qr-canvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const size = 150;
        canvas.width = size;
        canvas.height = size;

        const moduleSize = size / 25;

        // Simple decorative QR pattern
        ctx.fillStyle = '#1a1a2e';
        ctx.fillRect(0, 0, size, size);

        ctx.fillStyle = '#667eea';

        // Corner patterns
        for (let i = 0; i < 7; i++) {
            for (let j = 0; j < 7; j++) {
                if (i === 0 || i === 6 || j === 0 || j === 6 || (i >= 2 && i <= 4 && j >= 2 && j <= 4)) {
                    ctx.fillRect(i * moduleSize, j * moduleSize, moduleSize - 1, moduleSize - 1);
                    ctx.fillRect((24 - i) * moduleSize, j * moduleSize, moduleSize - 1, moduleSize - 1);
                    ctx.fillRect(i * moduleSize, (24 - j) * moduleSize, moduleSize - 1, moduleSize - 1);
                }
            }
        }

        // Random pattern in the middle
        for (let i = 8; i < 17; i++) {
            for (let j = 8; j < 17; j++) {
                if (Math.random() > 0.5) {
                    ctx.fillRect(i * moduleSize, j * moduleSize, moduleSize - 1, moduleSize - 1);
                }
            }
        }
    }
}

// ===== 11. LIQUID BLOB NAVIGATION =====
class LiquidNavigation {
    constructor() {
        this.nav = null;
        this.blob = null;
        this.items = [
            { icon: '🏠', label: 'Home', section: 'hero' },
            { icon: '📊', label: 'Features', section: 'features' },
            { icon: '💰', label: 'Pricing', section: 'pricing' },
            { icon: '📞', label: 'Contact', section: 'contact' },
            { icon: '⬆️', label: 'Top', section: 'top' }
        ];

        this.init();
    }

    init() {
        this.nav = document.createElement('nav');
        this.nav.className = 'liquid-nav';
        this.nav.innerHTML = `
            <div class="liquid-nav-items">
                <div class="liquid-nav-blob"></div>
                ${this.items.map((item, i) => `
                    <div class="liquid-nav-item" data-section="${item.section}" data-index="${i}">
                        ${item.icon}
                        <span class="liquid-nav-tooltip">${item.label}</span>
                    </div>
                `).join('')}
            </div>
        `;

        document.body.appendChild(this.nav);

        this.blob = this.nav.querySelector('.liquid-nav-blob');
        const navItems = this.nav.querySelectorAll('.liquid-nav-item');

        navItems.forEach((item, index) => {
            item.addEventListener('click', () => {
                const section = item.dataset.section;

                // Update active state
                navItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');

                // Move blob
                this.moveBlob(index);

                // Scroll to section
                if (section === 'top') {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    const el = document.getElementById(section) || document.querySelector(`.${section}`);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });

            item.addEventListener('mouseenter', () => {
                this.blob.classList.add('morphing');
            });

            item.addEventListener('mouseleave', () => {
                this.blob.classList.remove('morphing');
            });
        });

        // Set initial position
        this.moveBlob(0);
        navItems[0].classList.add('active');
    }

    moveBlob(index) {
        const itemHeight = 50;
        const gap = 15;
        const top = index * (itemHeight + gap);
        this.blob.style.transform = `translateY(${top}px)`;
    }
}

// ===== 12. MATRIX RAIN EFFECT =====
class MatrixRain {
    constructor() {
        this.container = null;
        this.canvas = null;
        this.ctx = null;
        this.isActive = false;
        this.animationId = null;
        this.drops = [];

        this.init();
    }

    init() {
        // Create container
        this.container = document.createElement('div');
        this.container.className = 'matrix-rain-container';
        this.container.innerHTML = `<canvas class="matrix-rain-canvas"></canvas>`;
        document.body.appendChild(this.container);

        this.canvas = this.container.querySelector('canvas');
        this.ctx = this.canvas.getContext('2d');

        // Create toggle button
        const toggle = document.createElement('button');
        toggle.className = 'matrix-toggle';
        toggle.innerHTML = 'M';
        toggle.title = 'Toggle Matrix Rain (Alt+M)';
        document.body.appendChild(toggle);

        toggle.addEventListener('click', () => this.toggle());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'm' && e.altKey) {
                e.preventDefault();
                this.toggle();
            }
        });

        window.addEventListener('resize', () => this.resize());
        this.resize();
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;

        // Initialize drops
        const columns = Math.floor(this.canvas.width / 20);
        this.drops = [];
        for (let i = 0; i < columns; i++) {
            this.drops[i] = Math.random() * -100;
        }
    }

    toggle() {
        this.isActive = !this.isActive;
        this.container.classList.toggle('active', this.isActive);
        document.querySelector('.matrix-toggle').classList.toggle('active', this.isActive);

        if (this.isActive) {
            this.animate();
            window.dynamicIsland?.show({
                icon: '🟢',
                title: 'Matrix Rain',
                message: 'Welcome to the Matrix',
                type: 'success',
                duration: 2000
            });
        } else {
            cancelAnimationFrame(this.animationId);
        }
    }

    animate() {
        if (!this.isActive) return;

        // Semi-transparent black to create fade effect
        this.ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        this.ctx.fillRect(0, 0, this.canvas.width, this.canvas.height);

        // Green text
        this.ctx.fillStyle = '#0F0';
        this.ctx.font = '15px monospace';

        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%^&*()アイウエオカキクケコサシスセソ';

        for (let i = 0; i < this.drops.length; i++) {
            const char = chars[Math.floor(Math.random() * chars.length)];
            const x = i * 20;
            const y = this.drops[i] * 20;

            this.ctx.fillText(char, x, y);

            // Reset drop to top with random delay
            if (y > this.canvas.height && Math.random() > 0.975) {
                this.drops[i] = 0;
            }

            this.drops[i]++;
        }

        this.animationId = requestAnimationFrame(() => this.animate());
    }
}

// ===== 13. PREMIUM PRELOADER (REMOVED - Using original at top of file) =====
// Duplicate class removed to prevent infinite loading issue

// ===== 14. FLOATING ACTION LABELS =====
class FloatingLabels {
    constructor() {
        this.labels = [
            { text: 'Live Support', icon: '💬', class: 'support', action: () => window.aiChat?.toggle() },
            { text: 'Get Quote', icon: '💰', class: 'sales', action: () => console.log('Sales') },
            { text: 'Book Demo', icon: '📅', class: 'demo', action: () => console.log('Demo') }
        ];

        this.init();
    }

    init() {
        const container = document.createElement('div');
        container.className = 'floating-labels';
        container.innerHTML = this.labels.map(label => `
            <div class="floating-label ${label.class}">
                <span class="floating-label-text">${label.text}</span>
                <span class="floating-label-icon">${label.icon}</span>
            </div>
        `).join('');

        document.body.appendChild(container);

        container.querySelectorAll('.floating-label').forEach((el, i) => {
            el.addEventListener('click', this.labels[i].action);
        });
    }
}

// ===== INITIALIZE BATCH 10 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    // Preloader is already initialized in main DOMContentLoaded handler

    setTimeout(() => {
        // Visual Effects - Keep subtle ones only
        new GeometricShapes();
        // SpotlightEffect already in Batch 3
        // MatrixRain disabled - too distracting

        // Widgets - ThemeToggle already in Batch 6
        // CountdownTimer disabled - not needed
        // QRShareWidget disabled - too cluttered

        // Navigation - Disabled overlapping elements
        // LiquidNavigation conflicts with main nav
        // FloatingLabels disabled - clutters screen

        // Audio/Visual - AudioVisualizer disabled

        // DISABLED: MorphingText and TypewriterEffect on hero
        // These were replacing the original hero text with different content
        // Keep the original HTML text intact

        console.log('%c Batch 10 Loaded (Clean) ', 'background: #ffd700; color: #000; padding: 3px 8px;');
    }, 4000);
});

// ============================================
// BATCH 11: INFINITY BEYOND - COSMIC PERFECTION
// ============================================

// ===== 1. PARALLAX TILT CARDS =====
class ParallaxTilt {
    constructor(element, options = {}) {
        this.element = typeof element === 'string' ? document.querySelector(element) : element;
        if (!this.element) return;

        this.options = {
            maxTilt: options.maxTilt || 15,
            perspective: options.perspective || 1000,
            scale: options.scale || 1.05,
            speed: options.speed || 400,
            glare: options.glare !== undefined ? options.glare : true,
            maxGlare: options.maxGlare || 0.3
        };

        this.init();
    }

    init() {
        this.element.classList.add('parallax-tilt');
        this.element.style.perspective = this.options.perspective + 'px';

        // Wrap content
        const inner = document.createElement('div');
        inner.className = 'parallax-tilt-inner';
        inner.innerHTML = this.element.innerHTML;
        this.element.innerHTML = '';
        this.element.appendChild(inner);
        this.inner = inner;

        // Add shine effect
        if (this.options.glare) {
            const shine = document.createElement('div');
            shine.className = 'parallax-tilt-shine';
            inner.appendChild(shine);
            this.shine = shine;
        }

        // Event listeners
        this.element.addEventListener('mouseenter', () => this.onEnter());
        this.element.addEventListener('mousemove', (e) => this.onMove(e));
        this.element.addEventListener('mouseleave', () => this.onLeave());
    }

    onEnter() {
        this.inner.style.transition = `transform ${this.options.speed}ms ease-out`;
        this.element.style.transform = `scale(${this.options.scale})`;
    }

    onMove(e) {
        const rect = this.element.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const tiltX = (y - centerY) / centerY * this.options.maxTilt;
        const tiltY = (centerX - x) / centerX * this.options.maxTilt;

        this.inner.style.transform = `rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;

        if (this.shine) {
            const glareX = (x / rect.width) * 100;
            const glareY = (y / rect.height) * 100;
            this.shine.style.background = `radial-gradient(circle at ${glareX}% ${glareY}%, rgba(255,255,255,${this.options.maxGlare}) 0%, transparent 60%)`;
        }
    }

    onLeave() {
        this.inner.style.transform = 'rotateX(0) rotateY(0)';
        this.element.style.transform = 'scale(1)';
    }
}

// ===== 2. RIPPLE CLICK EFFECT =====
class RippleEffect {
    constructor(selector = '.ripple-effect') {
        this.elements = document.querySelectorAll(selector);
        this.init();
    }

    init() {
        this.elements.forEach(el => {
            el.addEventListener('click', (e) => this.createRipple(e, el));
        });
    }

    createRipple(e, element) {
        const ripple = document.createElement('span');
        ripple.className = 'ripple';

        const rect = element.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
        `;

        element.appendChild(ripple);

        ripple.addEventListener('animationend', () => ripple.remove());
    }

    static addTo(element) {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (el) {
            el.classList.add('ripple-effect');
            el.addEventListener('click', (e) => {
                const ripple = document.createElement('span');
                ripple.className = 'ripple';

                const rect = el.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                ripple.style.cssText = `
                    width: ${size}px;
                    height: ${size}px;
                    left: ${e.clientX - rect.left - size / 2}px;
                    top: ${e.clientY - rect.top - size / 2}px;
                `;

                el.appendChild(ripple);
                ripple.addEventListener('animationend', () => ripple.remove());
            });
        }
    }
}

// ===== 3. SCROLL REVEAL ANIMATIONS =====
class ScrollReveal {
    constructor(options = {}) {
        this.options = {
            selector: options.selector || '.reveal',
            threshold: options.threshold || 0.1,
            rootMargin: options.rootMargin || '0px',
            once: options.once !== undefined ? options.once : true
        };

        this.init();
    }

    init() {
        const elements = document.querySelectorAll(this.options.selector);

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');

                    if (this.options.once) {
                        observer.unobserve(entry.target);
                    }
                } else if (!this.options.once) {
                    entry.target.classList.remove('revealed');
                }
            });
        }, {
            threshold: this.options.threshold,
            rootMargin: this.options.rootMargin
        });

        elements.forEach(el => observer.observe(el));
    }

    static reveal(element, animation = 'fade-up') {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (el) {
            el.classList.add('reveal', `reveal-${animation}`);
        }
    }
}

// ===== 4. TEXT SCRAMBLE EFFECT (DUPLICATE REMOVED - Using original at line 1224) =====

// ===== 5. INTERACTIVE BACKGROUND GRID =====
class InteractiveGrid {
    constructor() {
        this.canvas = null;
        this.ctx = null;
        this.isActive = false;
        this.mouse = { x: 0, y: 0 };
        this.points = [];

        this.init();
    }

    init() {
        // Create container
        const container = document.createElement('div');
        container.className = 'interactive-grid';
        container.innerHTML = `<canvas class="interactive-grid-canvas"></canvas>`;
        document.body.appendChild(container);

        this.canvas = container.querySelector('canvas');
        this.ctx = this.canvas.getContext('2d');

        // Create toggle
        const toggle = document.createElement('button');
        toggle.className = 'grid-toggle';
        toggle.innerHTML = '⊞';
        toggle.title = 'Toggle Interactive Grid (Alt+G)';
        document.body.appendChild(toggle);

        toggle.addEventListener('click', () => this.toggle());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'g' && e.altKey) {
                e.preventDefault();
                this.toggle();
            }
        });

        document.addEventListener('mousemove', (e) => {
            this.mouse.x = e.clientX;
            this.mouse.y = e.clientY;
        });

        window.addEventListener('resize', () => this.resize());
        this.resize();
    }

    resize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
        this.createPoints();
    }

    createPoints() {
        this.points = [];
        const spacing = 50;
        for (let x = 0; x < this.canvas.width; x += spacing) {
            for (let y = 0; y < this.canvas.height; y += spacing) {
                this.points.push({
                    x: x,
                    y: y,
                    originX: x,
                    originY: y
                });
            }
        }
    }

    toggle() {
        this.isActive = !this.isActive;
        document.querySelector('.grid-toggle').classList.toggle('active', this.isActive);

        if (this.isActive) {
            this.animate();
            window.dynamicIsland?.show({
                icon: '⊞',
                title: 'Interactive Grid',
                message: 'Grid background enabled',
                type: 'info',
                duration: 2000
            });
        }
    }

    animate() {
        if (!this.isActive) return;

        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

        // Draw connections
        this.ctx.strokeStyle = 'rgba(102, 126, 234, 0.1)';
        this.ctx.lineWidth = 1;

        for (let i = 0; i < this.points.length; i++) {
            const p = this.points[i];

            // Move point based on mouse
            const dx = this.mouse.x - p.originX;
            const dy = this.mouse.y - p.originY;
            const dist = Math.sqrt(dx * dx + dy * dy);
            const maxDist = 150;

            if (dist < maxDist) {
                const force = (maxDist - dist) / maxDist;
                p.x = p.originX + dx * force * 0.3;
                p.y = p.originY + dy * force * 0.3;
            } else {
                p.x += (p.originX - p.x) * 0.1;
                p.y += (p.originY - p.y) * 0.1;
            }

            // Draw point
            this.ctx.beginPath();
            this.ctx.arc(p.x, p.y, 2, 0, Math.PI * 2);
            this.ctx.fillStyle = `rgba(102, 126, 234, ${0.3 + (dist < maxDist ? (maxDist - dist) / maxDist * 0.7 : 0)})`;
            this.ctx.fill();

            // Connect nearby points
            for (let j = i + 1; j < this.points.length; j++) {
                const p2 = this.points[j];
                const dx2 = p.x - p2.x;
                const dy2 = p.y - p2.y;
                const dist2 = Math.sqrt(dx2 * dx2 + dy2 * dy2);

                if (dist2 < 80) {
                    this.ctx.beginPath();
                    this.ctx.moveTo(p.x, p.y);
                    this.ctx.lineTo(p2.x, p2.y);
                    this.ctx.strokeStyle = `rgba(102, 126, 234, ${0.2 * (1 - dist2 / 80)})`;
                    this.ctx.stroke();
                }
            }
        }

        requestAnimationFrame(() => this.animate());
    }
}

// ===== 6. PREMIUM TOOLTIP SYSTEM =====
class PremiumTooltip {
    static create(element, text, position = 'top') {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;

        el.classList.add('tooltip-trigger');

        const tooltip = document.createElement('div');
        tooltip.className = `premium-tooltip tooltip-${position}`;
        tooltip.textContent = text;

        el.appendChild(tooltip);
    }

    static createWithIcon(element, icon, text, position = 'top') {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;

        el.classList.add('tooltip-trigger');

        const tooltip = document.createElement('div');
        tooltip.className = `premium-tooltip tooltip-${position}`;
        tooltip.innerHTML = `<span class="tooltip-icon">${icon}</span>${text}`;

        el.appendChild(tooltip);
    }
}

// ===== 7. NOTIFICATION TOAST SYSTEM =====
class ToastSystem {
    constructor(position = 'top-right') {
        this.position = position;
        this.container = null;
        this.toasts = [];

        this.init();
    }

    init() {
        this.container = document.createElement('div');
        this.container.className = `toast-container ${this.position}`;
        document.body.appendChild(this.container);

        // Make globally accessible
        window.toast = this;
    }

    show(options = {}) {
        const {
            title = '',
            message = '',
            type = 'info',
            icon = this.getDefaultIcon(type),
            duration = 5000,
            closable = true,
            showProgress = true
        } = options;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                <div class="toast-message">${message}</div>
            </div>
            ${closable ? '<button class="toast-close">×</button>' : ''}
            ${showProgress ? `<div class="toast-progress" style="animation-duration: ${duration}ms"></div>` : ''}
        `;

        this.container.appendChild(toast);
        this.toasts.push(toast);

        if (closable) {
            toast.querySelector('.toast-close').addEventListener('click', () => this.dismiss(toast));
        }

        if (duration > 0) {
            setTimeout(() => this.dismiss(toast), duration);
        }

        return toast;
    }

    dismiss(toast) {
        toast.classList.add('toast-exiting');
        setTimeout(() => {
            toast.remove();
            this.toasts = this.toasts.filter(t => t !== toast);
        }, 300);
    }

    getDefaultIcon(type) {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        return icons[type] || icons.info;
    }

    success(message, title = 'Success') {
        return this.show({ message, title, type: 'success' });
    }

    error(message, title = 'Error') {
        return this.show({ message, title, type: 'error' });
    }

    warning(message, title = 'Warning') {
        return this.show({ message, title, type: 'warning' });
    }

    info(message, title = 'Info') {
        return this.show({ message, title, type: 'info' });
    }
}

// ===== 8. PREMIUM MODAL SYSTEM =====
class ModalSystem {
    constructor() {
        this.overlay = null;
        this.modal = null;
        this.isOpen = false;

        this.init();

        // Make globally accessible
        window.modal = this;
    }

    init() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'modal-overlay';
        this.overlay.innerHTML = `
            <div class="premium-modal">
                <div class="modal-header">
                    <div class="modal-title"></div>
                    <button class="modal-close">×</button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer"></div>
            </div>
        `;

        document.body.appendChild(this.overlay);

        this.modal = this.overlay.querySelector('.premium-modal');

        // Close handlers
        this.overlay.querySelector('.modal-close').addEventListener('click', () => this.close());
        this.overlay.addEventListener('click', (e) => {
            if (e.target === this.overlay) this.close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) this.close();
        });
    }

    open(options = {}) {
        const {
            title = '',
            icon = '',
            body = '',
            size = '', // sm, lg, xl, fullscreen
            buttons = [],
            onOpen = null,
            onClose = null
        } = options;

        this.onCloseCallback = onClose;

        // Set content
        this.modal.querySelector('.modal-title').innerHTML = icon ? `${icon} ${title}` : title;
        this.modal.querySelector('.modal-body').innerHTML = body;

        // Set size
        this.modal.className = 'premium-modal' + (size ? ` modal-${size}` : '');

        // Set buttons
        const footer = this.modal.querySelector('.modal-footer');
        footer.innerHTML = '';

        if (buttons.length > 0) {
            buttons.forEach(btn => {
                const button = document.createElement('button');
                button.className = `modal-btn modal-btn-${btn.type || 'secondary'}`;
                button.textContent = btn.text;
                button.addEventListener('click', () => {
                    if (btn.onClick) btn.onClick();
                    if (btn.closeOnClick !== false) this.close();
                });
                footer.appendChild(button);
            });
            footer.style.display = 'flex';
        } else {
            footer.style.display = 'none';
        }

        // Open modal
        this.overlay.classList.add('active');
        this.isOpen = true;
        document.body.style.overflow = 'hidden';

        if (onOpen) onOpen();
    }

    close() {
        this.overlay.classList.remove('active');
        this.isOpen = false;
        document.body.style.overflow = '';

        if (this.onCloseCallback) this.onCloseCallback();
    }

    confirm(options = {}) {
        return new Promise((resolve) => {
            this.open({
                title: options.title || 'Confirm',
                icon: options.icon || '❓',
                body: options.message || 'Are you sure?',
                buttons: [
                    {
                        text: options.cancelText || 'Cancel',
                        type: 'secondary',
                        onClick: () => resolve(false)
                    },
                    {
                        text: options.confirmText || 'Confirm',
                        type: 'primary',
                        onClick: () => resolve(true)
                    }
                ]
            });
        });
    }

    alert(title, message, icon = 'ℹ️') {
        return new Promise((resolve) => {
            this.open({
                title,
                icon,
                body: message,
                buttons: [
                    {
                        text: 'OK',
                        type: 'primary',
                        onClick: () => resolve()
                    }
                ]
            });
        });
    }
}

// ===== 9. ANIMATED ACCORDION =====
class Accordion {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        if (!this.container) return;

        this.options = {
            allowMultiple: options.allowMultiple || false,
            defaultOpen: options.defaultOpen || null
        };

        this.init();
    }

    init() {
        this.container.classList.add('premium-accordion');

        const items = this.container.querySelectorAll('.accordion-item');
        items.forEach((item, index) => {
            const header = item.querySelector('.accordion-header');

            header.addEventListener('click', () => this.toggle(item));

            // Add icon if not present
            if (!header.querySelector('.accordion-icon')) {
                const icon = document.createElement('div');
                icon.className = 'accordion-icon';
                icon.innerHTML = '▼';
                header.appendChild(icon);
            }

            // Open default
            if (this.options.defaultOpen === index) {
                item.classList.add('active');
            }
        });
    }

    toggle(item) {
        const isActive = item.classList.contains('active');

        if (!this.options.allowMultiple) {
            this.container.querySelectorAll('.accordion-item').forEach(i => {
                i.classList.remove('active');
            });
        }

        if (!isActive) {
            item.classList.add('active');
        } else if (this.options.allowMultiple) {
            item.classList.remove('active');
        }
    }
}

// ===== 10. PROGRESS STEPS INDICATOR =====
class ProgressSteps {
    constructor(container, steps, currentStep = 0) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        if (!this.container) return;

        this.steps = steps;
        this.currentStep = currentStep;

        this.init();
    }

    init() {
        this.container.classList.add('progress-steps');

        const bar = document.createElement('div');
        bar.className = 'progress-steps-bar';
        this.container.appendChild(bar);
        this.bar = bar;

        this.steps.forEach((step, index) => {
            const stepEl = document.createElement('div');
            stepEl.className = 'progress-step';
            stepEl.innerHTML = `
                <div class="progress-step-circle">${step.icon || index + 1}</div>
                <div class="progress-step-label">${step.label}</div>
            `;

            stepEl.addEventListener('click', () => this.goToStep(index));
            this.container.appendChild(stepEl);
        });

        this.updateProgress();
    }

    goToStep(index) {
        if (index >= 0 && index < this.steps.length) {
            this.currentStep = index;
            this.updateProgress();

            if (this.steps[index].onClick) {
                this.steps[index].onClick(index);
            }
        }
    }

    updateProgress() {
        const stepEls = this.container.querySelectorAll('.progress-step');
        const progress = this.currentStep / (this.steps.length - 1) * 100;

        this.bar.style.width = progress + '%';

        stepEls.forEach((el, index) => {
            el.classList.remove('completed', 'active');

            if (index < this.currentStep) {
                el.classList.add('completed');
            } else if (index === this.currentStep) {
                el.classList.add('active');
            }
        });
    }

    next() {
        this.goToStep(this.currentStep + 1);
    }

    prev() {
        this.goToStep(this.currentStep - 1);
    }
}

// ===== 11. MAGNETIC BUTTONS =====
class MagneticButton {
    constructor(element) {
        this.element = typeof element === 'string' ? document.querySelector(element) : element;
        if (!this.element) return;

        this.strength = 0.3;

        this.init();
    }

    init() {
        this.element.classList.add('magnetic-btn');

        // Wrap text
        if (!this.element.querySelector('.magnetic-btn-text')) {
            const text = this.element.innerHTML;
            this.element.innerHTML = `<span class="magnetic-btn-text">${text}</span>`;
        }

        this.text = this.element.querySelector('.magnetic-btn-text');

        this.element.addEventListener('mousemove', (e) => this.onMove(e));
        this.element.addEventListener('mouseleave', () => this.onLeave());
    }

    onMove(e) {
        const rect = this.element.getBoundingClientRect();
        const x = e.clientX - rect.left - rect.width / 2;
        const y = e.clientY - rect.top - rect.height / 2;

        this.element.style.transform = `translate(${x * this.strength}px, ${y * this.strength}px)`;
        this.text.style.transform = `translate(${x * this.strength * 0.5}px, ${y * this.strength * 0.5}px)`;
    }

    onLeave() {
        this.element.style.transform = '';
        this.text.style.transform = '';
    }

    static applyToAll(selector = '.magnetic-btn') {
        document.querySelectorAll(selector).forEach(el => new MagneticButton(el));
    }
}

// ===== 12. STAGGERED LIST ANIMATIONS =====
class StaggeredList {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        if (!this.container) return;

        this.options = {
            direction: options.direction || 'left', // left, right, up, down, scale
            delay: options.delay || 100,
            threshold: options.threshold || 0.1
        };

        this.init();
    }

    init() {
        this.container.classList.add('stagger-list', `stagger-${this.options.direction}`);

        const items = this.container.children;
        Array.from(items).forEach((item, index) => {
            item.classList.add('stagger-item');
            item.style.transitionDelay = `${index * this.options.delay}ms`;
        });

        // Observe container
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    Array.from(this.container.children).forEach(item => {
                        item.classList.add('stagger-visible');
                    });
                    observer.unobserve(this.container);
                }
            });
        }, { threshold: this.options.threshold });

        observer.observe(this.container);
    }
}

// ===== 13. FLOATING ORBS =====
class FloatingOrbs {
    constructor() {
        this.init();
    }

    init() {
        const container = document.createElement('div');
        container.className = 'floating-orbs';

        for (let i = 0; i < 4; i++) {
            const orb = document.createElement('div');
            orb.className = 'floating-orb';
            container.appendChild(orb);
        }

        document.body.appendChild(container);
    }
}

// ===== 14. GRADIENT BORDER CARD =====
class GradientBorderCard {
    static apply(element) {
        const el = typeof element === 'string' ? document.querySelector(element) : element;
        if (!el) return;

        el.classList.add('gradient-border-card');

        const content = el.innerHTML;
        el.innerHTML = `<div class="gradient-border-card-inner">${content}</div>`;
    }
}

// ===== 15. SKELETON LOADING =====
class Skeleton {
    static text(lines = 3, container) {
        const el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;

        let html = '';
        for (let i = 0; i < lines; i++) {
            const width = Math.floor(Math.random() * 40) + 60;
            html += `<div class="skeleton skeleton-text" style="width: ${width}%"></div>`;
        }
        el.innerHTML = html;
    }

    static card(container) {
        const el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;

        el.innerHTML = `
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-text skeleton-lg" style="width: 70%; margin-top: 16px"></div>
            <div class="skeleton skeleton-text" style="width: 90%"></div>
            <div class="skeleton skeleton-text" style="width: 60%"></div>
        `;
    }

    static avatar(container) {
        const el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el) return;

        el.innerHTML = `
            <div style="display: flex; gap: 12px; align-items: center">
                <div class="skeleton skeleton-avatar"></div>
                <div style="flex: 1">
                    <div class="skeleton skeleton-text" style="width: 40%"></div>
                    <div class="skeleton skeleton-text skeleton-sm" style="width: 60%"></div>
                </div>
            </div>
        `;
    }
}

// ===== INITIALIZE BATCH 11 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Visual Effects - Disabled (too heavy)
        // new FloatingOrbs();
        // new InteractiveGrid();

        // UI Components - Keep only modal
        window.modal = new ModalSystem();

        // Apply effects - Keep ripple only
        new RippleEffect('button, .btn');
        // ScrollReveal disabled - conflicts with existing animations

        // Parallax and magnetic disabled - too heavy

        // Demo toast disabled - annoying popup

        console.log('%c Batch 11 Loaded (Clean) ', 'background: #ff00ff; color: #fff; padding: 3px 8px;');
    }, 4500);
});

// ============================================
// BATCH 12: ABSOLUTE ULTIMATE - FINAL ASCENSION
// ============================================

// ===== 1. DRAG & DROP SORTABLE =====
class SortableList {
    constructor(container) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        if (!this.container) return;

        this.draggedItem = null;

        this.init();
    }

    init() {
        this.container.classList.add('sortable-list');

        const items = this.container.querySelectorAll('.sortable-item');
        items.forEach(item => this.setupItem(item));
    }

    setupItem(item) {
        item.draggable = true;

        item.addEventListener('dragstart', (e) => {
            this.draggedItem = item;
            item.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            this.container.querySelectorAll('.sortable-item').forEach(i => {
                i.classList.remove('drag-over');
            });
            this.draggedItem = null;
        });

        item.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (item !== this.draggedItem) {
                item.classList.add('drag-over');
            }
        });

        item.addEventListener('dragleave', () => {
            item.classList.remove('drag-over');
        });

        item.addEventListener('drop', (e) => {
            e.preventDefault();
            if (item !== this.draggedItem) {
                const allItems = [...this.container.querySelectorAll('.sortable-item')];
                const draggedIndex = allItems.indexOf(this.draggedItem);
                const droppedIndex = allItems.indexOf(item);

                if (draggedIndex < droppedIndex) {
                    item.after(this.draggedItem);
                } else {
                    item.before(this.draggedItem);
                }
            }
            item.classList.remove('drag-over');
        });
    }
}

// ===== 2. INFINITE SCROLL =====
class InfiniteScroll {
    constructor(container, loadMore) {
        this.container = typeof container === 'string' ? document.querySelector(container) : container;
        if (!this.container) return;

        this.loadMore = loadMore;
        this.isLoading = false;
        this.hasMore = true;
        this.page = 1;

        this.init();
    }

    init() {
        this.container.classList.add('infinite-scroll-container');

        // Create loader
        this.loader = document.createElement('div');
        this.loader.className = 'infinite-scroll-loader';
        this.loader.innerHTML = '<div class="infinite-scroll-spinner"></div>';
        this.container.appendChild(this.loader);

        // Observe loader
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !this.isLoading && this.hasMore) {
                this.load();
            }
        }, { threshold: 0.1 });

        observer.observe(this.loader);
    }

    async load() {
        this.isLoading = true;
        this.loader.classList.add('loading');

        try {
            const result = await this.loadMore(this.page);
            this.page++;

            if (result.hasMore === false) {
                this.hasMore = false;
                this.loader.innerHTML = '<div class="infinite-scroll-end">No more items</div>';
            }
        } catch (error) {
            console.error('Infinite scroll error:', error);
        }

        this.isLoading = false;
        this.loader.classList.remove('loading');
    }
}

// ===== 3. IMAGE LAZY LOADING =====
class LazyImages {
    constructor() {
        this.init();
    }

    init() {
        const images = document.querySelectorAll('img[data-src]');

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.addEventListener('load', () => img.classList.add('loaded'));
                    observer.unobserve(img);
                }
            });
        }, { rootMargin: '50px' });

        images.forEach(img => {
            // Wrap in container if not already
            if (!img.parentElement.classList.contains('lazy-image')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'lazy-image';
                img.parentElement.insertBefore(wrapper, img);
                wrapper.appendChild(img);

                const placeholder = document.createElement('div');
                placeholder.className = 'lazy-image-placeholder';
                wrapper.appendChild(placeholder);
            }

            observer.observe(img);
        });
    }
}

// ===== 4. OFFLINE/ONLINE INDICATOR =====
class ConnectionStatus {
    constructor() {
        this.indicator = null;
        this.hideTimeout = null;

        this.init();
    }

    init() {
        this.indicator = document.createElement('div');
        this.indicator.className = 'connection-status';
        this.indicator.innerHTML = `
            <div class="connection-status-dot"></div>
            <span class="connection-status-text"></span>
        `;
        document.body.appendChild(this.indicator);

        window.addEventListener('online', () => this.show(true));
        window.addEventListener('offline', () => this.show(false));

        // Initial check
        if (!navigator.onLine) {
            this.show(false);
        }
    }

    show(isOnline) {
        clearTimeout(this.hideTimeout);

        this.indicator.className = `connection-status ${isOnline ? 'online' : 'offline'} visible`;
        this.indicator.querySelector('.connection-status-text').textContent =
            isOnline ? 'Back Online!' : 'You are Offline';

        if (isOnline) {
            this.hideTimeout = setTimeout(() => {
                this.indicator.classList.remove('visible');
            }, 3000);
        }

        window.dynamicIsland?.show({
            icon: isOnline ? '📶' : '📵',
            title: isOnline ? 'Connected' : 'Disconnected',
            message: isOnline ? 'Internet connection restored' : 'No internet connection',
            type: isOnline ? 'success' : 'error',
            duration: 3000
        });
    }
}

// ===== 5. SCROLL DIRECTION DETECTION =====
class ScrollDirection {
    constructor() {
        this.lastScroll = 0;
        this.direction = 'down';
        this.callbacks = { up: [], down: [] };

        this.init();
    }

    init() {
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > this.lastScroll && this.direction !== 'down') {
                this.direction = 'down';
                this.callbacks.down.forEach(cb => cb());
            } else if (currentScroll < this.lastScroll && this.direction !== 'up') {
                this.direction = 'up';
                this.callbacks.up.forEach(cb => cb());
            }

            this.lastScroll = currentScroll;
        });
    }

    onScrollUp(callback) {
        this.callbacks.up.push(callback);
    }

    onScrollDown(callback) {
        this.callbacks.down.push(callback);
    }
}

// ===== 6. SMART NAVBAR =====
class SmartNavbar {
    constructor() {
        this.navbar = null;
        this.lastScroll = 0;

        this.init();
    }

    init() {
        this.navbar = document.createElement('nav');
        this.navbar.className = 'smart-navbar';
        this.navbar.innerHTML = `
            <div class="smart-navbar-inner">
                <div class="smart-navbar-logo">🚀 Premium</div>
                <div class="smart-navbar-links">
                    <a href="#" class="smart-navbar-link">Home</a>
                    <a href="#features" class="smart-navbar-link">Features</a>
                    <a href="#pricing" class="smart-navbar-link">Pricing</a>
                    <a href="#contact" class="smart-navbar-link">Contact</a>
                </div>
            </div>
        `;

        document.body.prepend(this.navbar);

        window.addEventListener('scroll', () => this.handleScroll());
    }

    handleScroll() {
        const currentScroll = window.pageYOffset;

        // Add shadow when scrolled
        this.navbar.classList.toggle('scrolled', currentScroll > 50);

        // Hide/show on scroll direction
        if (currentScroll > this.lastScroll && currentScroll > 100) {
            this.navbar.classList.add('hidden');
        } else {
            this.navbar.classList.remove('hidden');
        }

        this.lastScroll = currentScroll;
    }
}

// ===== 7. COPY TO CLIPBOARD =====
class CopyToClipboard {
    static copy(text, button) {
        navigator.clipboard.writeText(text).then(() => {
            if (button) {
                button.classList.add('copied');
                setTimeout(() => button.classList.remove('copied'), 2000);
            }

            window.toast?.success('Copied to clipboard!');
        }).catch(err => {
            window.toast?.error('Failed to copy');
        });
    }

    static createButton(text) {
        const btn = document.createElement('button');
        btn.className = 'copy-btn';
        btn.innerHTML = `
            <span class="copy-btn-icon">📋</span>
            Copy
            <span class="copy-feedback">Copied!</span>
        `;
        btn.addEventListener('click', () => this.copy(text, btn));
        return btn;
    }
}

// ===== 8. PERFORMANCE MONITOR (FPS) =====
class FPSCounter {
    constructor() {
        this.counter = null;
        this.toggle = null;
        this.isVisible = false;
        this.fps = 0;
        this.frames = 0;
        this.lastTime = performance.now();

        this.init();
    }

    init() {
        // Create counter
        this.counter = document.createElement('div');
        this.counter.className = 'fps-counter';
        this.counter.innerHTML = 'FPS: 60';
        document.body.appendChild(this.counter);

        // Create toggle
        this.toggle = document.createElement('button');
        this.toggle.className = 'fps-toggle';
        this.toggle.innerHTML = '📊';
        this.toggle.title = 'Toggle FPS Counter (Alt+P)';
        document.body.appendChild(this.toggle);

        this.toggle.addEventListener('click', () => this.toggleVisibility());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'p' && e.altKey) {
                e.preventDefault();
                this.toggleVisibility();
            }
        });

        // Start measuring
        this.measure();
    }

    toggleVisibility() {
        this.isVisible = !this.isVisible;
        this.counter.classList.toggle('visible', this.isVisible);
    }

    measure() {
        this.frames++;
        const currentTime = performance.now();

        if (currentTime - this.lastTime >= 1000) {
            this.fps = this.frames;
            this.frames = 0;
            this.lastTime = currentTime;

            this.counter.innerHTML = `FPS: ${this.fps}`;
            this.counter.classList.remove('low', 'critical');

            if (this.fps < 30) {
                this.counter.classList.add('critical');
            } else if (this.fps < 50) {
                this.counter.classList.add('low');
            }
        }

        requestAnimationFrame(() => this.measure());
    }
}

// ===== 9. CONFETTI PARTY MODE =====
class ConfettiParty {
    constructor() {
        this.container = null;
        this.toggle = null;
        this.isActive = false;
        this.interval = null;
        this.colors = ['#ff6b6b', '#ffd93d', '#6bcb77', '#4d96ff', '#f093fb', '#667eea'];

        this.init();
    }

    init() {
        // Create container
        this.container = document.createElement('div');
        this.container.className = 'confetti-container';
        document.body.appendChild(this.container);

        // Create toggle
        this.toggle = document.createElement('button');
        this.toggle.className = 'party-toggle';
        this.toggle.innerHTML = '🎉';
        this.toggle.title = 'Party Mode! (Alt+Y)';
        document.body.appendChild(this.toggle);

        this.toggle.addEventListener('click', () => this.toggleParty());

        document.addEventListener('keydown', (e) => {
            if (e.key === 'y' && e.altKey) {
                e.preventDefault();
                this.toggleParty();
            }
        });
    }

    toggleParty() {
        this.isActive = !this.isActive;

        if (this.isActive) {
            this.interval = setInterval(() => this.createConfetti(), 50);

            window.dynamicIsland?.show({
                icon: '🎉',
                title: 'Party Mode!',
                message: 'Let\'s celebrate!',
                type: 'success',
                duration: 2000
            });
        } else {
            clearInterval(this.interval);
        }
    }

    createConfetti() {
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.backgroundColor = this.colors[Math.floor(Math.random() * this.colors.length)];
        confetti.style.width = (Math.random() * 10 + 5) + 'px';
        confetti.style.height = confetti.style.width;
        confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';

        this.container.appendChild(confetti);

        setTimeout(() => confetti.classList.add('active'), 10);
        setTimeout(() => confetti.remove(), 4000);
    }

    burst(x, y, count = 50) {
        for (let i = 0; i < count; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti active';
                confetti.style.left = x + 'px';
                confetti.style.top = y + 'px';
                confetti.style.backgroundColor = this.colors[Math.floor(Math.random() * this.colors.length)];
                confetti.style.width = (Math.random() * 10 + 5) + 'px';
                confetti.style.height = confetti.style.width;

                this.container.appendChild(confetti);
                setTimeout(() => confetti.remove(), 3000);
            }, i * 20);
        }
    }
}

// ===== 10. PAGE VISIBILITY HANDLER =====
class PageVisibility {
    constructor() {
        this.overlay = null;
        this.wasHidden = false;

        this.init();
    }

    init() {
        this.overlay = document.createElement('div');
        this.overlay.className = 'visibility-overlay';
        this.overlay.innerHTML = `
            <div class="visibility-message">👋</div>
            <div class="visibility-text">Welcome Back!</div>
            <div class="visibility-subtext">We missed you</div>
        `;
        document.body.appendChild(this.overlay);

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.wasHidden = true;
                document.title = '👋 Come back! - Premium';
            } else {
                if (this.wasHidden) {
                    document.title = '🎉 Welcome Back! - Premium';
                    this.showWelcome();

                    setTimeout(() => {
                        document.title = 'Premium Trading Platform';
                    }, 3000);
                }
            }
        });
    }

    showWelcome() {
        this.overlay.classList.add('active');
        setTimeout(() => this.overlay.classList.remove('active'), 2000);
    }
}

// ===== 11. CURSOR TRAIL PARTICLES =====
class CursorParticles {
    constructor() {
        this.isActive = false;
        this.toggle = null;
        this.colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#ffd700'];

        this.init();
    }

    init() {
        // Create toggle
        this.toggle = document.createElement('button');
        this.toggle.className = 'cursor-particles-toggle';
        this.toggle.innerHTML = '✨';
        this.toggle.title = 'Toggle Cursor Particles';
        document.body.appendChild(this.toggle);

        this.toggle.addEventListener('click', () => this.toggleParticles());

        document.addEventListener('mousemove', (e) => {
            if (this.isActive) {
                this.createParticle(e.clientX, e.clientY);
            }
        });
    }

    toggleParticles() {
        this.isActive = !this.isActive;
        this.toggle.classList.toggle('active', this.isActive);

        window.dynamicIsland?.show({
            icon: '✨',
            title: 'Cursor Particles',
            message: this.isActive ? 'Sparkles enabled!' : 'Sparkles disabled',
            type: 'info',
            duration: 2000
        });
    }

    createParticle(x, y) {
        if (Math.random() > 0.3) return; // Throttle

        const particle = document.createElement('div');
        particle.className = 'cursor-particle';

        const size = Math.random() * 8 + 4;
        const color = this.colors[Math.floor(Math.random() * this.colors.length)];

        particle.style.cssText = `
            left: ${x}px;
            top: ${y}px;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            box-shadow: 0 0 ${size}px ${color};
        `;

        document.body.appendChild(particle);
        setTimeout(() => particle.remove(), 1000);
    }
}

// ===== 12. BUTTON LOADING STATE =====
class ButtonLoader {
    static start(button) {
        button.classList.add('btn-loading');
        button.disabled = true;
    }

    static stop(button) {
        button.classList.remove('btn-loading');
        button.disabled = false;
    }

    static async withLoading(button, asyncFn) {
        this.start(button);
        try {
            return await asyncFn();
        } finally {
            this.stop(button);
        }
    }
}

// ===== 13. KEYBOARD SHORTCUTS MANAGER (DUPLICATE REMOVED - Using original at line 2649) =====

// ===== FINAL SUMMARY DISPLAY =====
class FinalSummary {
    static show() {
        console.log(`
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║     🏆 PREMIUM WEBSITE - ABSOLUTE ULTIMATE EDITION 🏆        ║
║                                                              ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║     📊 STATISTICS:                                           ║
║     ├─ Total CSS Lines: ~11,000+                            ║
║     ├─ Total JS Lines: ~8,500+                              ║
║     ├─ Total Features: 250+                                  ║
║     ├─ Total Batches: 12                                     ║
║     └─ Tier Level: ∞⁵ ABSOLUTE ULTIMATE                     ║
║                                                              ║
║     ✨ FEATURES INCLUDE:                                     ║
║     ├─ AI Chat Widget                                        ║
║     ├─ Voice Commands                                        ║
║     ├─ Dynamic Island Notifications                          ║
║     ├─ Particle Networks                                     ║
║     ├─ Matrix Rain Effect                                    ║
║     ├─ 3D Parallax Cards                                     ║
║     ├─ Premium Modals & Toasts                              ║
║     ├─ Theme Switching                                       ║
║     ├─ Achievement System                                    ║
║     ├─ Performance Monitor                                   ║
║     ├─ And 240+ more features...                            ║
║                                                              ║
║     🎹 KEYBOARD SHORTCUTS: Press Alt+H for full list        ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
        `);
    }
}

// ===== INITIALIZE BATCH 12 FEATURES =====
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Connection & Performance - Disabled (distracting)
        // new ConnectionStatus();
        // new FPSCounter();

        // Fun Features - All disabled (too intrusive)
        // new ConfettiParty();
        // new CursorParticles();
        // new PageVisibility();

        // Navigation - Keep only essential
        new SmartNavbar();
        // ScrollDirection conflicts with other scroll handlers

        // Images - Keep lazy loading
        new LazyImages();

        // Shortcuts - Already in Batch 6
        // new KeyboardShortcuts();

        // Final message disabled

        console.log('%c Batch 12 Loaded (Clean) ', 'background: #6bcb77; color: #000; padding: 3px 8px;');
    }, 5000);
});
