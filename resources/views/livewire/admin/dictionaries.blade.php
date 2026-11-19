<div>
    <x-admin-nav />
    <div class="mx-auto max-w-5xl px-6 py-8 flex flex-col gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="text-4xl">Довідники</h1>
            <p class="text-muted">Значення, з яких обирають у картках тварин і статтях. Запис, що вже використовується, можна перейменувати, але не видалити.</p>
        </div>

        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Довідники">
            @foreach (\App\Enums\DictionaryType::cases() as $case)
                <button type="button" role="tab" wire:click="$set('tab', '{{ $case->value }}')" aria-selected="{{ $type === $case ? 'true' : 'false' }}"
                        @class(['btn border-[1.5px]', 'bg-primary text-primary-ink border-primary' => $type === $case, 'bg-card border-line' => $type !== $case])>{{ $case->label() }}</button>
            @endforeach
        </div>

        <x-flash :message="$flash" class="!px-0 !pt-0" />
        @error('delete')<p role="alert" class="rounded-2xl bg-danger-soft text-danger px-5 py-3 font-medium">{{ $message }}</p>@enderror

        @if ($type->isScopedBySpecies())
            <label class="label self-start">Вид
                <select wire:model.live="speciesFilter" class="field font-normal w-auto">
                    @foreach ($speciesList as $s)<option value="{{ $s->id }}">{{ $s->name_plural }}</option>@endforeach
                </select>
            </label>
        @endif

        <div class="card divide-y divide-line">
            @forelse ($records as $record)
                <div class="p-4 flex flex-col gap-3" wire:key="{{ $type->value }}-{{ $record->id }}">
                    @if (($edit['id'] ?? null) === $record->id)
                        <form wire:submit="save" class="flex flex-col gap-3" data-bi-scope>
                            <x-bilingual model="edit.name" placeholder="{{ $type->hasBreeds() ? 'Назва (Котик)' : 'Назва' }}" compact />
                            @if ($type->hasPluralName())
                                <x-bilingual model="edit.name_plural" placeholder="У множині (Котики)" compact />
                            @endif
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary min-h-10">Зберегти</button>
                                <button type="button" wire:click="cancelEdit" class="btn-ghost min-h-10">Скасувати</button>
                            </div>
                        </form>
                    @else
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-col">
                                <span class="font-semibold">{{ $record->name }}@if ($type->hasPluralName()) <span class="text-muted font-normal">/ {{ $record->name_plural }}</span>@endif</span>
                                <span class="text-sm text-muted" lang="en">
                                    @if (filled($record->name_en))
                                        EN: {{ $record->name_en }}@if ($type->hasPluralName() && filled($record->name_plural_en)) / {{ $record->name_plural_en }}@endif
                                    @else
                                        <span class="text-danger">немає перекладу</span>
                                    @endif
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm text-muted">
                                    {{ $record->{$usage} }} {{ \App\Support\Ukrainian::plural($record->{$usage}, $type->usageForms()) }}
                                    @if ($type->hasBreeds()) · {{ $record->breeds_count }} {{ \App\Support\Ukrainian::plural($record->breeds_count, ['порода', 'породи', 'порід']) }}@endif
                                </span>
                                <button type="button" wire:click="startEdit({{ $record->id }})" class="btn-ghost min-h-10">Редагувати</button>
                                @if ($record->{$usage} === 0)
                                    <button type="button" wire:click="delete({{ $record->id }})"
                                            wire:confirm="Видалити «{{ $record->name }}»?{{ $type->hasBreeds() && $record->breeds_count ? ' Разом із ним видаляться й породи цього виду.' : '' }}"
                                            class="btn-ghost min-h-10 text-danger">Видалити</button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="p-6 text-muted">Записів ще немає.</p>
            @endforelse
        </div>

        <form wire:submit="add" class="card p-5 flex flex-col gap-3" data-bi-scope>
            <h2 class="text-2xl">Додати {{ $type->accusative() }}</h2>
            <x-bilingual model="new.name" placeholder="{{ $type->hasBreeds() ? 'Назва (наприклад, Кролик)' : 'Назва' }}" compact />
            @if ($type->hasPluralName())
                <x-bilingual model="new.name_plural" placeholder="У множині (Кролики)" compact />
            @endif
            @if ($type->hasSlug())
                <p class="text-sm text-muted">Адреса {{ $type->hasBreeds() ? 'розділу в каталозі' : 'рубрики' }} створюється з англійської назви (або з транслітерації) й далі не змінюється.</p>
            @endif
            @error('speciesFilter')<span class="error">{{ $message }}</span>@enderror
            <button type="submit" class="btn-primary self-start min-h-11">Додати</button>
        </form>
    </div>
</div>
