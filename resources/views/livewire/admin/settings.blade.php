<div>
    <x-admin-nav />
    <div class="mx-auto max-w-4xl px-6 py-8 flex flex-col gap-6">
        <h1 class="text-4xl">Налаштування притулку</h1>
        <x-flash :message="$flash" class="!px-0 !pt-0" />
        <form wire:submit="save" class="card p-6 md:p-8 flex flex-col gap-4" novalidate data-bi-scope>
            <fieldset class="flex flex-col gap-5">
                <legend class="font-display font-extrabold text-2xl pb-1">Як притулок підписаний на сайті</legend>
                <x-translation-meter class="-mt-2" />
                <x-bilingual label="Назва" model="form.name" required translit hint="у шапці, підвалі й заголовку вкладки" en-placeholder="Наприклад, Tails Club" />
                <x-bilingual label="Підзаголовок" model="form.tagline" hint="під назвою в шапці" />
                <x-bilingual label="Адреса" model="form.address" translit hint="у підвалі сайту" />
            </fieldset>

            <fieldset class="flex flex-col gap-5 pt-4 border-t border-line">
                <legend class="font-display font-extrabold text-2xl pb-1">Тексти на сайті</legend>
                <x-bilingual label="Типові умови прилаштування" model="form.default_adoption_terms" type="textarea" :rows="4"
                             hint="показуються на сторінці тварини, якщо в її картці власні умови не вказано" />
            </fieldset>

            <fieldset class="flex flex-col gap-4 pt-4 border-t border-line">
                <legend class="font-display font-extrabold text-2xl pb-1">Контакти</legend>
                <p class="text-sm text-muted -mt-2">Однакові для обох мов.</p>
            @foreach ([
                'phone' => ['Телефон', 'tel'], 'email' => ['Публічний e-mail', 'email'],
                'notification_email' => ['E-mail для сповіщень про анкети *', 'email'],
            ] as $field => [$label, $type])
                <label class="label">{{ $label }}
                    <input type="{{ $type }}" wire:model="form.{{ $field }}" class="field font-normal">
                    @error('form.'.$field)<span class="error">{{ $message }}</span>@enderror
                </label>
            @endforeach
            </fieldset>

            <fieldset class="flex flex-col gap-4 pt-4 border-t border-line">
                <legend class="font-display font-extrabold text-2xl pb-1">Слідкуй за нами</legend>
                <p class="text-sm text-muted -mt-2">Посилання з'являться внизу кожної сторінки. Порожні мережі не показуються.</p>
                @foreach (\App\Models\ShelterSetting::SOCIALS as $field => $label)
                    <label class="label">{{ $label }}
                        <input type="url" wire:model="form.{{ $field }}" placeholder="https://" class="field font-normal">
                        @error('form.'.$field)<span class="error">{{ $message }}</span>@enderror
                    </label>
                @endforeach
            </fieldset>
            <button type="submit" class="btn-primary self-start min-h-12 px-8">Зберегти</button>
        </form>
    </div>
</div>
