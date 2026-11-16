@props(['animalId', 'name' => 'тварину', 'large' => false])
{{-- Сердечко «в обрані». Стан береться зі сховища Alpine $store.favorites (браузер для гостя, база для користувача). --}}
<button type="button"
        x-data
        x-on:click.prevent.stop="$store.favorites.toggle({{ $animalId }})"
        x-bind:aria-pressed="$store.favorites.has({{ $animalId }}) ? 'true' : 'false'"
        x-bind:aria-label="$store.favorites.has({{ $animalId }}) ? 'Прибрати {{ $name }} з обраних' : 'Додати {{ $name }} в обрані'"
        x-bind:title="$store.favorites.has({{ $animalId }}) ? 'В обраних' : 'Додати в обрані'"
        {{ $attributes->class([
            'fav-btn inline-flex items-center justify-center gap-2 font-semibold transition-transform active:scale-90',
            'size-11 rounded-full bg-card/85 backdrop-blur shadow hover:bg-card' => ! $large,
            'btn-outline min-h-12' => $large,
        ]) }}>
    <svg viewBox="0 0 24 24" class="{{ $large ? 'size-5' : 'size-6' }} text-danger" aria-hidden="true">
        <path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 8 3.4 4.5 6.9 4.5c2 0 3.6 1.1 5.1 3 1.5-1.9 3.1-3 5.1-3 3.5 0 5.5 3.5 4.2 6.8-1.8 4.6-9.3 9.2-9.3 9.2z"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"
              x-bind:fill="$store.favorites.has({{ $animalId }}) ? 'currentColor' : 'none'"/>
    </svg>
    @if ($large)
        <span x-text="$store.favorites.has({{ $animalId }}) ? 'В обраних' : 'В обрані'">В обрані</span>
    @endif
</button>
