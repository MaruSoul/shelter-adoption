@php($seo = \App\Models\SeoPage::for('home'))
<x-layout :seo-title="$seo->tr('title')" :description="$seo->tr('description')">
    <x-slot:head><x-json-ld :data="\App\Support\StructuredData::shelter(\App\Models\ShelterSetting::current())" /></x-slot:head>
    <section class="mx-auto max-w-6xl px-6 pt-14 pb-10 flex flex-wrap items-center gap-12">
        <div class="grow basis-[440px] min-w-0 flex flex-col gap-6">
            <h1 class="text-5xl md:text-6xl leading-[1.08]">{{ $seo->tr('h1') ?: __('Знайдіть друга, який чекає саме на вас') }}</h1>
            <p class="text-lg text-muted max-w-xl">{{ __('Кожна тварина в нашому каталозі оглянута ветеринаром і готова до нового дому. Оберіть за характером, залиште анкету – ми зателефонуємо самі.') }}</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('animals.index') }}" class="btn-primary min-h-13 px-7 text-lg">{{ __('Знайти друга') }}</a>
                <a href="{{ auth()->check() ? route('cabinet.animals.create') : route('register') }}" class="btn-sun min-h-13 px-7 text-lg">{{ __('Я знайшов тварину') }}</a>
            </div>
            <dl class="flex flex-wrap gap-8 pt-2">
                <div><dt class="text-sm text-muted">{{ __('Шукають дім') }}</dt><dd class="font-display font-extrabold text-3xl">{{ $waitingCount }}</dd></div>
                <div><dt class="text-sm text-muted">{{ __('Знайшли дім через сайт') }}</dt><dd class="font-display font-extrabold text-3xl">{{ $adoptedCount }}</dd></div>
            </dl>
        </div>
        @if (count($lottery) > 0)
            <x-friend-lottery :pool="$lottery" class="grow basis-[380px] min-w-0" />
        @endif
    </section>

    @if (count($quickPicks) > 0)
        <section aria-labelledby="quick-picks" class="mx-auto max-w-6xl px-6 pb-14 flex flex-col gap-4">
            <h2 id="quick-picks" class="text-sm font-semibold uppercase tracking-wide text-muted">{{ __('Швидкий вибір') }}</h2>
            <ul class="flex gap-3 overflow-x-auto -mx-6 px-6 pb-2 snap-x sm:flex-wrap sm:overflow-visible sm:mx-0 sm:px-0">
                @foreach ($quickPicks as $pick)
                    <li class="snap-start shrink-0">
                        <a href="{{ $pick['url'] }}" class="card card-lift flex items-center gap-3 rounded-2xl pl-5 pr-3 py-3 hover:border-primary">
                            <span class="flex flex-col leading-tight">
                                <span class="font-display font-extrabold text-lg">{{ $pick['label'] }}</span>
                                <span class="text-xs text-muted">{{ $pick['hint'] }}</span>
                            </span>
                            <span class="min-w-9 h-9 px-2 rounded-full bg-sun-soft text-accent font-bold flex items-center justify-center" aria-label="{{ trans_choice(':count тварина|:count тварини|:count тварин', $pick['count']) }}">{{ $pick['count'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <section class="mx-auto max-w-6xl px-6 pb-16 flex flex-col gap-7">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-4xl" data-reveal>{{ __('Вони шукають дім') }}</h2>
            <a href="{{ route('animals.index') }}" class="font-semibold text-primary hover:underline">{{ __('Дивитися всіх →') }}</a>
        </div>
        <div class="animal-grid">
            @foreach ($animals as $animal)
                <x-animal-card :animal="$animal" />
            @endforeach
        </div>
    </section>

    <section id="help" class="bg-surface">
        <div class="mx-auto max-w-6xl px-6 py-16 flex flex-col gap-8">
            <h2 class="text-4xl" data-reveal>{{ __('Як забрати друга додому') }}</h2>
            <ol class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(260px,1fr))]">
                @foreach ([
                    [__('Оберіть друга'), __('Скористайтесь фільтрами за видом, віком і характером, перегляньте фото та історію тварини.')],
                    [__('Заповніть анкету'), __('Реєстрація не потрібна: залиште контакти й зручний спосіб звʼязку.')],
                    [__('Познайомтесь'), __('Ми зателефонуємо, домовимось про зустріч у притулку і допоможемо з адаптацією.')],
                ] as $i => [$title, $text])
                    <li class="bg-card rounded-3xl p-7 flex flex-col gap-3" data-reveal>
                        <span class="size-11 rounded-full bg-sun-soft text-accent flex items-center justify-center font-display font-extrabold text-xl" aria-hidden="true">{{ $i + 1 }}</span>
                        <h3 class="text-2xl">{{ $title }}</h3>
                        <p class="text-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
            <div class="rounded-3xl bg-card p-7 flex flex-wrap items-center justify-between gap-4" data-reveal>
                <div class="flex flex-col gap-1 max-w-2xl">
                    <h3 class="text-2xl">{{ __('Знайшли тварину на вулиці?') }}</h3>
                    <p class="text-muted">{{ __('Зареєструйтесь і розмістіть її картку. Після перевірки адміністратором вона зʼявиться в каталозі з позначкою «Розміщено волонтером».') }}</p>
                </div>
                <a href="{{ auth()->check() ? route('cabinet.animals.create') : route('register') }}" class="btn-sun">{{ __('Розмістити тварину') }}</a>
            </div>
            {{-- Фінансова допомога: вступ і способи з панелі «Допомога»; нічого не заповнено – блоку немає --}}
            @php($donations = \App\Models\DonationMethod::query()->shown()->get())
            @php($helpText = \App\Models\ShelterSetting::current()->tr('help_text'))
            @if ($donations->isNotEmpty() || $helpText)
                <div class="flex flex-col gap-4" data-reveal>
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div class="flex flex-col gap-1 max-w-3xl">
                            <h3 class="text-3xl">{{ __('Як ще допомогти притулку') }}</h3>
                            @if ($helpText)<div class="seo-text prose-links">{{ \App\Support\SimpleMarkup::block($helpText) }}</div>@endif
                        </div>
                        <a href="{{ route('help') }}" class="font-semibold text-primary hover:underline">{{ __('Усі способи допомогти') }} →</a>
                    </div>
                    <x-donation-tabs :methods="$donations" />
                </div>
            @endif
        </div>
    </section>

    @if ($posts->isNotEmpty())
        <section class="mx-auto max-w-6xl px-6 py-16 flex flex-col gap-7">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 class="text-4xl" data-reveal>{{ __('Корисне з блогу') }}</h2>
                <a href="{{ route('blog.index') }}" class="font-semibold text-primary hover:underline">{{ __('Усі статті →') }}</a>
            </div>
            <div class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(280px,1fr))]">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </section>
    @endif

    <x-seo-text :seo="$seo" />
</x-layout>
