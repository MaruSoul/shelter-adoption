@props(['animal'])
<div {{ $attributes->merge(['class' => 'card card-lift relative overflow-hidden group', 'data-reveal' => true]) }}>
<a href="{{ route('animals.show', $animal) }}" class="flex flex-col h-full focus-visible:outline-offset-[-4px]">
    <div class="relative aspect-[4/3] bg-tint-1 overflow-hidden">
        @if ($animal->coverPhoto)
            <img src="{{ $animal->coverPhoto->thumbUrl() }}" alt="{{ $animal->coverPhoto->altFor($animal) }}" loading="lazy" decoding="async" width="600" height="450" class="size-full object-cover card-photo">
        @endif
        @if ($animal->isFromVolunteer())
            <span class="chip absolute left-2 top-2 sm:left-3 sm:top-3 max-w-[calc(100%-3.5rem)] px-2 sm:px-3 bg-sun-soft text-accent text-[11px] sm:text-xs border border-sun/60"><span class="sm:hidden">{{ __('Від волонтера') }}</span><span class="hidden sm:inline">{{ __('Розміщено волонтером') }}</span></span>
        @endif
        @if ($animal->status === \App\Enums\AnimalStatus::Adopted)
            <span class="chip absolute left-2 top-2 sm:left-3 sm:top-3 px-2 sm:px-3 bg-success-soft text-success text-[11px] sm:text-xs">{{ __('Вже вдома') }}</span>
        @endif
    </div>
    <div class="p-3 pb-4 sm:p-4 sm:pb-5 flex flex-col gap-1 sm:gap-2">
        <div class="flex items-center justify-between gap-2">
            <span class="font-display font-extrabold text-lg sm:text-xl leading-tight group-hover:text-primary truncate">{{ $animal->display_name }}</span>
            <span class="chip bg-surface text-xs hidden sm:inline-flex">{{ $animal->species->label() }}</span>
        </div>
        <span class="text-xs sm:text-sm text-muted">{{ $animal->ageLabel() }} · {{ $animal->sex->label() }}<span class="hidden sm:inline"> · {{ \App\Support\Ukrainian::place($animal->locality->name) }}</span></span>
    </div>
</a>
@if ($animal->isPublic())
    <x-favorite-button :animal-id="$animal->id" :name="$animal->display_name" class="absolute right-2 top-2 sm:right-3 sm:top-3" />
@endif
</div>
