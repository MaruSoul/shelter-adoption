<x-layout>
    <section class="mx-auto max-w-6xl px-6 pt-14 pb-10 flex flex-wrap items-center gap-12">
        <div class="grow basis-[440px] min-w-0 flex flex-col gap-6">
            <h1 class="text-5xl md:text-6xl leading-[1.08]">Знайдіть друга, який чекає саме на вас</h1>
            <p class="text-lg text-muted max-w-xl">Кожна тварина в нашому каталозі оглянута ветеринаром і готова до нового дому. Оберіть за характером, залиште анкету – ми зателефонуємо самі.</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('animals.index') }}" class="btn-primary min-h-13 px-7 text-lg">Знайти друга</a>
                <a href="{{ auth()->check() ? route('cabinet.animals.create') : route('register') }}" class="btn-sun min-h-13 px-7 text-lg">Я знайшов тварину</a>
            </div>
            <dl class="flex flex-wrap gap-8 pt-2">
                <div><dt class="text-sm text-muted">Шукають дім</dt><dd class="font-display font-extrabold text-3xl">{{ $waitingCount }}</dd></div>
                <div><dt class="text-sm text-muted">Знайшли дім через сайт</dt><dd class="font-display font-extrabold text-3xl">{{ $adoptedCount }}</dd></div>
            </dl>
        </div>
        <div class="grow basis-[380px] min-w-0 aspect-[5/4] rounded-[2rem] bg-sun-soft overflow-hidden grid grid-cols-2 gap-3 p-3">
            @foreach ($animals->take(4) as $a)
                <a href="{{ route('animals.show', $a) }}" class="rounded-2xl overflow-hidden bg-tint-1 block">
                    @if ($a->coverPhoto)<img src="{{ $a->coverPhoto->thumbUrl() }}" alt="{{ $a->name }}" class="size-full object-cover">@endif
                </a>
            @endforeach
        </div>
    </section>

    <section aria-label="Швидкий пошук" class="mx-auto max-w-6xl px-6 pb-14">
        <form action="{{ route('animals.index') }}" method="get" class="card p-5 flex flex-wrap gap-4 items-end shadow-lg shadow-primary/5">
            <label class="label grow basis-44 text-sm">Кого шукаєте
                <select name="species" class="field font-normal">
                    <option value="">Усі тварини</option>
                    @foreach ($species as $s)<option value="{{ $s->slug }}">{{ $s->name_plural }}</option>@endforeach
                </select>
            </label>
            <label class="label grow basis-44 text-sm">Вік
                <select name="age[]" class="field font-normal">
                    <option value="">Будь-який</option>
                    @foreach (\App\Enums\AgeGroup::cases() as $g)<option value="{{ $g->value }}">{{ $g->label() }}</option>@endforeach
                </select>
            </label>
            <label class="label grow basis-44 text-sm">Стать
                <select name="sex[]" class="field font-normal">
                    <option value="">Будь-яка</option>
                    @foreach (\App\Enums\Sex::cases() as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach
                </select>
            </label>
            <button type="submit" class="btn-primary rounded-xl min-h-12 px-7">Показати</button>
        </form>
    </section>

    <section class="mx-auto max-w-6xl px-6 pb-16 flex flex-col gap-7">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-4xl">Вони шукають дім</h2>
            <a href="{{ route('animals.index') }}" class="font-semibold text-primary hover:underline">Дивитися всіх →</a>
        </div>
        <div class="grid gap-6 grid-cols-[repeat(auto-fill,minmax(240px,1fr))]">
            @foreach ($animals as $animal)
                <x-animal-card :animal="$animal" />
            @endforeach
        </div>
    </section>

    <section id="help" class="bg-surface">
        <div class="mx-auto max-w-6xl px-6 py-16 flex flex-col gap-8">
            <h2 class="text-4xl">Як забрати друга додому</h2>
            <ol class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(260px,1fr))]">
                @foreach ([
                    ['Оберіть друга', 'Скористайтесь фільтрами за видом, віком і характером, перегляньте фото та історію тварини.'],
                    ['Заповніть анкету', 'Реєстрація не потрібна: залиште контакти й зручний спосіб звʼязку.'],
                    ['Познайомтесь', 'Ми зателефонуємо, домовимось про зустріч у притулку і допоможемо з адаптацією.'],
                ] as $i => [$title, $text])
                    <li class="bg-card rounded-3xl p-7 flex flex-col gap-3">
                        <span class="size-11 rounded-full bg-sun-soft text-accent flex items-center justify-center font-display font-extrabold text-xl" aria-hidden="true">{{ $i + 1 }}</span>
                        <h3 class="text-2xl">{{ $title }}</h3>
                        <p class="text-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
            <div class="rounded-3xl bg-card p-7 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-col gap-1 max-w-2xl">
                    <h3 class="text-2xl">Знайшли тварину на вулиці?</h3>
                    <p class="text-muted">Зареєструйтесь і розмістіть її картку. Після перевірки адміністратором вона зʼявиться в каталозі з позначкою «Розміщено волонтером».</p>
                </div>
                <a href="{{ auth()->check() ? route('cabinet.animals.create') : route('register') }}" class="btn-sun">Розмістити тварину</a>
            </div>
        </div>
    </section>

    @if ($posts->isNotEmpty())
        <section class="mx-auto max-w-6xl px-6 py-16 flex flex-col gap-7">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <h2 class="text-4xl">Корисне з блогу</h2>
                <a href="{{ route('blog.index') }}" class="font-semibold text-primary hover:underline">Усі статті →</a>
            </div>
            <div class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(280px,1fr))]">
                @foreach ($posts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        </section>
    @endif
</x-layout>
