@props(['animal'])
<div {{ $attributes->merge(['class' => 'card card-lift relative overflow-hidden group', 'data-reveal' => true]) }}>
<a href="{{ route('animals.show', $animal) }}" class="flex flex-col h-full focus-visible:outline-offset-[-4px]">
    <div class="relative aspect-[4/3] bg-tint-1 overflow-hidden">
        @if ($animal->coverPhoto)
            <img src="{{ $animal->coverPhoto->thumbUrl() }}" alt="{{ $animal->coverPhoto->alt ?? $animal->name }}" loading="lazy" decoding="async" width="600" height="450" class="size-full object-cover card-photo">
        @endif
        @if ($animal->isFromVolunteer())
            <span class="chip absolute left-3 top-3 bg-sun-soft text-accent text-xs border border-sun/60">Розміщено волонтером</span>
        @endif
        @if ($animal->status === \App\Enums\AnimalStatus::Adopted)
            <span class="chip absolute left-3 top-3 bg-success-soft text-success text-xs">Вже вдома</span>
        @endif
    </div>
    <div class="p-4 pb-5 flex flex-col gap-2">
        <div class="flex items-center justify-between gap-2">
            <span class="font-display font-extrabold text-xl group-hover:text-primary">{{ $animal->name }}</span>
            <span class="chip bg-surface text-xs">{{ $animal->species->name }}</span>
        </div>
        <span class="text-sm text-muted">{{ $animal->ageLabel() }} · {{ $animal->sex->label() }} · {{ $animal->locality->name }}</span>
    </div>
</a>
@if ($animal->isPublic())
    <x-favorite-button :animal-id="$animal->id" :name="$animal->name" class="absolute right-3 top-3" />
@endif
</div>
