<div>
    <x-admin-nav />
    <div class="mx-auto max-w-6xl px-6 py-8 flex flex-col gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h1 class="text-4xl">Тварини</h1>
            <a href="{{ route('admin.animals.create') }}" class="btn-primary">Додати тварину</a>
        </div>
        <div class="card p-5 grid gap-4 grid-cols-[repeat(auto-fit,minmax(200px,1fr))]">
            <label class="label text-sm">Пошук <input type="search" wire:model.live.debounce.400ms="q" placeholder="Кличка" class="field font-normal"></label>
            <label class="label text-sm">Статус
                <select wire:model.live="status" class="field font-normal"><option value="">Усі</option>@foreach ($statuses as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach</select>
            </label>
            <label class="label text-sm">Джерело
                <select wire:model.live="source" class="field font-normal"><option value="">Усі</option>@foreach ($sources as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach</select>
            </label>
            <label class="label text-sm">Англійська версія
                <select wire:model.live="translation" class="field font-normal"><option value="">Усі</option><option value="missing">Без перекладу</option></select>
            </label>
        </div>
        <div class="card overflow-x-auto">
            <table class="w-full text-left">
                <thead class="text-sm text-muted border-b border-line">
                    <tr><th class="p-4 font-semibold">Тварина</th><th class="p-4 font-semibold">Вид</th><th class="p-4 font-semibold">Джерело</th><th class="p-4 font-semibold">Активні анкети</th><th class="p-4 font-semibold">Статус</th><th class="p-4 font-semibold">EN</th></tr>
                </thead>
                <tbody>
                    @forelse ($animals as $animal)
                        <tr class="border-b border-line last:border-0 hover:bg-bg" wire:key="an-{{ $animal->id }}">
                            <td class="p-3">
                                <a href="{{ route('admin.animals.edit', $animal) }}" class="flex items-center gap-3 font-semibold text-primary hover:underline">
                                    <span class="size-12 rounded-xl overflow-hidden bg-tint-1 shrink-0">@if ($animal->coverPhoto)<img src="{{ $animal->coverPhoto->thumbUrl() }}" alt="" class="size-full object-cover">@endif</span>
                                    {{ $animal->name }}
                                </a>
                            </td>
                            <td class="p-3">{{ $animal->species->name }}</td>
                            <td class="p-3 text-sm">{{ $animal->source->label() }}</td>
                            <td class="p-3">{{ $animal->active_applications_count }}</td>
                            <td class="p-3"><x-animal-status :status="$animal->status" /></td>
                            <td class="p-3"><x-translation-chip :state="$animal->needsTranslation() ? 'none' : 'full'" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-muted">Тварин за цими умовами немає.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $animals->links('components.pagination') }}
    </div>
</div>
