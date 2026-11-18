const menuToggle = document.querySelector('[data-menu-toggle]');
const navigation = document.querySelector('#main-navigation');
const menuPanel = document.querySelector('[data-menu-panel]');
const header = document.querySelector('[data-header]');
const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');

if (menuToggle && navigation && menuPanel) {
    const mobile = window.matchMedia('(max-width: 900px)');
    const isOpen = () => menuPanel.hasAttribute('data-open');
    const setMenu = open => {
        open = mobile.matches && open;
        menuPanel.toggleAttribute('data-open', open);
        navigation.inert = mobile.matches && !open;
        menuToggle.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('nav-open', open);
    };

    menuPanel.setAttribute('data-enhanced', '');
    setMenu(false);
    menuToggle.hidden = false;
    menuToggle.addEventListener('click', () => setMenu(!isOpen()));
    mobile.addEventListener('change', () => {
        if (mobile.matches && navigation.contains(document.activeElement)) menuToggle.focus();
        setMenu(false);
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && isOpen()) {
            setMenu(false);
            menuToggle.focus();
        }
    });

    document.addEventListener('click', event => {
        if (isOpen() && !menuPanel.contains(event.target) && !menuToggle.contains(event.target)) {
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

const progressBar = document.querySelector('[data-scroll-progress]');
let scrollFrame;
const updateScrollUi = () => {
    const y = window.scrollY || document.documentElement.scrollTop;
    if (header) header.classList.toggle('is-scrolled', y > 12);
    if (progressBar) {
        const h = document.documentElement.scrollHeight - window.innerHeight;
        progressBar.style.transform = `scaleX(${h > 0 ? Math.min(y / h, 1) : 0})`;
    }
    scrollFrame = undefined;
};
updateScrollUi();
window.addEventListener('scroll', () => { scrollFrame ??= requestAnimationFrame(updateScrollUi); }, { passive: true });

for (const hero of document.querySelectorAll('[data-hero]')) {
    const slides = [...hero.querySelectorAll('[data-slide]')];
    const dots = [...hero.querySelectorAll('[data-slide-to]')];
    const controls = hero.querySelector('[data-hero-controls]');
    if (!controls || slides.length < 2) continue;

    const pauseButton = hero.querySelector('[data-slide-pause]');
    const interval = Number(hero.dataset.heroInterval) || 7500;
    let index = 0;
    let timer;
    let hovered = false;
    let paused = motionPreference.matches;

    hero.style.setProperty('--hero-cycle', `${interval}ms`);

    const show = next => {
        const previous = index;
        index = (next + slides.length) % slides.length;
        if (index === previous) return;

        slides.forEach((slide, position) => {
            const active = position === index;
            slide.classList.toggle('is-active', active);
            slide.classList.toggle('is-leaving', position === previous);
            slide.setAttribute('aria-hidden', String(!active));
            slide.inert = !active;
        });
        dots.forEach((dot, position) => dot.setAttribute('aria-pressed', String(position === index)));
    };

    const schedule = () => {
        window.clearTimeout(timer);
        const running = !paused && !hovered && !document.hidden && !hero.contains(document.activeElement);
        hero.toggleAttribute('data-hero-running', running);
        if (running) {
            timer = window.setTimeout(() => {
                show(index + 1);
                schedule();
            }, interval);
        }
    };

    const updatePause = () => {
        pauseButton.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow');
        pauseButton.setAttribute('aria-pressed', String(paused));
        pauseButton.textContent = paused ? '▷' : 'Ⅱ';
    };

    controls.hidden = false;
    updatePause();
    pauseButton.addEventListener('click', () => {
        paused = !paused;
        updatePause();
        schedule();
    });
    hero.querySelector('[data-slide-prev]').addEventListener('click', () => { show(index - 1); schedule(); });
    hero.querySelector('[data-slide-next]').addEventListener('click', () => { show(index + 1); schedule(); });
    dots.forEach((dot, position) => dot.addEventListener('click', () => { show(position); schedule(); }));

    hero.addEventListener('pointerenter', event => {
        if (event.pointerType !== 'touch') hovered = true;
        schedule();
    });
    hero.addEventListener('pointerleave', () => { hovered = false; schedule(); });
    hero.addEventListener('focusin', schedule);
    hero.addEventListener('focusout', () => window.setTimeout(schedule, 0));
    document.addEventListener('visibilitychange', schedule);
    motionPreference.addEventListener('change', () => {
        paused = motionPreference.matches;
        updatePause();
        schedule();
    });
    schedule();
}

if (!motionPreference.matches && 'IntersectionObserver' in window) {
    const effects = [
        ['.story-columns > .story-column:has(> figure):not(:has(> .story-columns)), .story-photo-spread > figure', 'left'],
        ['.story-columns > .story-column:not(:has(> figure, > .story-columns, > .story-card)), .story-photo-copy', 'right'],
        ['.section-heading > h2, .story-layout--topics > .story-columns > .story-column > h2, .story-layout--leadership h2', 'down'],
        ['.story-gallery figure, .gallery-grid figure, .pillars-group .story-card', 'zoom'],
        ['[data-reveal], .story-section > :is(h2, h3, figure), .event-list li, .contact-layout > *', 'up'],
    ];
    const targets = new Map();
    for (const [selector, fallback] of effects) {
        for (const element of document.querySelectorAll(selector)) {
            if (!targets.has(element)) targets.set(element, element.dataset.reveal || fallback);
        }
    }
    const belowViewport = [...targets].filter(([element]) => element.getBoundingClientRect().top > window.innerHeight * .88);
    const animated = new Set(belowViewport.map(([element]) => element));
    const stagger = new WeakMap();
    const observer = new IntersectionObserver((entries, observation) => {
        for (const { target, isIntersecting } of entries) {
            if (isIntersecting) {
                target.removeAttribute('data-reveal-pending');
                observation.unobserve(target);
            }
        }
    }, { rootMargin: '0px 0px -5% 0px' });

    for (const [element, effect] of belowViewport) {
        let parent = element.parentElement;
        while (parent && !animated.has(parent)) parent = parent.parentElement;
        if (parent) continue;

        const group = element.parentElement;
        const index = stagger.get(group) ?? 0;
        stagger.set(group, index + 1);
        element.dataset.revealEffect = effect;
        element.style.setProperty('--reveal-delay', `${Math.min(index * 65, 195)}ms`);
        element.setAttribute('data-reveal-pending', '');
        observer.observe(element);
    }
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
