import './photo-uploader';

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

/**
 * Обрані тварини ($store.favorites).
 * Гість: список id у localStorage цього браузера.
 * Користувач, який увійшов: список із сервера (meta[name=favorites]); зміни зберігаються в базі.
 * Після входу обрані з браузера один раз переносяться в акаунт.
 */
const STORAGE_KEY = 'domivka:favorites';

const readLocal = () => {
    try {
        return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]').map(Number).filter(Boolean);
    } catch {
        return [];
    }
};
const writeLocal = (ids) => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
    } catch {
        /* приватний режим без сховища – обрані живуть до перезавантаження */
    }
};

const registerFavorites = () => {
    const meta = document.querySelector('meta[name="favorites"]');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const post = (url, body) => fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
        body: body ? JSON.stringify(body) : null,
    });

    window.Alpine.store('favorites', {
        ids: meta ? JSON.parse(meta.content) : readLocal(),
        loggedIn: Boolean(meta),

        init() {
            if (!this.loggedIn) return;
            const local = readLocal();
            if (!local.length) return;
            post(meta.dataset.sync, { ids: local })
                .then((r) => (r.ok ? r.json() : Promise.reject()))
                .then((data) => {
                    this.ids = data.ids;
                    writeLocal([]);
                })
                .catch(() => {});
        },

        has(id) {
            return this.ids.includes(id);
        },

        toggle(id) {
            const wasFavorite = this.has(id);
            this.ids = wasFavorite ? this.ids.filter((x) => x !== id) : [id, ...this.ids];

            if (!this.loggedIn) {
                writeLocal(this.ids);
                return;
            }
            post(`${meta.dataset.toggle}/${id}`).then((r) => {
                if (!r.ok) throw new Error();
            }).catch(() => {
                // Сервер не зберіг – повертаємо як було
                this.ids = wasFavorite ? [id, ...this.ids] : this.ids.filter((x) => x !== id);
            });
        },
    });
};

if (window.Alpine) {
    registerFavorites();
} else {
    document.addEventListener('alpine:init', registerFavorites);
}
