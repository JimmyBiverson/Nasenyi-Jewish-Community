const menuToggle = document.querySelector('[data-menu-toggle]');
const navigation = document.querySelector('#main-navigation');

if (menuToggle && navigation) {
    const setMenu = open => {
        navigation.toggleAttribute('data-open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
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

for (const hero of document.querySelectorAll('[data-hero]')) {
    const slides = [...hero.querySelectorAll('[data-slide]')];
    const dots = [...hero.querySelectorAll('[data-slide-to]')];
    const controls = hero.querySelector('[data-hero-controls]');
    if (!controls) continue;
    let index = 0;

    const show = next => {
        index = (next + slides.length) % slides.length;
        slides.forEach((slide, position) => { slide.hidden = position !== index; });
        dots.forEach((dot, position) => dot.setAttribute('aria-pressed', String(position === index)));
    };

    controls.hidden = false;
    hero.querySelector('[data-slide-prev]').addEventListener('click', () => show(index - 1));
    hero.querySelector('[data-slide-next]').addEventListener('click', () => show(index + 1));
    dots.forEach((dot, position) => dot.addEventListener('click', () => show(position)));
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
            image.alt = link.querySelector('img').alt;
            caption.textContent = link.dataset.caption;
            lightbox.showModal();
        });
    }
}
