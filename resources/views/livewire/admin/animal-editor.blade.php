<div>
    <x-admin-nav />
    <div class="mx-auto max-w-5xl px-6 py-8 flex flex-col gap-6">
        <a href="{{ route('admin.animals.index') }}" class="font-semibold text-primary hover:underline">← Усі тварини</a>
        <div class="flex flex-wrap items-center gap-4">
            <h1 class="text-4xl">{{ $animal->exists ? $animal->name : 'Нова тварина' }}</h1>
            @if ($animal->exists)
                <x-animal-status :status="$animal->status" />
                <a href="{{ route('animals.show', $animal) }}" target="_blank" class="font-semibold text-primary hover:underline">Переглянути на сайті ↗</a>
                <a href="{{ route('admin.applications.index', ['animal' => $animal->name]) }}" class="font-semibold text-primary hover:underline">Анкети на цю тварину ({{ $animal->applications()->count() }})</a>
            @endif
        </div>
        <form wire:submit="save" class="card p-6 md:p-8 flex flex-col gap-5" novalidate data-bi-scope>
            <div class="flex flex-wrap items-center justify-between gap-3 -mt-1 pb-4 border-b border-line">
                <p class="text-sm text-muted max-w-xl">Тексти картки заповнюються двома мовами. Якщо англійської немає, на англійській версії сайту показується український текст із позначкою.</p>
                <x-translation-meter />
            </div>
            @include('livewire.partials.animal-fields')
            @include('livewire.partials.photo-manager')
            @if (! $animal->exists || in_array($animal->status, $statuses, true))
                <label class="label max-w-xs">Показ у каталозі
                    <select wire:model="status" class="field font-normal">
                        @foreach ($statuses as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach
                    </select>
                </label>
            @elseif ($animal->status === \App\Enums\AnimalStatus::Adopted)
                <p class="rounded-2xl bg-success-soft text-success px-4 py-3">Тварину прилаштовано {{ $animal->adopted_at?->format('d.m.Y') }}. Статус змінюється лише через анкету.</p>
            @else
                <p class="rounded-2xl bg-sun-soft px-4 py-3">Картка волонтера – статус змінюється на сторінці <a href="{{ route('admin.moderation') }}" class="text-primary underline">модерації</a>.</p>
            @endif
            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" class="btn-primary min-h-12 px-8" wire:loading.attr="disabled">Зберегти</button>
                <x-translation-meter />
            </div>
        </form>
    </div>
</div>
