import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const menuButton = document.querySelector('[data-menu-toggle]');
const menu = document.querySelector('#mobile-menu');
const main = document.querySelector('main');
const footer = document.querySelector('footer');

function closeMenu(restoreFocus = true) {
    menu.hidden = true;
    menuButton.setAttribute('aria-expanded', 'false');
    menuButton.setAttribute('aria-label', 'Open navigation');
    document.body.classList.remove('menu-is-open');
    main.inert = false;
    footer.inert = false;
    if (restoreFocus) menuButton.focus();
}

menuButton?.addEventListener('click', () => {
    if (!menu.hidden) return closeMenu();
    menu.hidden = false;
    menuButton.setAttribute('aria-expanded', 'true');
    menuButton.setAttribute('aria-label', 'Close navigation');
    document.body.classList.add('menu-is-open');
    main.inert = true;
    footer.inert = true;
    menu.querySelector('a').focus();
});
menu?.addEventListener('click', event => {
    if (event.target.closest('a')) closeMenu();
});
document.addEventListener('keydown', event => {
    if (!menu || menu.hidden) return;
    if (event.key === 'Escape') closeMenu();
    if (event.key === 'Tab') {
        const controls = [menuButton, ...menu.querySelectorAll('a')];
        const index = controls.indexOf(document.activeElement);
        event.preventDefault();
        controls[(index + (event.shiftKey ? -1 : 1) + controls.length) % controls.length].focus();
    }
});
window.matchMedia('(min-width: 801px)').addEventListener('change', event => {
    if (event.matches && menu && !menu.hidden) closeMenu(false);
});

gsap.registerPlugin(ScrollTrigger);
const motion = gsap.matchMedia();
motion.add('(prefers-reduced-motion: no-preference)', () => {
    if (document.querySelector('.hero-copy')) gsap.from('.hero-copy > *', { y: 16, duration: .75, stagger: .09, ease: 'power2.out', clearProps: 'all' });
    document.querySelectorAll('[data-reveal]').forEach(element => {
        gsap.from(element, { y: 25, opacity: 0, duration: .8, ease: 'power2.out', clearProps: 'all', scrollTrigger: { trigger: element, start: 'top 94%', once: true } });
    });
    if (document.querySelector('.statement-star')) gsap.to('.statement-star', { rotation: 65, ease: 'none', scrollTrigger: { trigger: '.about-section', start: 'top bottom', end: 'bottom top', scrub: 1 } });
});

const globe = document.querySelector('[data-globe]');
if (globe && !reduceMotion.matches && !navigator.connection?.saveData) {
    const activate = globe.querySelector('[data-scene-activate]');
    const startScene = async () => {
        activate.disabled = true;
        activate.textContent = 'Loading 3D…';
        try {
            const module = await import('./hero-scene.js');
            module.createGlobe(globe);
            activate.hidden = true;
        } catch {
            globe.classList.remove('scene-ready');
            activate.textContent = '3D unavailable';
        }
    };
    if (window.matchMedia('(max-width: 800px), (pointer: coarse)').matches) {
        activate.hidden = false;
        activate.addEventListener('click', startScene, { once: true });
    } else if ('requestIdleCallback' in window) requestIdleCallback(startScene, { timeout: 1500 });
    else setTimeout(startScene, 200);
}

const form = document.querySelector('[data-enquiry-form]');
const contactEmail = form?.dataset.contactEmail || 'info@mdarena.in';
form?.addEventListener('submit', async event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    const button = form.querySelector('button[type=submit]');
    if (button.disabled) return;
    const label = form.querySelector('[data-submit-label]');
    const status = form.querySelector('[data-form-status]');
    form.querySelectorAll('[data-error]').forEach(element => { element.textContent = ''; });
    form.querySelectorAll('[aria-invalid]').forEach(element => element.removeAttribute('aria-invalid'));
    button.disabled = true;
    label.textContent = 'Saving your enquiry…';
    status.textContent = '';
    status.classList.remove('is-success');
    try {
        const response = await fetch(form.action, {
            method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(20000),
        });
        if (response.status === 422) {
            const result = await response.json();
            for (const [field, messages] of Object.entries(result.errors || {})) {
                const error = form.querySelector(`[data-error="${CSS.escape(field)}"]`);
                if (error) error.textContent = messages[0];
                form.elements.namedItem(field)?.setAttribute('aria-invalid', 'true');
            }
            status.textContent = 'Please check the highlighted fields and try again.';
            form.querySelector('[aria-invalid=true]')?.focus();
            return;
        }
        if (response.status === 429) throw new Error(`A few too many requests. Please wait a minute, or email ${contactEmail}.`);
        if (response.status === 419) throw new Error('This page has expired. Refresh it before trying again.');
        if (!response.ok) throw new Error(`We couldn’t save your enquiry. Please try again or email ${contactEmail}.`);
        const result = await response.json();
        status.textContent = result.message;
        status.classList.add('is-success');
        form.reset();
        form.elements.namedItem('submission_id').value = crypto.randomUUID();
        status.focus({ preventScroll: true });
    } catch (error) {
        status.textContent = error.name === 'TimeoutError' || error.name === 'TypeError'
            ? `The connection was interrupted. You can try again safely, or email ${contactEmail}.`
            : error.message;
    } finally {
        button.disabled = false;
        label.textContent = 'Let’s start something';
    }
});
