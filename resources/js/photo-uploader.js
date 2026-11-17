/**
 * Завантаження фото тварини в адмінці й кабінеті волонтера.
 *
 * Livewire з `wire:model` + `multiple` надсилає всі обрані файли одним запитом. Десять фото з телефона
 * по 5–8 МБ легко перевищують ліміт запиту на сервері, і тоді не завантажується жодне. Тому файли
 * надсилаються по одному, з прогресом, а перед відправкою великі знімки зменшуються в браузері:
 * сервер однаково зберігає фото не більше 1600 px (PhotoStorage::MAX_SIDE).
 */
const MAX_SIDE = 2560;
const REENCODE_FROM = 3 * 1024 * 1024;

async function shrink(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return file;

    let bitmap;
    try {
        bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
    } catch {
        return file; // браузер не зміг прочитати – хай перевірить сервер
    }

    const scale = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));
    if (scale === 1 && file.size <= REENCODE_FROM) {
        bitmap.close();
        return file;
    }

    const canvas = document.createElement('canvas');
    canvas.width = Math.round(bitmap.width * scale);
    canvas.height = Math.round(bitmap.height * scale);
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff'; // прозорі ділянки PNG у JPEG стають білими, а не чорними
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
    bitmap.close();

    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.9));
    if (!blob || blob.size >= file.size) return file;

    return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg', lastModified: file.lastModified });
}

window.photoUploader = (wire) => ({
    busy: false,
    done: 0,
    total: 0,
    progress: 0,
    skipped: 0,
    failed: [],

    async pick(event) {
        let files = [...event.target.files];
        event.target.value = '';
        if (!files.length || this.busy) return;

        // Скільки ще можна додати – сервер оновлює data-left після кожного рендеру
        const left = Number(this.$el.dataset.left ?? files.length);
        this.skipped = Math.max(0, files.length - left);
        files = files.slice(0, left);

        this.failed = [];
        this.total = files.length;
        this.done = 0;
        this.busy = true;

        for (const original of files) {
            this.progress = 0;
            try {
                const file = await shrink(original);
                await new Promise((resolve, reject) => wire.$uploadMultiple(
                    'newUploads', [file], resolve, reject, (e) => { this.progress = e.detail.progress; },
                ));
            } catch {
                this.failed.push(original.name);
            }
            this.done++;
        }

        this.busy = false;
    },
});
