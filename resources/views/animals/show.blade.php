@php($cover = $animal->photos->first())
<x-layout :title="$animal->name.' – '.$animal->species->name.' шукає дім'"
          :description="\Illuminate\Support\Str::limit($animal->story ?? $animal->behavior ?? '', 155)"
          :image="$cover?->url()">
    <div class="mx-auto max-w-6xl px-6 py-7 flex flex-col gap-10">
        <nav aria-label="Навігаційний ланцюжок" class="text-sm text-muted flex flex-wrap gap-2">
            <a href="{{ route('home') }}" class="hover:underline">Головна</a><span aria-hidden="true">/</span>
            <a href="{{ route('animals.index', ['species' => $animal->species->slug]) }}" class="hover:underline">{{ $animal->species->name_plural }}</a><span aria-hidden="true">/</span>
            <span class="text-ink">{{ $animal->name }}</span>
        </nav>

        @unless ($animal->isPublic())
            <div role="status" class="rounded-2xl bg-sun-soft px-5 py-3 font-medium">
                Ця картка зараз не видна в каталозі: {{ mb_strtolower($animal->status->label()) }}.
                @if ($animal->moderation_note) Коментар модератора: {{ $animal->moderation_note }} @endif
            </div>
        @endunless

        <div class="flex flex-wrap gap-10 items-start">
            {{-- Галерея: стрілки видно завжди (на телефоні немає наведення), гортання клавішами ← → і свайпом --}}
            <div class="grow-[999] basis-[520px] min-w-0 flex flex-col gap-3"
                 x-data="{
                     active: 0,
                     startX: null,
                     photos: @js($animal->photos->map(fn ($p) => ['url' => $p->url(), 'alt' => $p->alt ?? $animal->name])->values()),
                     go(step) { this.active = (this.active + step + this.photos.length) % this.photos.length },
                     swipe(endX) { if (this.startX !== null && Math.abs(endX - this.startX) > 40) this.go(endX < this.startX ? 1 : -1); this.startX = null },
                 }">
                <div class="group relative aspect-[4/3] rounded-[1.75rem] bg-tint-1 overflow-hidden"
                     @if ($animal->photos->count() > 1)
                         role="region" aria-roledescription="галерея" aria-label="Фото {{ $animal->name }}" tabindex="0"
                         x-on:keydown.left.prevent="go(-1)" x-on:keydown.right.prevent="go(1)"
                         x-on:touchstart.passive="startX = $event.touches[0].clientX"
                         x-on:touchend="swipe($event.changedTouches[0].clientX)"
                     @endif>
                    <template x-if="photos.length">
                        <img x-bind:src="photos[active].url" x-bind:alt="photos[active].alt" class="size-full object-cover">
                    </template>
                    @if ($animal->photos->count() > 1)
                        <button type="button" x-on:click="go(-1)" aria-label="Попереднє фото"
                                class="absolute left-3 top-1/2 -translate-y-1/2 size-11 rounded-full bg-card/70 backdrop-blur text-ink shadow flex items-center justify-center opacity-80 transition hover:bg-card hover:opacity-100 group-hover:opacity-100 focus-visible:opacity-100">
                            <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                        </button>
                        <button type="button" x-on:click="go(1)" aria-label="Наступне фото"
                                class="absolute right-3 top-1/2 -translate-y-1/2 size-11 rounded-full bg-card/70 backdrop-blur text-ink shadow flex items-center justify-center opacity-80 transition hover:bg-card hover:opacity-100 group-hover:opacity-100 focus-visible:opacity-100">
                            <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <span class="absolute bottom-3 right-3 rounded-full bg-ink/60 text-bg text-sm font-semibold px-3 py-1" aria-live="polite">
                            <span x-text="active + 1">1</span> / {{ $animal->photos->count() }}
                        </span>
                    @endif
                </div>
                @if ($animal->photos->count() > 1)
                    <div class="grid grid-cols-5 gap-3">
                        @foreach ($animal->photos as $i => $photo)
                            <button type="button" x-on:click="active = {{ $i }}" class="aspect-[4/3] rounded-2xl overflow-hidden border-2 transition-opacity"
                                    x-bind:class="active === {{ $i }} ? 'border-primary' : 'border-transparent opacity-75 hover:opacity-100'"
                                    x-bind:aria-current="active === {{ $i }}"
                                    aria-label="Показати фото {{ $i + 1 }}">
                                <img src="{{ $photo->thumbUrl() }}" alt="" loading="lazy" class="size-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="card grow basis-80 min-w-0 p-7 flex flex-col gap-5">
                <div class="flex flex-col gap-2">
                    <h1 class="text-5xl leading-tight">{{ $animal->name }}</h1>
                    <span @class(['chip self-start', 'bg-sun-soft text-accent' => $animal->acceptsApplications(), 'bg-success-soft text-success' => ! $animal->acceptsApplications()])>
                        {{ $animal->species->name }} · {{ mb_strtolower($animal->status->label()) }}
                    </span>
                    @if ($animal->isFromVolunteer())
                        <span class="text-sm text-muted">Розміщено волонтером@if ($animal->current_location) · зараз: {{ $animal->current_location }}@endif</span>
                    @endif
                </div>
                <dl class="grid grid-cols-2 gap-x-5 gap-y-3.5">
                    @foreach ([
                        'Стать' => $animal->sex->label(),
                        'Вік' => $animal->ageLabel(),
                        'Порода' => $animal->breed?->name ?? 'Без породи',
                        'Забарвлення' => $animal->color?->name ?? '–',
                        'Область' => $animal->locality->region->name,
                        'Населений пункт' => $animal->locality->name,
                        'Стерилізація' => $animal->is_sterilized ? 'Проведена' : 'Немає',
                        'Вакцинація' => $animal->is_vaccinated ? 'Проведена' : 'Немає',
                    ] as $label => $value)
                        <div class="flex flex-col"><dt class="text-sm text-muted">{{ $label }}</dt><dd class="font-semibold">{{ $value }}</dd></div>
                    @endforeach
                </dl>

                @if ($animal->acceptsApplications())
                    <a href="{{ route('animals.adopt', $animal) }}" class="btn-primary min-h-14 text-lg">Взяти додому</a>
                @else
                    <p class="rounded-2xl bg-success-soft text-success px-4 py-3 font-medium">{{ $animal->foundHomePhrase() }}. Подивіться інших тварин, які чекають на вас.</p>
                @endif

                <div class="flex flex-wrap items-center gap-x-4 text-sm text-muted" x-data="{ copied: false }">
                    <span>Поділитися:</span>
                    <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($animal->name.' шукає дім') }}" target="_blank" rel="noopener" class="min-h-11 inline-flex items-center font-semibold text-primary hover:underline">Telegram</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="min-h-11 inline-flex items-center font-semibold text-primary hover:underline">Facebook</a>
                    <button type="button" class="min-h-11 font-semibold text-primary hover:underline"
                            x-on:click="navigator.clipboard.writeText(@js(url()->current())); copied = true; setTimeout(() => copied = false, 2000)">
                        <span x-show="!copied">Копіювати посилання</span><span x-show="copied" x-cloak>Скопійовано</span>
                    </button>
                </div>
            </aside>
        </div>

        <div class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(300px,1fr))]">
            @foreach (['Коротка історія' => $animal->story, 'Особливості поведінки' => $animal->behavior, 'Умови прилаштування' => $animal->adoption_terms] as $heading => $text)
                @if ($text)
                    <section class="card p-6 flex flex-col gap-2.5">
                        <h2 class="text-2xl">{{ $heading }}</h2>
                        <p class="text-muted whitespace-pre-line">{{ $text }}</p>
                    </section>
                @endif
            @endforeach
        </div>

        @if ($similar->isNotEmpty())
            <section class="flex flex-col gap-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-3xl">Вони теж шукають дім</h2>
                    <a href="{{ route('animals.index', ['species' => $animal->species->slug]) }}" class="font-semibold text-primary hover:underline">Дивитися всіх →</a>
                </div>
                <div class="grid gap-5 grid-cols-[repeat(auto-fill,minmax(230px,1fr))]">
                    @foreach ($similar as $other)
                        <x-animal-card :animal="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layout>
