const menuToggle = document.querySelector('[data-menu-toggle]');
const navigation = document.querySelector('#main-navigation');
const header = document.querySelector('[data-header]');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (menuToggle && navigation) {
    const setMenu = open => {
        navigation.toggleAttribute('data-open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('nav-open', open);
    };

    navigation.setAttribute('data-enhanced', '');
    menuToggle.hidden = false;
    menuToggle.addEventListener('click', () => setMenu(menuToggle.getAttribute('aria-expanded') !== 'true'));

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && navigation.hasAttribute('data-open')) {
            setMenu(false);
            menuToggle.focus();
        }
    });

    document.addEventListener('click', event => {
        if (navigation.hasAttribute('data-open') && !navigation.contains(event.target) && !menuToggle.contains(event.target)) {
            setMenu(false);
        }
    });
}

for (const dropdown of document.querySelectorAll('[data-nav-dropdown]')) {
    document.addEventListener('click', event => {
        if (!dropdown.contains(event.target)) dropdown.open = false;
    });
    dropdown.addEventListener('keydown', event => {
        if (event.key === 'Escape' && dropdown.open) {
            event.stopPropagation();
            dropdown.open = false;
            dropdown.querySelector('summary').focus();
        }
    });
}

const updateScrollUi = () => {
    const y = window.scrollY || document.documentElement.scrollTop;
    if (header) header.classList.toggle('is-scrolled', y > 12);
    const bar = document.querySelector('[data-scroll-progress]');
    if (bar) {
        const h = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.transform = `scaleX(${h > 0 ? Math.min(y / h, 1) : 0})`;
    }
};
updateScrollUi();
window.addEventListener('scroll', updateScrollUi, { passive: true });

for (const hero of document.querySelectorAll('[data-hero]')) {
    const slides = [...hero.querySelectorAll('[data-slide]')];
    const dots = [...hero.querySelectorAll('[data-slide-to]')];
    const controls = hero.querySelector('[data-hero-controls]');
    if (slides.length === 0) continue;

    let index = slides.findIndex(slide => slide.classList.contains('is-active'));
    if (index < 0) index = 0;
    let timer;

    const show = (next, user = false) => {
        index = (next + slides.length) % slides.length;
        slides.forEach((slide, position) => {
            const active = position === index;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', String(!active));
        });
        dots.forEach((dot, position) => dot.setAttribute('aria-pressed', String(position === index)));
        if (user) restart();
    };

    const stop = () => { if (timer) window.clearInterval(timer); };
    const start = () => {
        if (reduceMotion || slides.length < 2) return;
        const interval = Number(hero.dataset.heroInterval || 6500);
        timer = window.setInterval(() => show(index + 1), interval);
    };
    const restart = () => { stop(); start(); };

    if (controls && slides.length > 1) {
        controls.hidden = false;
        hero.querySelector('[data-slide-prev]')?.addEventListener('click', () => show(index - 1, true));
        hero.querySelector('[data-slide-next]')?.addEventListener('click', () => show(index + 1, true));
        dots.forEach((dot, position) => dot.addEventListener('click', () => show(position, true)));
    }

    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    hero.addEventListener('focusin', stop);
    hero.addEventListener('focusout', event => {
        if (!hero.contains(event.relatedTarget)) start();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stop(); else start();
    });

    start();
}

const revealTargets = [
    ...document.querySelectorAll('[data-reveal]'),
    ...document.querySelectorAll('.home-story > .story-section'),
    ...document.querySelectorAll('.story-body > .story-section'),
    ...document.querySelectorAll('.listing-section .story-card, .gallery-grid figure, .event-list li, .index-intro, .contact-layout > *'),
];

if (!reduceMotion && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries, obs) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            entry.target.classList.add('is-visible');
            obs.unobserve(entry.target);
        }
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

    revealTargets.forEach((el, i) => {
        if (el.classList.contains('reveal-ready')) return;
        el.classList.add('reveal-ready');
        el.style.setProperty('--reveal-delay', `${Math.min((i % 6) * 60, 240)}ms`);
        observer.observe(el);
    });
} else {
    revealTargets.forEach(el => el.classList.add('is-visible'));
}

const lightbox = document.querySelector('[data-lightbox-dialog]');

if (lightbox && typeof lightbox.showModal === 'function') {
    const image = lightbox.querySelector('img');
    const caption = lightbox.querySelector('p');

    for (const link of document.querySelectorAll('[data-lightbox]')) {
        link.addEventListener('click', event => {
            if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
            event.preventDefault();
            image.src = link.href;
            const innerImg = link.querySelector('img');
            image.alt = innerImg ? innerImg.alt : '';
            caption.textContent = link.dataset.caption || '';
            lightbox.showModal();
        });
    }

    lightbox.addEventListener('click', event => {
        if (event.target === lightbox) lightbox.close();
    });
}
