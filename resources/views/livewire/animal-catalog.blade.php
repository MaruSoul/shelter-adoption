<div class="mx-auto max-w-6xl px-6 py-10 flex flex-col gap-7">
    <div class="flex flex-col gap-2">
        <h1 class="text-4xl md:text-5xl">{{ $species ? $speciesList->firstWhere('slug', $species)?->name_plural.' шукають дім' : 'Знайти друга' }}</h1>
        <p class="text-muted">Усі тварини оглянуті ветеринаром. Фільтри зберігаються в адресі сторінки – нею можна поділитися.</p>
    </div>

    <div class="flex flex-wrap gap-8 items-start" x-data="{ filtersOpen: false }">
        {{-- На телефоні фільтри сховані за кнопкою, щоб тварин було видно одразу --}}
        <button type="button" class="lg:hidden btn-outline w-full" x-on:click="filtersOpen = !filtersOpen" x-bind:aria-expanded="filtersOpen" aria-controls="catalog-filters">
            <span x-text="filtersOpen ? 'Сховати фільтри' : 'Фільтри'">Фільтри</span>@if ($activeCount > 0) <span class="chip bg-sun text-sun-ink text-xs px-2 py-0.5">{{ $activeCount }}</span>@endif
        </button>

        {{-- Фільтри --}}
        <aside id="catalog-filters" aria-label="Фільтри" class="card p-6 flex-col gap-5 grow basis-64 lg:max-w-72 hidden lg:flex" x-bind:class="filtersOpen && 'max-lg:flex'">
            <fieldset class="flex flex-col gap-2">
                <legend class="font-semibold mb-2">Кого шукаєте</legend>
                <div class="flex flex-wrap gap-2">
                    <button type="button" wire:click="setSpecies('')" aria-pressed="{{ $species === '' ? 'true' : 'false' }}"
                            @class(['btn border-[1.5px]', 'bg-primary text-primary-ink border-primary' => $species === '', 'bg-card border-line' => $species !== ''])>Усі</button>
                    @foreach ($speciesList as $s)
                        <button type="button" wire:click="setSpecies('{{ $s->slug }}')" aria-pressed="{{ $species === $s->slug ? 'true' : 'false' }}"
                                @class(['btn border-[1.5px]', 'bg-primary text-primary-ink border-primary' => $species === $s->slug, 'bg-card border-line' => $species !== $s->slug])>{{ $s->name_plural }}</button>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="flex flex-col gap-1">
                <legend class="font-semibold mb-2">Стать</legend>
                @foreach ($sexes as $s)
                    <label class="flex items-center gap-2.5 min-h-8"><input type="checkbox" wire:model.live="sex" value="{{ $s->value }}" class="size-4 accent-primary"> {{ $s->pluralLabel() }}</label>
                @endforeach
            </fieldset>

            <fieldset class="flex flex-col gap-1">
                <legend class="font-semibold mb-2">Вік</legend>
                @foreach ($ageGroups as $g)
                    <label class="flex items-center gap-2.5 min-h-8"><input type="checkbox" wire:model.live="age" value="{{ $g->value }}" class="size-4 accent-primary"> {{ $g->label() }}</label>
                @endforeach
            </fieldset>

            <fieldset class="flex flex-col gap-1">
                <legend class="font-semibold mb-2">Здоровʼя</legend>
                <label class="flex items-center gap-2.5 min-h-8"><input type="checkbox" wire:model.live="sterilized" class="size-4 accent-primary"> Стерилізовані</label>
                <label class="flex items-center gap-2.5 min-h-8"><input type="checkbox" wire:model.live="vaccinated" class="size-4 accent-primary"> Вакциновані</label>
            </fieldset>

            <label class="label">Область
                <select wire:model.live="region" class="field font-normal">
                    <option value="">Усі</option>
                    @foreach ($regions as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                </select>
            </label>

            @if ($localities->isNotEmpty())
                <label class="label">Населений пункт
                    <select wire:model.live="locality" class="field font-normal">
                        <option value="">Усі</option>
                        @foreach ($localities as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach
                    </select>
                </label>
            @endif

            @if ($breeds->isNotEmpty())
                <label class="label">Порода
                    <select wire:model.live="breed" class="field font-normal">
                        <option value="">Будь-яка</option>
                        @foreach ($breeds as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach
                    </select>
                </label>
            @endif

            <label class="label">Забарвлення
                <select wire:model.live="color" class="field font-normal">
                    <option value="">Будь-яке</option>
                    @foreach ($colors as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
            </label>

            <label class="flex items-center gap-2.5 min-h-8"><input type="checkbox" wire:model.live="adopted" class="size-4 accent-primary"> Показати тих, хто знайшов дім</label>

            @if ($activeCount > 0)
                <button type="button" wire:click="resetFilters" class="btn-ghost">Скинути фільтри ({{ $activeCount }})</button>
            @endif
        </aside>

        {{-- Результати --}}
        <section class="grow-[999] basis-[560px] min-w-0 flex flex-col gap-5" aria-live="polite">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-muted">Знайдено: <strong class="text-ink">{{ $this->animals->total() }}</strong></span>
                <label class="flex items-center gap-2 text-muted">Сортувати
                    <select wire:model.live="sort" class="field min-h-10 w-auto bg-card">
                        <option value="new">Спочатку нові</option>
                        <option value="young">Спочатку молодші</option>
                        <option value="old">Спочатку старші</option>
                    </select>
                </label>
            </div>

            <div wire:loading.class="opacity-60" class="transition-opacity">
                @if ($this->animals->isEmpty())
                    <div class="card p-10 flex flex-col items-center gap-3 text-center">
                        <x-mascot.sleep class="w-48" label="Лапа дрімає" />
                        <h2 class="text-2xl">Нікого не знайшли</h2>
                        <p class="text-muted max-w-md">За цими фільтрами тварин зараз немає. Спробуйте прибрати частину фільтрів – можливо, ваш друг уже чекає.</p>
                        <button type="button" wire:click="resetFilters" class="btn-primary">Скинути фільтри</button>
                    </div>
                @else
                    <div class="animal-grid">
                        @foreach ($this->animals as $animal)
                            <x-animal-card :animal="$animal" wire:key="animal-{{ $animal->id }}" />
                        @endforeach
                    </div>
                @endif
            </div>

            {{ $this->animals->links('components.pagination') }}
        </section>
    </div>
</div>
