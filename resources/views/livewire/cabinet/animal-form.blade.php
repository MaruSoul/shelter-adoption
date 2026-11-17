<div class="mx-auto max-w-4xl px-6 py-10 flex flex-col gap-6">
    <div class="flex flex-col gap-1.5">
        <a href="{{ route('cabinet') }}" class="font-semibold text-primary hover:underline">{{ __('← Кабінет') }}</a>
        <h1 class="text-4xl">{{ $animal->exists ? __('Редагування картки') : __('Розмістити знайдену тварину') }}</h1>
        <p class="text-muted">{{ __('Картка зʼявиться в каталозі з позначкою «Розміщено волонтером» після перевірки адміністратором притулку.') }}</p>
    </div>
    <form wire:submit="save" class="card p-6 md:p-8 flex flex-col gap-5" novalidate>
        @include('livewire.partials.animal-fields')
        @include('livewire.partials.photo-manager')
        <button type="submit" class="btn-primary self-start min-h-12 px-8" wire:loading.attr="disabled">
            {{ $animal->exists ? __('Зберегти й надіслати на перевірку') : __('Надіслати на перевірку') }}
        </button>
    </form>
</div>
