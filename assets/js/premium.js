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
    // Initialize components
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

    console.log('%c ZYN Trade Premium Edition ',
        'background: linear-gradient(135deg, #00d4ff, #7c3aed); color: white; padding: 10px 20px; font-size: 16px; font-weight: bold; border-radius: 5px;');
});

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
