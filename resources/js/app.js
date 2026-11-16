/**
 * М'яка поява блоків із data-reveal, коли вони доходять до екрана.
 * Працює лише якщо в <html> є клас reveal (його ставить layout, коли рух не вимкнено в системі).
 * Нові картки, які Livewire додає після фільтрації, підхоплюються через MutationObserver.
 */
const root = document.documentElement;
window.__revealReady = true;

if (root.classList.contains('reveal') && 'IntersectionObserver' in window) {
    const shown = new WeakSet();
    const io = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) continue;
            shown.add(entry.target);
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
        }
    }, { rootMargin: '0px 0px -8% 0px' });

    const watch = (scope) => {
        scope.querySelectorAll?.('[data-reveal]:not(.is-visible)').forEach((el) => {
            // Картки в одному ряду з'являються по черзі, з невеликим кроком
            const siblings = [...el.parentElement.children].filter((c) => c.hasAttribute('data-reveal'));
            el.style.setProperty('--reveal-delay', `${(siblings.indexOf(el) % 4) * 70}ms`);
            io.observe(el);
        });
    };

    watch(document);
    new MutationObserver((mutations) => {
        for (const m of mutations) {
            // Livewire під час оновлення може переписати class – повертаємо вже показаним блокам видимість
            if (m.type === 'attributes') {
                if (shown.has(m.target) && !m.target.classList.contains('is-visible')) m.target.classList.add('is-visible');
                continue;
            }
            m.addedNodes.forEach((n) => n.nodeType === 1 && (n.matches('[data-reveal]') ? watch(n.parentElement) : watch(n)));
        }
    }).observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] });
} else {
    root.classList.remove('reveal');
}
