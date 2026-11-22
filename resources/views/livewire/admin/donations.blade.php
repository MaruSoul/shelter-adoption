<div>
    <x-admin-nav />
    <div class="mx-auto max-w-4xl px-6 py-8 flex flex-col gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="text-4xl">Допомога</h1>
            <p class="text-muted">Як відвідувачі можуть підтримати притулок грошима. Кожен спосіб – окрема вкладка на сторінці
                <a href="{{ app('url')->routeIn('uk', 'help') }}" target="_blank" class="text-primary underline">«Як допомогти» ↗</a> і в блоці на головній.
                Показуються лише заповнені способи; якщо їх немає, на сайті лишається тільки вступний текст.</p>
        </div>
        <x-flash :message="$flash" class="!px-0 !pt-0" />

        {{-- Список способів у порядку вкладок на сайті --}}
        <div class="card divide-y divide-line">
            @forelse ($this->methods as $i => $method)
                <div class="p-4 flex flex-wrap items-center justify-between gap-3" wire:key="donation-{{ $method->id }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex flex-col">
                            <button type="button" wire:click="move({{ $method->id }}, -1)" @disabled($i === 0) class="btn-ghost px-2 min-h-7 disabled:opacity-30" aria-label="Перемістити вище">↑</button>
                            <button type="button" wire:click="move({{ $method->id }}, 1)" @disabled($loop->last) class="btn-ghost px-2 min-h-7 disabled:opacity-30" aria-label="Перемістити нижче">↓</button>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="font-semibold truncate">{{ $method->tr('title', 'uk') ?: $method->type->defaultTitle() }}
                                @unless ($method->is_visible)<span class="chip bg-tint-1 text-muted text-xs ml-1">сховано</span>@endunless
                            </span>
                            <span class="text-sm text-muted">{{ $method->type->label() }}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="edit({{ $method->id }})" class="btn-ghost min-h-10">Редагувати</button>
                        <button type="button" wire:click="toggle({{ $method->id }})" class="btn-ghost min-h-10">{{ $method->is_visible ? 'Сховати' : 'Показати' }}</button>
                        <button type="button" wire:click="delete({{ $method->id }})" wire:confirm="Видалити «{{ $method->tr('title', 'uk') ?: $method->type->defaultTitle() }}»?" class="btn-ghost min-h-10 text-danger">Видалити</button>
                    </div>
                </div>
            @empty
                <p class="p-6 text-muted">Способів допомоги ще немає. Додайте перший нижче.</p>
            @endforelse
        </div>

        @if ($editingId === null)
            <div class="card p-5 flex flex-col gap-3">
                <h2 class="text-2xl">Додати спосіб</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach ($types as $t)
                        <button type="button" wire:click="create('{{ $t->value }}')" class="text-left rounded-2xl border-[1.5px] border-line bg-card p-4 flex flex-col gap-1 hover:border-primary transition-colors">
                            <span class="font-semibold">+ {{ $t->label() }}</span>
                            <span class="text-sm text-muted">{{ $t->hint() }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <form wire:submit="save" class="card p-6 flex flex-col gap-5" novalidate data-bi-scope wire:key="donation-form-{{ $editingId }}-{{ $type }}">
                <div class="flex flex-col gap-1">
                    <h2 class="text-2xl">{{ $editingId ? 'Редагування' : 'Новий спосіб' }}: {{ $editingType->label() }}</h2>
                    <p class="text-sm text-muted">{{ $editingType->hint() }}</p>
                </div>

                @foreach ($editingType->fields() as $field)
                    <label class="label">{{ $field->label }}@unless ($field->required) <span class="font-normal text-sm text-muted">– необовʼязково</span>@endunless
                        <input type="{{ $field->inputType }}" wire:model="details.{{ $field->key }}" class="field font-normal" placeholder="{{ $field->placeholder }}"
                               @if ($field->inputType === 'url') inputmode="url" @endif>
                        @if ($field->hint)<span class="text-sm text-muted font-normal">{{ $field->hint }}</span>@endif
                        @error('details.'.$field->key)<span class="error">{{ $message }}</span>@enderror
                    </label>
                @endforeach

                <x-bilingual label="Назва вкладки" model="form.title" :required="$editingType->requiresTitle()" :optional="! $editingType->requiresTitle()"
                             hint="{{ $editingType->requiresTitle() ? 'наприклад, Portmone або Patreon' : 'якщо порожньо – «'.$editingType->defaultTitle().'»; можна, наприклад, «На корм на зиму»' }}" />
                <x-bilingual label="Короткий опис" model="form.note" type="textarea" :rows="2" optional
                             hint="на що збираєте або як переказати; показується над кнопкою чи реквізитами" />

                <div class="flex gap-2">
                    <button type="submit" class="btn-primary min-h-12 px-8">Зберегти</button>
                    <button type="button" wire:click="cancel" class="btn-ghost min-h-12">Скасувати</button>
                </div>
            </form>
        @endif

        <form wire:submit="savePage" class="card p-6 flex flex-col gap-5" novalidate data-bi-scope>
            <h2 class="text-2xl">Текст і звіти</h2>
            <x-bilingual label="Вступний текст" model="page.help_text" type="textarea" :rows="4"
                         hint="над вкладками: чим можна допомогти, що зараз потрібно притулку (корм, ліки, речі)" />
            <label class="label">Посилання на звіти <span class="font-normal text-sm text-muted">– необовʼязково</span>
                <input type="url" wire:model="page.reports_url" class="field font-normal" inputmode="url" placeholder="https://…">
                <span class="text-sm text-muted font-normal">Сторінка чи документ, де видно, на що витрачено кошти (Google Drive, Instagram-хайлайт, сайт). Зʼявиться в підвалі сайту й на сторінці «Як допомогти».</span>
                @error('page.reports_url')<span class="error">{{ $message }}</span>@enderror
            </label>
            <button type="submit" class="btn-outline self-start min-h-11">Зберегти</button>
        </form>
    </div>
</div>
