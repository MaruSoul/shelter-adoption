// Навантажувальний тест NFR-07 (k6): 50 одночасних віртуальних користувачів.
//   40 VU переглядають головну, каталог із фільтрами й сторінки тварин;
//   10 VU подають анкету «Взяти додому» (той самий запит Livewire, що й браузер).
// Запуск (сайт із демо-даними; для стенда ADOPTION_MAX_PER_HOUR підвищено, бо всі VU мають одну IP-адресу):
//   k6 run -e BASE_URL=http://localhost:8080 tests/load/catalog-and-form.js
import http from 'k6/http';
import { check, sleep } from 'k6';
import { parseHTML } from 'k6/html';

const BASE = __ENV.BASE_URL || 'http://localhost:8080';
const DURATION = __ENV.DURATION || '60s';
const THINK = Number(__ENV.THINK || 1); // пауза «на читання» між діями, с

export const options = {
    scenarios: {
        browse: { executor: 'constant-vus', vus: 40, duration: DURATION, exec: 'browse' },
        apply: { executor: 'constant-vus', vus: 10, duration: DURATION, exec: 'apply' },
    },
    thresholds: {
        http_req_failed: ['rate<0.01'],
        'http_req_duration{scenario:browse}': ['p(95)<500'],
        'http_req_duration{scenario:apply}': ['p(95)<500'],
        checks: ['rate>0.99'],
    },
};

const FILTERS = ['', '?species=cat', '?species=dog', '?sex[0]=female', '?age[0]=2-5', '?sterilized=1', '?species=dog&page=2'];

export function setup() {
    // Слаги доступних тварин беремо з каталогу (3 сторінки по 12)
    const slugs = new Set();
    for (const page of [1, 2, 3]) {
        const res = http.get(`${BASE}/tvaryny?page=${page}`);
        for (const m of res.body.matchAll(/\/tvaryny\/([a-z0-9-]+-\d+)"/g)) slugs.add(m[1]);
    }
    return { slugs: [...slugs] };
}

export function browse(data) {
    const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];
    let res = http.get(`${BASE}/`, { tags: { name: 'home' } });
    check(res, { 'головна 200': (r) => r.status === 200 });
    res = http.get(`${BASE}/tvaryny${pick(FILTERS)}`, { tags: { name: 'catalog' } });
    check(res, { 'каталог 200': (r) => r.status === 200 && r.body.includes('Знайдено') });
    res = http.get(`${BASE}/tvaryny/${pick(data.slugs)}`, { tags: { name: 'animal' } });
    check(res, { 'тварина 200': (r) => r.status === 200 });
    sleep(THINK);
}

export function apply(data) {
    const slug = data.slugs[Math.floor(Math.random() * data.slugs.length)];
    const page = http.get(`${BASE}/tvaryny/${slug}/vzyaty-dodomu`, { tags: { name: 'form' } });
    if (!check(page, { 'форма 200': (r) => r.status === 200 })) return;

    const doc = parseHTML(page.body);
    const token = doc.find('meta[name=csrf-token]').attr('content');
    const snapshot = doc.find('[wire\\:snapshot]').first().attr('wire:snapshot');
    const updateUri = doc.find('[data-update-uri]').first().attr('data-update-uri');
    const regionId = doc.find('select option[value!=""]').first().attr('value');

    const payload = {
        _token: token,
        components: [{
            snapshot,
            updates: {
                name: 'Навантажувальний тест',
                email: `k6-${__VU}-${__ITER}-${Date.now()}@example.com`,
                phone: '+380501234567',
                region_id: regionId,
                consent: true,
            },
            calls: [{ method: 'submit', params: [], metadata: {} }],
        }],
    };
    const res = http.post(updateUri, JSON.stringify(payload), {
        headers: { 'Content-Type': 'application/json', 'X-Livewire': '1' },
        tags: { name: 'submit' },
    });
    check(res, { 'анкету прийнято': (r) => r.status === 200 && r.body.includes('\\"submitted\\":true') });
    sleep(THINK);
}
