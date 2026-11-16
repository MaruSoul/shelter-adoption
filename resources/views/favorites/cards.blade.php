@foreach ($animals as $animal)
    <div x-show="$store.favorites.has({{ $animal->id }})" x-transition.opacity>
        <x-animal-card :animal="$animal" />
    </div>
@endforeach
