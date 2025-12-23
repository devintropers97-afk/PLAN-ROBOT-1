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
        // Particle Trail
        new ParticleTrail({
            particleCount: 15,
            colors: ['#00d4ff', '#7c3aed', '#00ff88', '#ff6b6b'],
            fadeTime: 800
        });

        // Spotlight Effect on cards
        new SpotlightEffect('.feature-card, .pricing-card, .strategy-card');

        // Enhanced Counters
        new EnhancedCounter();

        // Animated Icons
        new AnimatedIcons();

        // Typewriter for hero tagline (if element exists)
        const heroTagline = document.querySelector('.hero-tagline');
        if (heroTagline) {
            const originalText = heroTagline.textContent.trim();
            const texts = [
                originalText,
                'Automated Trading Revolution',
                'Smart Money Management',
                'AI-Powered Strategies'
            ];
            heroTagline.textContent = '';
            new TypewriterEffect(heroTagline, texts);
        }

        // Text Scramble for section titles on hover
        document.querySelectorAll('.section-title').forEach(title => {
            const scrambler = new TextScramble(title);
            const originalText = title.textContent;

            title.addEventListener('mouseenter', () => {
                scrambler.setText(originalText);
            });
        });

        console.log('%c Batch 3 Premium Features Loaded ',
            'background: linear-gradient(135deg, #00ff88, #00d4ff); color: #000; padding: 5px 10px; font-size: 12px; border-radius: 3px;');
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
