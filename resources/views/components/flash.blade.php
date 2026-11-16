@props(['message' => null])
@if ($message)
    <div {{ $attributes->merge(['class' => 'mx-auto max-w-6xl w-full px-6 pt-6']) }} role="status" wire:key="flash-{{ md5($message) }}">
        <div class="rounded-2xl bg-success-soft text-success px-5 py-3 font-medium">{{ $message }}</div>
    </div>
@endif
