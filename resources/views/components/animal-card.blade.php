@props(['animal'])
<a href="{{ route('animals.show', $animal) }}" {{ $attributes->merge(['class' => 'card overflow-hidden flex flex-col hover:shadow-lg hover:shadow-primary/5 transition-shadow group']) }}>
    <div class="relative aspect-[4/3] bg-tint-1">
        @if ($animal->coverPhoto)
            <img src="{{ $animal->coverPhoto->thumbUrl() }}" alt="{{ $animal->coverPhoto->alt ?? $animal->name }}" loading="lazy" decoding="async" width="600" height="450" class="size-full object-cover">
        @endif
        @if ($animal->isFromVolunteer())
            <span class="chip absolute left-3 top-3 bg-sun text-sun-ink text-xs">Розміщено волонтером</span>
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
