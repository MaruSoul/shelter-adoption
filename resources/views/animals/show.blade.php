@php($cover = $animal->photos->first())
<x-layout :title="__(':name – :species шукає дім', ['name' => $animal->name, 'species' => mb_strtolower(__($animal->species->name))])"
          :description="\Illuminate\Support\Str::limit($animal->story ?? $animal->behavior ?? '', 155)"
          :image="$cover?->url()">
    <x-slot:head><x-json-ld :data="\App\Support\StructuredData::animalBreadcrumbs($animal)" /></x-slot:head>
    <div class="mx-auto max-w-6xl px-6 py-7 flex flex-col gap-10">
        <nav aria-label="{{ __('Навігаційний ланцюжок') }}" class="text-sm text-muted flex flex-wrap gap-2">
            <a href="{{ route('home') }}" class="hover:underline">{{ __('Головна') }}</a><span aria-hidden="true">/</span>
            <a href="{{ route('animals.index', ['species' => $animal->species->slug]) }}" class="hover:underline">{{ __($animal->species->name_plural) }}</a><span aria-hidden="true">/</span>
            <span class="text-ink">{{ $animal->name }}</span>
        </nav>

        @unless ($animal->isPublic())
            <div role="status" class="rounded-2xl bg-sun-soft px-5 py-3 font-medium">
                {{ __('Ця картка зараз не видна в каталозі: :status.', ['status' => mb_strtolower($animal->status->label())]) }}
                @if ($animal->moderation_note) {{ __('Коментар модератора:') }} {{ $animal->moderation_note }} @endif
            </div>
        @endunless

        <div class="flex flex-wrap gap-10 items-start">
            {{-- Галерея: стрілки видно завжди (на телефоні немає наведення), гортання клавішами ← → і свайпом --}}
            <div class="grow-[999] basis-[520px] min-w-0 flex flex-col gap-3"
                 x-data="{
                     active: 0,
                     startX: null,
                     photos: @js($animal->photos->map(fn ($p) => ['url' => $p->url(), 'srcset' => $p->thumbUrl().' 600w, '.$p->url().' 1400w', 'alt' => $p->alt ?? $animal->name])->values()),
                     go(step) { this.active = (this.active + step + this.photos.length) % this.photos.length },
                     swipe(endX) { if (this.startX !== null && Math.abs(endX - this.startX) > 40) this.go(endX < this.startX ? 1 : -1); this.startX = null },
                 }">
                <div class="group relative aspect-[4/3] rounded-[1.75rem] bg-tint-1 overflow-hidden"
                     @if ($animal->photos->count() > 1)
                         role="region" aria-roledescription="{{ __('галерея') }}" aria-label="{{ __('Фото :name', ['name' => $animal->name]) }}" tabindex="0"
                         x-on:keydown.left.prevent="go(-1)" x-on:keydown.right.prevent="go(1)"
                         x-on:touchstart.passive="startX = $event.touches[0].clientX"
                         x-on:touchend="swipe($event.changedTouches[0].clientX)"
                     @endif>
                    {{-- Перше фото є в HTML одразу (браузер починає його вантажити ще до запуску скриптів), далі галерея лише підміняє адресу --}}
                    @if ($cover)
                        {{-- На телефоні браузер бере мініатюру 600 px, на великому екрані – повне фото --}}
                        <img src="{{ $cover->url() }}" srcset="{{ $cover->thumbUrl() }} 600w, {{ $cover->url() }} 1400w"
                             sizes="(min-width: 1024px) 720px, 100vw" alt="{{ $cover->alt ?? $animal->name }}" fetchpriority="high" width="1200" height="900"
                             x-bind:src="photos[active].url" x-bind:srcset="photos[active].srcset" x-bind:alt="photos[active].alt" class="size-full object-cover">
                    @endif
                    @if ($animal->photos->count() > 1)
                        <button type="button" x-on:click="go(-1)" aria-label="{{ __('Попереднє фото') }}"
                                class="absolute left-3 top-1/2 -translate-y-1/2 size-11 rounded-full bg-card/70 backdrop-blur text-ink shadow flex items-center justify-center opacity-80 transition hover:bg-card hover:opacity-100 group-hover:opacity-100 focus-visible:opacity-100">
                            <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
                        </button>
                        <button type="button" x-on:click="go(1)" aria-label="{{ __('Наступне фото') }}"
                                class="absolute right-3 top-1/2 -translate-y-1/2 size-11 rounded-full bg-card/70 backdrop-blur text-ink shadow flex items-center justify-center opacity-80 transition hover:bg-card hover:opacity-100 group-hover:opacity-100 focus-visible:opacity-100">
                            <svg viewBox="0 0 24 24" class="size-6" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
                        </button>
                        <span class="absolute bottom-3 right-3 rounded-full bg-ink/60 text-bg text-sm font-semibold px-3 py-1" aria-live="polite">
                            <span x-text="active + 1">1</span> / {{ $animal->photos->count() }}
                        </span>
                    @endif
                </div>
                @if ($animal->photos->count() > 1)
                    <div class="grid grid-cols-5 sm:grid-cols-6 gap-2 sm:gap-3">
                        @foreach ($animal->photos as $i => $photo)
                            <button type="button" x-on:click="active = {{ $i }}" class="aspect-[4/3] rounded-2xl overflow-hidden border-2 transition-opacity"
                                    x-bind:class="active === {{ $i }} ? 'border-primary' : 'border-transparent opacity-75 hover:opacity-100'"
                                    x-bind:aria-current="active === {{ $i }}"
                                    aria-label="{{ __('Показати фото :n', ['n' => $i + 1]) }}">
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
                        {{ __($animal->species->name) }} · {{ mb_strtolower($animal->status->label()) }}
                    </span>
                    @if ($animal->isFromVolunteer())
                        <span class="text-sm text-muted">{{ __('Розміщено волонтером') }}@if ($animal->current_location) · {{ __('зараз:') }} {{ $animal->current_location }}@endif</span>
                    @endif
                </div>
                <dl class="grid grid-cols-2 gap-x-5 gap-y-3.5">
                    @foreach ([
                        __('Стать') => $animal->sex->label(),
                        __('Вік') => $animal->ageLabel(),
                        __('Порода') => $animal->breed ? __($animal->breed->name) : __('Без породи'),
                        __('Забарвлення') => $animal->color ? __($animal->color->name) : '–',
                        __('Область') => __($animal->locality->region->name),
                        __('Населений пункт') => \App\Support\Ukrainian::place($animal->locality->name),
                        __('Стерилізація') => $animal->is_sterilized ? __('Проведена') : __('Немає'),
                        __('Вакцинація') => $animal->is_vaccinated ? __('Проведена') : __('Немає'),
                    ] as $label => $value)
                        <div class="flex flex-col"><dt class="text-sm text-muted">{{ $label }}</dt><dd class="font-semibold">{{ $value }}</dd></div>
                    @endforeach
                </dl>

                @if ($animal->acceptsApplications())
                    <a href="{{ route('animals.adopt', $animal) }}" class="btn-primary min-h-14 text-lg" x-data
                       x-init="new IntersectionObserver(([e]) => $dispatch('main-cta', e.isIntersecting)).observe($el)">{{ __('Взяти додому') }}</a>
                @endif
                @if ($animal->isPublic())
                    <x-favorite-button :animal-id="$animal->id" :name="$animal->name" :large="true" />
                @endif
                @if ($animal->acceptsApplications())
                @else
                    <p class="rounded-2xl bg-success-soft text-success px-4 py-3 font-medium">{{ $animal->foundHomePhrase() }}. {{ __('Подивіться інших тварин, які чекають на вас.') }}</p>
                @endif

                @php($shareUrl = url()->current())
                @php($shareText = __(':name шукає дім', ['name' => $animal->name]))
                <div class="flex flex-wrap items-center gap-2 text-sm text-muted" x-data="{ copied: false, canShare: !! navigator.share }">
                    <span class="w-full">{{ __('Поділитися:') }}</span>
                    {{-- На телефоні – системне меню «Поділитися» з усіма месенджерами --}}
                    <button type="button" x-show="canShare" x-cloak class="share-btn" aria-label="{{ __('Поділитися') }}"
                            x-on:click="navigator.share({ title: @js($shareText), url: @js($shareUrl) }).catch(() => {})">
                        <x-icon.social network="share" class="size-5" />
                    </button>
                    <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="share-btn" aria-label="{{ __('Поділитися в Telegram') }}" title="Telegram">
                        <x-icon.social network="telegram" class="size-5" />
                    </a>
                    <a href="viber://forward?text={{ rawurlencode($shareText.' '.$shareUrl) }}" class="share-btn" aria-label="{{ __('Поділитися у Viber') }}" title="Viber">
                        <x-icon.social network="viber" class="size-5" />
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="share-btn" aria-label="{{ __('Поділитися у Facebook') }}" title="Facebook">
                        <x-icon.social network="facebook" class="size-5" />
                    </a>
                    <button type="button" class="share-btn" x-bind:aria-label="copied ? @js(__('Посилання скопійовано')) : @js(__('Копіювати посилання'))" title="{{ __('Копіювати посилання') }}"
                            x-on:click="navigator.clipboard.writeText(@js($shareUrl)); copied = true; setTimeout(() => copied = false, 2000)">
                        <x-icon.social network="link" class="size-5" />
                    </button>
                    <span x-show="copied" x-cloak role="status" class="font-semibold text-success">{{ __('Скопійовано') }}</span>
                </div>
            </aside>
        </div>

        @if (app()->getLocale() !== 'uk' && ($animal->story || $animal->behavior || $animal->adoption_terms))
            {{-- Тексти про тварину пишуть люди українською – чесно попереджаємо англомовного відвідувача --}}
            <p class="text-sm text-muted -mb-6">{{ __('The animal’s story is written by the shelter in Ukrainian.') }}</p>
        @endif
        <div class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(300px,1fr))]">
            @foreach ([__('Коротка історія') => $animal->story, __('Особливості поведінки') => $animal->behavior, __('Умови прилаштування') => $animal->adoption_terms] as $heading => $text)
                @if ($text)
                    <section class="card p-6 flex flex-col gap-2.5">
                        <h2 class="text-2xl">{{ $heading }}</h2>
                        <p class="text-muted whitespace-pre-line" lang="uk">{{ $text }}</p>
                    </section>
                @endif
            @endforeach
        </div>

        @if ($similar->isNotEmpty())
            <section class="flex flex-col gap-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="text-3xl">{{ __('Вони теж шукають дім') }}</h2>
                    <a href="{{ route('animals.index', ['species' => $animal->species->slug]) }}" class="font-semibold text-primary hover:underline">{{ __('Дивитися всіх →') }}</a>
                </div>
                <div class="animal-grid">
                    @foreach ($similar as $other)
                        <x-animal-card :animal="$other" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @if ($animal->acceptsApplications())
        {{-- На телефоні кнопка «Взяти додому» завжди під рукою, поки основна кнопка поза екраном --}}
        <div class="lg:hidden fixed inset-x-0 bottom-0 z-30 border-t border-line bg-card/95 backdrop-blur px-4 py-3 pb-[max(.75rem,env(safe-area-inset-bottom))] flex items-center gap-3 transition-transform duration-200"
             x-data="{ cta: true, footer: false, get hidden() { return this.cta || this.footer } }"
             x-init="const f = document.querySelector('footer'); f && new IntersectionObserver(([e]) => footer = e.isIntersecting).observe(f)"
             x-on:main-cta.window="cta = $event.detail"
             x-bind:class="hidden ? 'translate-y-full' : 'translate-y-0'" x-bind:aria-hidden="hidden" x-bind:inert="hidden">
            <div class="min-w-0 grow leading-tight">
                <span class="block font-display font-extrabold truncate">{{ $animal->name }}</span>
                <span class="block text-xs text-muted truncate">{{ $animal->ageLabel() }} · {{ $animal->sex->label() }}</span>
            </div>
            <x-favorite-button :animal-id="$animal->id" :name="$animal->name" class="shrink-0 border border-line" />
            <a href="{{ route('animals.adopt', $animal) }}" class="btn-primary shrink-0 min-h-12 px-5">{{ __('Взяти додому') }}</a>
        </div>
    @endif
</x-layout>
