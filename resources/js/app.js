import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

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
    menuPanel.setAttribute('data-lenis-prevent', '');
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

const homeLayouts = new Map([
    ['About Nasenyi', 'opening'],
    ['The people who preserved our faith', 'profile'],
    ['Our pillars', 'pillars'],
    ['Prayer, traditions, cultural identity, learning and festivals', 'topics'],
    ['A Ugandan Seder: Passover 2026 in Nasenyi', 'seder'],
    ['The 8 Kilometers of Faith', 'faith'],
    ['The Legacy of Leadership', 'leadership'],
]);

let pillarsGroup;
for (const section of document.querySelectorAll('.home-story > .story-section')) {
    const layout = homeLayouts.get(section.querySelector('h2, h3')?.textContent.trim());
    if (layout) section.classList.add(`story-layout--${layout}`);

    if (layout === 'pillars') {
        pillarsGroup = document.createElement('div');
        pillarsGroup.className = 'pillars-group';
        section.before(pillarsGroup);
        pillarsGroup.append(section);
    } else if (pillarsGroup && section.querySelector(':scope > .story-card')) {
        pillarsGroup.append(section);
    } else {
        pillarsGroup = null;
    }
}

for (const section of document.querySelectorAll('.story-body > .story-section')) {
    if ([...section.classList].some(name => name.startsWith('story-layout--'))) continue;

    const columns = section.querySelector(':scope > .story-columns');
    const photo = section.firstElementChild;

    if (columns?.querySelectorAll(':scope > .story-column').length > 1) {
        section.classList.add('story-layout--spread');
    } else if (photo?.matches('figure:has(img)') && section.querySelector('p')) {
        section.classList.add('story-layout--illustrated');
        const spread = document.createElement('div');
        const copy = document.createElement('div');
        spread.className = 'story-photo-spread';
        copy.className = 'story-photo-copy';
        copy.append(...[...section.childNodes].filter(node => node !== photo));
        spread.append(photo, copy);
        section.append(spread);
    }
}

for (const section of document.querySelectorAll('.home-story .story-layout--faith')) {
    if (section.querySelector('iframe')) continue;

    const link = section.querySelector('a[href*="youtube.com/watch"], a[href*="youtu.be/"]');
    const paragraph = link?.closest('p');
    if (!paragraph) continue;

    const url = new URL(link.href);
    const videoId = url.hostname === 'youtu.be' ? url.pathname.slice(1)
        : ['www.youtube.com', 'youtube.com', 'm.youtube.com'].includes(url.hostname) ? url.searchParams.get('v') : '';
    if (!/^[A-Za-z0-9_-]{11}$/.test(videoId ?? '')) continue;

    const preview = document.createElement('div');
    preview.className = 'story-video';
    const player = document.createElement('iframe');
    player.title = 'The 8 Kilometers of Faith video';
    player.loading = 'lazy';
    player.allow = 'autoplay; encrypted-media; picture-in-picture';
    player.allowFullscreen = true;
    player.referrerPolicy = 'strict-origin-when-cross-origin';
    const play = motionPreference.matches ? '0' : '1';
    player.src = `https://www.youtube-nocookie.com/embed/${videoId}?autoplay=${play}&mute=1&playsinline=1&loop=1&playlist=${videoId}`;
    preview.append(player);
    paragraph.after(preview);
}

if (!motionPreference.matches && 'IntersectionObserver' in window) {
    const countUp = new IntersectionObserver((entries, observation) => {
        for (const { target, isIntersecting } of entries) {
            if (!isIntersecting) continue;

            observation.unobserve(target);
            const original = target.textContent.trim();
            const match = original.match(/^(\D*)([\d,]+(?:\.\d+)?)(\D*)$/);
            if (!match) continue;

            const [, prefix, rawNumber, suffix] = match;
            const value = Number(rawNumber.replaceAll(',', ''));
            if (!Number.isFinite(value)) continue;

            const decimals = (rawNumber.split('.')[1] || '').length;
            const formatter = new Intl.NumberFormat(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
            const start = performance.now();

            const update = timestamp => {
                const progress = Math.min((timestamp - start) / 1400, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                target.textContent = `${prefix}${formatter.format(value * eased)}${suffix}`;

                if (progress < 1) {
                    requestAnimationFrame(update);
                } else {
                    target.textContent = original;
                }
            };

            target.textContent = `${prefix}0${suffix}`;
            requestAnimationFrame(update);
        }
    }, { threshold: 0.35 });

    for (const counter of document.querySelectorAll('.home-story .story-layout--opening .story-stat strong')) {
        countUp.observe(counter);
    }
}

for (const hero of document.querySelectorAll('[data-hero]')) {
    const slides = [...hero.querySelectorAll('[data-slide]')];
    const dots = [...hero.querySelectorAll('[data-slide-to]')];
    const controls = hero.querySelector('[data-hero-controls]');
    if (!controls || slides.length < 2) continue;

    const pauseButton = hero.querySelector('[data-slide-pause]');
    const interval = Number(hero.dataset.heroInterval) || 7500;
    let index = 0;
    let timer;
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
        const running = !paused && !document.hidden && !hero.contains(document.activeElement);
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
        ['.story-columns > .story-column', 'up'],
        ['.story-gallery figure, .gallery-grid figure, .story-grid > .story-card, .story-layout--topics .story-card, .story-layout--leadership .story-card, .pillars-group .story-card', 'zoom'],
        ['[data-reveal], .story-section > :is(h2, h3, figure, .story-video), .event-list li, .contact-layout > *', 'up'],
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

const pinnedSections = [...document.querySelectorAll('[data-pinned-cta]')];
let lenis = null;
let lenisRaf = null;
let matchMediaContext = null;
let scrollLocked = false;
let scrollReady = false;

const syncScrollLock = () => {
    if (!lenis) return;
    const locked = document.body.classList.contains('nav-open') || document.querySelectorAll('dialog[open]').length > 0;
    if (locked === scrollLocked) return;
    scrollLocked = locked;
    if (locked) lenis.stop();
    else lenis.start();
};

if (typeof HTMLDialogElement !== 'undefined') {
    const nativeShowModal = HTMLDialogElement.prototype.showModal;
    const nativeClose = HTMLDialogElement.prototype.close;
    HTMLDialogElement.prototype.showModal = function (...args) {
        const result = nativeShowModal.apply(this, args);
        syncScrollLock();
        return result;
    };
    HTMLDialogElement.prototype.close = function (...args) {
        const result = nativeClose.apply(this, args);
        syncScrollLock();
        return result;
    };
}

new MutationObserver(syncScrollLock).observe(document.body, { attributes: true, attributeFilter: ['class'] });
document.addEventListener('close', syncScrollLock, true);

document.addEventListener('click', event => {
    if (!lenis || event.defaultPrevented || event.button !== 0 || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
    const link = event.target.closest('a[href^="#"]');
    if (!link || link.hash.length <= 1) return;
    let id;
    try {
        id = decodeURIComponent(link.hash.slice(1));
    } catch {
        return;
    }
    const target = document.getElementById(id);
    if (!target) return;
    event.preventDefault();
    const offset = header ? -header.getBoundingClientRect().height : 0;
    lenis.scrollTo(target, { offset, duration: 1.15 });
    history.pushState(null, '', link.hash);
});

const clearPinnedStyles = () => {
    for (const section of pinnedSections) {
        gsap.set(section.querySelectorAll('[data-pinned-cta-media], [data-pinned-cta-ring], [data-pinned-cta-content]'), { clearProps: 'all' });
    }
};

const destroySmoothScroll = () => {
    if (matchMediaContext) {
        matchMediaContext.revert();
        matchMediaContext = null;
    }
    if (lenisRaf) {
        gsap.ticker.remove(lenisRaf);
        lenisRaf = null;
    }
    if (lenis) {
        lenis.destroy();
        lenis = null;
    }
    window.__lenis = null;
    scrollLocked = false;
    scrollReady = false;
    document.documentElement.classList.remove('has-anim');
    clearPinnedStyles();
};

const initializeSmoothScroll = () => {
    if (scrollReady || motionPreference.matches) return;
    gsap.registerPlugin(ScrollTrigger);
    scrollReady = true;
    document.documentElement.classList.add('has-anim');
    try {
        lenis = new Lenis({
            duration: 1.15,
            easing: t => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
            smoothWheel: true,
            syncTouch: false,
            wheelMultiplier: 1,
            touchMultiplier: 1.6,
            autoResize: true,
        });
        lenis.on('scroll', ScrollTrigger.update);
        lenisRaf = time => lenis.raf(time * 1000);
        gsap.ticker.add(lenisRaf);
        gsap.ticker.lagSmoothing(0);
        window.__lenis = lenis;
        document.dispatchEvent(new CustomEvent('site:lenis', { detail: lenis }));

        matchMediaContext = gsap.matchMedia();
        matchMediaContext.add('(min-width: 1024px)', () => {
            const cleanups = [];
            for (const section of pinnedSections) {
                const media = section.querySelector('[data-pinned-cta-media]');
                const ring = section.querySelector('[data-pinned-cta-ring]');
                const content = section.querySelector('[data-pinned-cta-content]');
                if (!media || !content) continue;

                const timeline = gsap.timeline({
                    scrollTrigger: {
                        trigger: section,
                        pin: true,
                        start: 'top top',
                        end: '+=200%',
                        scrub: 1,
                        anticipatePin: 1,
                    },
                });
                timeline.fromTo(media,
                    { clipPath: 'circle(84px at 50% 50%)', scale: 1.18 },
                    { clipPath: 'circle(120% at 50% 50%)', scale: 1, duration: 2, ease: 'power2.in' }, 0);
                if (ring) {
                    timeline.fromTo(ring,
                        { scale: 2, autoAlpha: 1 },
                        { scale: 420, duration: 2, ease: 'power2.in', autoAlpha: 0 }, 0);
                }
                timeline.to(content, { autoAlpha: 1, scale: 1, duration: 1, ease: 'power2.out' }, '-=1');
                cleanups.push(() => {
                    timeline.scrollTrigger?.kill();
                    timeline.kill();
                });
            }
            ScrollTrigger.refresh();
            return () => cleanups.forEach(cleanup => cleanup());
        });

        const refresh = () => ScrollTrigger.refresh();
        window.addEventListener('load', refresh, { once: true });
        for (const image of document.querySelectorAll('[data-pinned-cta] img')) {
            if (image.complete) continue;
            image.addEventListener('load', refresh, { once: true });
            image.addEventListener('error', refresh, { once: true });
        }
        syncScrollLock();
    } catch (error) {
        destroySmoothScroll();
        console.error('[nasenyi motion]', error);
    }
};

motionPreference.addEventListener('change', () => {
    if (motionPreference.matches) destroySmoothScroll();
    else initializeSmoothScroll();
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSmoothScroll, { once: true });
} else {
    initializeSmoothScroll();
}
