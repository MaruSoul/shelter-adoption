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

        <div class="card overflow-x-auto">
            <table class="w-full text-left">
                <thead class="text-sm text-muted border-b border-line">
                    <tr><th class="p-4 font-semibold">Дата</th><th class="p-4 font-semibold">Людина</th><th class="p-4 font-semibold">Тварина</th><th class="p-4 font-semibold">Звʼязок</th><th class="p-4 font-semibold">Статус</th></tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr class="border-b border-line last:border-0 hover:bg-bg" wire:key="a-{{ $application->id }}">
                            <td class="p-4 whitespace-nowrap text-sm">{{ $application->created_at->format('d.m.Y H:i') }}</td>
                            <td class="p-4"><a href="{{ route('admin.applications.show', $application) }}" class="font-semibold text-primary hover:underline">{{ $application->name }}</a><br><span class="text-sm text-muted">{{ $application->phone }} · {{ $application->region->name }}</span></td>
                            <td class="p-4"><a href="{{ route('admin.animals.edit', $application->animal) }}" class="font-semibold text-primary hover:underline">{{ $application->animal->name }}</a></td>
                            <td class="p-4 text-sm">{{ $application->contact_channels->map->label()->join(', ') }}</td>
                            <td class="p-4"><span class="chip {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-muted">Анкет за цими умовами немає.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $applications->links('components.pagination') }}
    </div>
</div>
