<div class="mx-auto max-w-5xl px-6 py-10 flex flex-col gap-7">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-4xl">Кабінет</h1>
            <p class="text-muted">{{ auth()->user()->name }} · {{ auth()->user()->email }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('cabinet.animals.create') }}" class="btn-sun">Розмістити тварину</a>
            <a href="{{ route('cabinet.profile') }}" class="btn-outline">Мій профіль</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn-ghost">Вийти</button></form>
        </div>
    </div>

    @if ($flash)
        <div class="rounded-2xl bg-success-soft text-success px-5 py-3 font-medium" role="status" wire:key="flash-{{ md5($flash) }}">{{ $flash }}</div>
    @endif

    <div role="tablist" class="flex gap-2 border-b border-line">
        <button type="button" role="tab" aria-selected="{{ $tab === 'applications' ? 'true' : 'false' }}" wire:click="$set('tab', 'applications')"
                @class(['px-4 py-3 -mb-px border-b-2 font-semibold', 'border-primary text-primary' => $tab === 'applications', 'border-transparent text-muted' => $tab !== 'applications'])>
            Мої анкети ({{ $applications->count() }})
        </button>
        <button type="button" role="tab" aria-selected="{{ $tab === 'animals' ? 'true' : 'false' }}" wire:click="$set('tab', 'animals')"
                @class(['px-4 py-3 -mb-px border-b-2 font-semibold', 'border-primary text-primary' => $tab === 'animals', 'border-transparent text-muted' => $tab !== 'animals'])>
            Розміщені тварини ({{ $animals->count() }})
        </button>
    </div>

    @if ($tab === 'applications')
        @forelse ($applications as $application)
            <article class="card p-5 flex flex-wrap gap-5 items-start" wire:key="app-{{ $application->id }}">
                <div class="size-24 rounded-2xl overflow-hidden bg-tint-1 shrink-0">
                    @if ($application->animal->coverPhoto)<img src="{{ $application->animal->coverPhoto->thumbUrl() }}" alt="" class="size-full object-cover">@endif
                </div>
                <div class="grow basis-64 flex flex-col gap-2">
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($application->animal->trashed())
                            <span class="font-display font-extrabold text-xl">{{ $application->animal->name }}</span>
                        @else
                            <a href="{{ route('animals.show', $application->animal) }}" class="font-display font-extrabold text-xl hover:text-primary">{{ $application->animal->name }}</a>
                        @endif
                        <span class="chip {{ $application->status->badgeClasses() }}">{{ $application->status->label() }}</span>
                    </div>
                    <span class="text-sm text-muted">Подано {{ $application->created_at->translatedFormat('j F Y, H:i') }}</span>
                    <ol class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted" aria-label="Історія статусів">
                        @foreach ($application->statusChanges as $change)
                            <li>{{ $change->created_at->format('d.m') }} – {{ $change->to_status->label() }}@if ($change->note && $change->to_status === \App\Enums\ApplicationStatus::Closed) ({{ $change->note }})@endif</li>
                        @endforeach
                    </ol>
                </div>
            </article>
        @empty
            <div class="card p-8 flex flex-wrap items-center gap-6">
                <x-mascot.sleep class="w-36 shrink-0" label="Лапа дрімає" />
                <div class="flex flex-col gap-3 items-start grow basis-64">
                <p class="text-muted">Анкет поки немає. Анкети, подані раніше з адресою {{ auth()->user()->email }}, зʼявляться тут автоматично.</p>
                <a href="{{ route('animals.index') }}" class="btn-primary">Знайти друга</a>
                </div>
            </div>
        @endforelse
    @else
        @forelse ($animals as $animal)
            <article class="card p-5 flex flex-wrap gap-5 items-start" wire:key="animal-{{ $animal->id }}">
                <div class="size-24 rounded-2xl overflow-hidden bg-tint-1 shrink-0">
                    @if ($animal->coverPhoto)<img src="{{ $animal->coverPhoto->thumbUrl() }}" alt="" class="size-full object-cover">@endif
                </div>
                <div class="grow basis-64 flex flex-col gap-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('animals.show', $animal) }}" class="font-display font-extrabold text-xl hover:text-primary">{{ $animal->name }}</a>
                        <x-animal-status :status="$animal->status" />
                    </div>
                    @if ($animal->status === \App\Enums\AnimalStatus::Rejected && $animal->moderation_note)
                        <p class="rounded-xl bg-danger-soft text-danger px-3 py-2 text-sm"><strong>Причина:</strong> {{ $animal->moderation_note }}</p>
                    @endif
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        @can('update', $animal)
                            <a href="{{ route('cabinet.animals.edit', $animal) }}" class="font-semibold text-primary hover:underline">Редагувати</a>
                        @endcan
                        @can('delete', $animal)
                            <button type="button" class="font-semibold text-danger hover:underline"
                                    wire:click="deleteAnimal({{ $animal->id }})"
                                    wire:confirm="Видалити картку «{{ $animal->name }}»? Її буде прибрано з сайту назавжди. Якщо на тварину вже подали анкети, їх буде закрито з поясненням.">
                                Видалити
                            </button>
                        @endcan
                    </div>
                </div>
            </article>
        @empty
            <div class="card p-8 flex flex-wrap items-center gap-6">
                <x-mascot.sleep class="w-36 shrink-0" label="Лапа дрімає" />
                <div class="flex flex-col gap-3 items-start grow basis-64">
                <p class="text-muted">Ви ще не розміщували тварин. Знайшли тварину на вулиці? Розмістіть її картку – після перевірки вона зʼявиться в каталозі.</p>
                <a href="{{ route('cabinet.animals.create') }}" class="btn-sun">Розмістити тварину</a>
                </div>
            </div>
        @endforelse
    @endif
</div>
