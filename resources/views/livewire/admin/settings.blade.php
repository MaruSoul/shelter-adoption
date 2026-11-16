<div>
    <x-admin-nav />
    <div class="mx-auto max-w-3xl px-6 py-8 flex flex-col gap-6">
        <h1 class="text-4xl">Налаштування притулку</h1>
        <x-flash :message="$flash" class="!px-0 !pt-0" />
        <form wire:submit="save" class="card p-6 md:p-8 flex flex-col gap-4" novalidate>
            @foreach ([
                'name' => ['Назва *', 'text'], 'tagline' => ['Підзаголовок', 'text'], 'address' => ['Адреса', 'text'],
                'phone' => ['Телефон', 'tel'], 'email' => ['Публічний e-mail', 'email'],
                'notification_email' => ['E-mail для сповіщень про анкети *', 'email'],
                'instagram_url' => ['Instagram', 'url'], 'telegram_url' => ['Telegram', 'url'],
            ] as $field => [$label, $type])
                <label class="label">{{ $label }}
                    <input type="{{ $type }}" wire:model="form.{{ $field }}" class="field font-normal">
                    @error('form.'.$field)<span class="error">{{ $message }}</span>@enderror
                </label>
            @endforeach
            <button type="submit" class="btn-primary self-start min-h-12 px-8">Зберегти</button>
        </form>
    </div>
</div>
