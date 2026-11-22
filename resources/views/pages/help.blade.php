{{-- Сторінка «Як допомогти»: гроші (вкладки зі способами з панелі «Допомога»), звіти й інші способи підтримати притулок --}}
<x-layout :title="__('Як допомогти притулку')" :seo-title="$seo->tr('title')"
          :description="$seo->tr('description') ?: __('Підтримайте притулок «:name»: банка Monobank, переказ за реквізитами, PayPal. Звіти про витрати й інші способи допомогти тваринам.', ['name' => $shelter->tr('name')])">
    <section class="mx-auto max-w-6xl w-full px-6 pt-12 pb-6 flex flex-col gap-4">
        <h1 class="text-4xl md:text-5xl">{{ $seo->tr('h1') ?: __('Як допомогти притулку') }}</h1>
        @if ($intro = $shelter->tr('help_text'))
            <div class="seo-text prose-links text-lg max-w-3xl">{{ \App\Support\SimpleMarkup::block($intro) }}</div>
        @endif
    </section>

    @if ($methods->isNotEmpty())
        <section class="mx-auto max-w-6xl w-full px-6 py-6 flex flex-col gap-4" aria-labelledby="donate-heading">
            <h2 id="donate-heading" class="text-3xl">{{ __('Підтримати фінансово') }}</h2>
            <x-donation-tabs :methods="$methods" />
        </section>
    @endif

    @if ($shelter->reports_url)
        <section class="mx-auto max-w-6xl w-full px-6 py-6">
            <div class="rounded-3xl bg-sun-soft p-7 flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-col gap-1 max-w-2xl">
                    <h2 class="text-2xl">{{ __('Куди йдуть кошти') }}</h2>
                    <p class="text-muted">{{ __('Ми публікуємо звіти: на що витрачено кожну гривню – корм, лікування, стерилізація.') }}</p>
                </div>
                <a href="{{ $shelter->reports_url }}" target="_blank" rel="noopener" class="btn-primary">{{ __('Переглянути звіти') }} ↗</a>
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-6xl w-full px-6 py-6 flex flex-col gap-4">
        <h2 class="text-3xl">{{ __('Інші способи допомогти') }}</h2>
        <ul class="grid gap-6 grid-cols-[repeat(auto-fit,minmax(260px,1fr))]">
            @foreach ([
                [__('Візьміть друга додому'), __('Найкраща допомога – новий дім. Подивіться, хто на вас чекає.'), route('animals.index'), __('Знайти друга')],
                [__('Розмістіть знайдену тварину'), __('Знайшли тварину на вулиці? Додайте її картку, і ми допоможемо знайти їй родину.'), auth()->check() ? route('cabinet.animals.create') : route('register'), __('Розмістити тварину')],
                [__('Розкажіть друзям'), __('Поділіться сторінкою тварини в соцмережах – так її побачить більше людей.'), route('blog.index'), __('Читати блог')],
            ] as [$title, $text, $url, $action])
                <li class="bg-card rounded-3xl p-7 flex flex-col gap-3 border border-line">
                    <h3 class="text-2xl">{{ $title }}</h3>
                    <p class="text-muted grow">{{ $text }}</p>
                    <a href="{{ $url }}" class="font-semibold text-primary hover:underline">{{ $action }} →</a>
                </li>
            @endforeach
        </ul>
    </section>

    <x-seo-text :seo="$seo" />
</x-layout>
