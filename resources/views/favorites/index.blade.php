<x-layout title="{{ __('Обрані') }}">
    <section class="mx-auto max-w-6xl px-6 py-10 flex flex-col gap-7"
             x-data="{
                 loaded: {{ $animals === null ? 'false' : 'true' }},
                 async load() {
                     const ids = $store.favorites.ids;
                     if (!ids.length) { this.loaded = true; return; }
                     const res = await fetch('{{ route('favorites.cards') }}?ids=' + ids.join(','));
                     this.$refs.grid.innerHTML = await res.text();
                     this.loaded = true;
                 },
             }"
             @if ($animals === null) x-init="load()" @endif>
        <div class="flex flex-col gap-2">
            <h1 class="text-4xl">{{ __('Обрані') }}</h1>
            <p class="text-muted max-w-2xl">
                @auth
                    {{ __('Тварини, яких ви відмітили сердечком. Список збережено у вашому акаунті.') }}
                @else
                    {{ __('Тварини, яких ви відмітили сердечком. Поки ви не увійшли, список зберігається лише в цьому браузері –') }}
                    <a href="{{ route('login') }}" class="text-primary underline">{{ __('увійдіть') }}</a>{{ __(', щоб він не загубився.') }}
                @endauth
            </p>
        </div>

        <div class="animal-grid" x-ref="grid">
            @if ($animals !== null)
                @include('favorites.cards', ['animals' => $animals])
            @endif
        </div>

        <div x-cloak x-show="loaded && $store.favorites.ids.length === 0" class="card p-10 flex flex-col items-center gap-3 text-center">
            <x-mascot.sleep class="w-48" label="{{ __('Лапа дрімає') }}" />
            <h2 class="text-2xl">{{ __('Тут поки порожньо') }}</h2>
            <p class="text-muted max-w-md">{{ __('Натисніть сердечко на картці тварини, щоб не загубити тих, хто запав у душу.') }}</p>
            <a href="{{ route('animals.index') }}" class="btn-primary">{{ __('Знайти друга') }}</a>
        </div>
    </section>
</x-layout>
