<div>
    <x-admin-nav />
    <div class="mx-auto max-w-4xl px-6 py-8 flex flex-col gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="text-4xl">SEO</h1>
            <p class="text-muted">Як сторінки виглядають у пошуку Google. Порожнє поле – сторінка показує свій звичайний заголовок, тож заповнювати можна поступово.</p>
        </div>
        <x-flash :message="$flash" class="!px-0 !pt-0" />

        <form wire:submit="save" class="card p-6 md:p-8 flex flex-col gap-5" novalidate data-bi-scope>
            <label class="label">Сторінка
                <select wire:model.live="page" class="field font-normal">
                    @foreach ($pages as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                </select>
            </label>

            {{-- Попередній перегляд: приблизно так сторінку покаже Google --}}
            @php($previewTitle = $form['title'] ?: '(типовий заголовок сторінки)')
            @php($previewDescription = $form['description'] ?: '(типовий опис сторінки)')
            <div class="rounded-2xl border border-line bg-bg p-4 flex flex-col gap-0.5" aria-label="Попередній перегляд у пошуку">
                <span class="text-sm text-muted truncate">{{ $this->pageUrl() }}</span>
                <span class="text-xl text-[#1a0dab] truncate">{{ \Illuminate\Support\Str::limit($previewTitle, 62) }}</span>
                <span class="text-sm text-muted line-clamp-2">{{ \Illuminate\Support\Str::limit($previewDescription, 160) }}</span>
            </div>

            @foreach (['title' => ['Title', 60, 'заголовок вкладки й посилання в пошуку; головне ключове слово – ближче до початку', 'text'],
                       'description' => ['Description', 160, 'текст під посиланням у пошуку; має заохочувати перейти на сайт', 'textarea']] as $field => [$label, $max, $hint, $type])
                <div class="flex flex-col gap-1">
                    <x-bilingual :label="$label" model="form.{{ $field }}" :type="$type" :rows="2" :hint="$hint" live />
                    {{-- Лічильник символів: довший текст Google обріже трикрапкою --}}
                    <p class="text-sm text-muted" x-data>
                        Рекомендовано до {{ $max }} символів:
                        UA <span x-text="($wire.form.{{ $field }} || '').length" x-bind:class="($wire.form.{{ $field }} || '').length > {{ $max }} && 'text-danger font-semibold'"></span>,
                        EN <span x-text="($wire.form.{{ $field }}_en || '').length" x-bind:class="($wire.form.{{ $field }}_en || '').length > {{ $max }} && 'text-danger font-semibold'"></span>
                    </p>
                </div>
            @endforeach
            <x-bilingual label="H1" model="form.h1" hint="головний заголовок на самій сторінці" />
            <x-bilingual label="SEO-текст" model="form.text" type="textarea" :rows="10"
                         hint="показується внизу сторінки. {{ \App\Support\SimpleMarkup::BLOCK_HINT }}" />
            <p class="text-sm text-muted -mt-2">Пишіть для людей: що це за сторінка, кого тут можна знайти, як узяти тварину. Посилання на інші сторінки сайту (розділи каталогу, статті) допомагають і відвідувачам, і пошуку.</p>

            <button type="submit" class="btn-primary self-start min-h-12 px-8">Зберегти</button>
        </form>

        <form wire:submit="saveGoogle" class="card p-6 md:p-8 flex flex-col gap-4" novalidate>
            <h2 class="text-2xl">Підключення Google</h2>
            <label class="label">Код підтвердження Google Search Console
                <input type="text" wire:model="google.google_site_verification" class="field font-normal" placeholder="наприклад, AbC123…">
                <span class="text-sm text-muted font-normal">Search Console → «Додати ресурс» → спосіб «Тег HTML»: скопіюйте лише значення content="…". Після збереження натисніть «Підтвердити». Карта сайту: <code>{{ route('sitemap') }}</code></span>
                @error('google.google_site_verification')<span class="error">{{ $message }}</span>@enderror
            </label>
            <label class="label">Ідентифікатор Google Analytics 4
                <input type="text" wire:model="google.google_analytics_id" class="field font-normal" placeholder="G-XXXXXXXXXX">
                <span class="text-sm text-muted font-normal">Аналітика вмикається лише для відвідувачів, які погодилися в банері внизу сторінки.</span>
                @error('google.google_analytics_id')<span class="error">{{ $message }}</span>@enderror
            </label>
            <button type="submit" class="btn-outline self-start min-h-11">Зберегти</button>
        </form>
    </div>
</div>
