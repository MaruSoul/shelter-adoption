<div>
    <x-admin-nav />
    <x-flash :message="$flash" />
    <div class="mx-auto max-w-6xl px-6 py-8 flex flex-col gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="text-4xl">Модерація</h1>
            <p class="text-muted">Картки, які розмістили волонтери. Після схвалення тварина зʼявиться в каталозі з позначкою «Розміщено волонтером».</p>
        </div>
        @if ($animals->isNotEmpty())
            <p class="text-sm font-semibold text-muted">У черзі: {{ $animals->count() }}</p>
        @endif
        @forelse ($animals as $animal)
            <article class="card overflow-hidden flex flex-wrap" wire:key="m-{{ $animal->id }}" x-data="{ active: 0 }">
                {{-- Фото: велике + мініатюри, щоб модератор побачив усі світлини --}}
                <div class="grow basis-80 max-w-md bg-tint-1 flex flex-col gap-2 p-3">
                    @php($photos = $animal->photos)
                    @if ($photos->isNotEmpty())
                        <div class="aspect-[4/3] rounded-2xl overflow-hidden">
                            @foreach ($photos as $i => $photo)
                                <img src="{{ $photo->url() }}" alt="{{ $photo->alt ?? $animal->name }}" x-show="active === {{ $i }}" @if ($i) x-cloak @endif class="size-full object-cover">
                            @endforeach
                        </div>
                        @if ($photos->count() > 1)
                            <div class="grid grid-cols-5 gap-2">
                                @foreach ($photos as $i => $photo)
                                    <button type="button" x-on:click="active = {{ $i }}" aria-label="Фото {{ $i + 1 }}"
                                            class="aspect-square rounded-lg overflow-hidden border-2"
                                            x-bind:class="active === {{ $i }} ? 'border-primary' : 'border-transparent opacity-75'">
                                        <img src="{{ $photo->thumbUrl() }}" alt="" class="size-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="aspect-[4/3] rounded-2xl flex items-center justify-center text-muted">Без фото</p>
                    @endif
                </div>

                <div class="grow-[999] basis-96 min-w-0 p-6 flex flex-col gap-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex flex-col gap-1">
                            <h2 class="text-3xl">{{ $animal->name }}</h2>
                            <p class="text-sm text-muted">
                                Розмістив(-ла) {{ $animal->author?->name }}
                                @if ($animal->author)
                                    · <a href="mailto:{{ $animal->author->email }}" class="text-primary hover:underline">{{ $animal->author->email }}</a>
                                @endif
                                · {{ $animal->updated_at->format('d.m.Y H:i') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            {{-- Дрібну помилку (кличка, порода, опис, фото) адміністратор виправляє сам, а не відхиляє картку --}}
                            <a href="{{ route('admin.animals.edit', $animal) }}" class="btn-outline min-h-11 text-sm">Редагувати</a>
                            <a href="{{ route('animals.show', $animal) }}" target="_blank" class="btn-outline min-h-11 text-sm">Переглянути сторінку ↗</a>
                        </div>
                    </div>

                    <dl class="grid grid-cols-[repeat(auto-fill,minmax(150px,1fr))] gap-x-5 gap-y-2.5 text-sm">
                        @foreach ([
                            'Вид' => $animal->species->name,
                            'Стать' => $animal->sex->label(),
                            'Вік' => $animal->ageLabel(),
                            'Порода' => $animal->breed?->name ?? 'Без породи',
                            'Забарвлення' => $animal->color?->name ?? '–',
                            'Населений пункт' => $animal->locality->name.', '.$animal->locality->region->name,
                            'Стерилізація' => $animal->is_sterilized ? 'Так' : 'Ні',
                            'Вакцинація' => $animal->is_vaccinated ? 'Так' : 'Ні',
                            'Де зараз' => $animal->current_location ?: '–',
                        ] as $label => $value)
                            <div><dt class="text-muted">{{ $label }}</dt><dd class="font-semibold">{{ $value }}</dd></div>
                        @endforeach
                    </dl>

                    @foreach (['Історія' => $animal->story, 'Поведінка' => $animal->behavior, 'Умови прилаштування' => $animal->adoption_terms] as $heading => $text)
                        @if ($text)
                            <details class="group rounded-2xl bg-surface px-4 py-3" @if ($loop->first) open @endif>
                                <summary class="cursor-pointer font-semibold">{{ $heading }}</summary>
                                <p class="pt-2 text-muted whitespace-pre-line">{{ $text }}</p>
                            </details>
                        @endif
                    @endforeach

                    <div class="flex flex-wrap items-end gap-3 pt-2 mt-auto border-t border-line">
                        <button type="button" wire:click="approve({{ $animal->id }})" class="btn-primary mt-3">Схвалити</button>
                        <label class="label grow basis-64 text-sm">Причина відхилення
                            <input type="text" wire:model="reasons.{{ $animal->id }}" class="field font-normal" placeholder="Що потрібно виправити">
                            @error('reasons.'.$animal->id)<span class="error">{{ $message }}</span>@enderror
                        </label>
                        <button type="button" wire:click="reject({{ $animal->id }})" class="btn border-[1.5px] border-danger text-danger">Відхилити</button>
                    </div>
                </div>
            </article>
        @empty
            <p class="card p-8 text-muted">Черга порожня – усі картки перевірено.</p>
        @endforelse
    </div>
</div>
