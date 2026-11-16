<div>
    <x-admin-nav />
    <div class="mx-auto max-w-6xl px-6 py-8 flex flex-col gap-6">
        <h1 class="text-4xl">Анкети</h1>
        <div class="card p-5 grid gap-4 grid-cols-[repeat(auto-fit,minmax(180px,1fr))]">
            <label class="label text-sm">Пошук
                <input type="search" wire:model.live.debounce.400ms="q" placeholder="Імʼя, телефон або e-mail" class="field font-normal">
            </label>
            <label class="label text-sm">Статус
                <select wire:model.live="status" class="field font-normal">
                    <option value="">Усі</option>
                    <option value="working">У роботі (усі етапи)</option>
                    @foreach ($statuses as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach
                </select>
            </label>
            <label class="label text-sm">Тварина
                <input type="search" wire:model.live.debounce.400ms="animal" placeholder="Кличка" class="field font-normal">
            </label>
            <label class="label text-sm">Від
                <input type="date" wire:model.live="from" class="field font-normal">
            </label>
            <label class="label text-sm">До
                <input type="date" wire:model.live="to" class="field font-normal">
            </label>
        </div>

        @if ($email)
            <div class="flex flex-wrap items-center gap-3 rounded-2xl bg-tint-1 px-5 py-3">
                <span>Усі анкети людини з поштою <strong>{{ $email }}</strong></span>
                <button type="button" wire:click="$set('email', '')" class="font-semibold text-primary hover:underline">× Показати всі анкети</button>
            </div>
        @endif

        <div class="card overflow-x-auto">
            <table class="w-full text-left">
                <thead class="text-sm text-muted border-b border-line">
                    <tr><th class="p-4 font-semibold">Дата</th><th class="p-4 font-semibold">Людина</th><th class="p-4 font-semibold">Тварина</th><th class="p-4 font-semibold">Звʼязок</th><th class="p-4 font-semibold">Статус</th><th class="p-4"><span class="sr-only">Дія</span></th></tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr class="border-b border-line last:border-0 hover:bg-bg" wire:key="a-{{ $application->id }}">
                            <td class="p-4 whitespace-nowrap text-sm">{{ $application->created_at->format('d.m.Y H:i') }}</td>
                            <td class="p-4">
                                <button type="button" wire:click="$set('email', '{{ $application->email }}')" class="font-semibold text-primary hover:underline text-left" title="Показати всі анкети цієї людини">{{ $application->name }}</button>
                                <span class="block text-sm text-muted">{{ $application->phone }} · {{ $application->region->name }}</span>
                                @if (! $email && ($perPerson[$application->email] ?? 1) > 1)
                                    <span class="block text-xs text-muted">усього анкет: {{ $perPerson[$application->email] }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if ($application->animal->trashed())
                                    <span class="font-semibold">{{ $application->animal->name }}</span>
                                    <span class="block text-xs text-muted">картку видалено автором</span>
                                @else
                                    <a href="{{ route('admin.animals.edit', $application->animal) }}" class="font-semibold text-primary hover:underline">{{ $application->animal->name }}</a>
                                @endif
                            </td>
                            <td class="p-4 text-sm">{{ $application->contact_channels->map->label()->join(', ') }}</td>
                            <td class="p-4"><span class="chip whitespace-nowrap {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span></td>
                            <td class="p-4 text-right"><a href="{{ route('admin.applications.show', $application) }}" class="btn-outline min-h-10 px-4 whitespace-nowrap">Відкрити →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-muted">Анкет за цими умовами немає.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $applications->links('components.pagination') }}
    </div>
</div>
