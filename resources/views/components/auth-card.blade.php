@props(['title', 'subtitle' => null])
<x-layout :title="$title">
    <div class="mx-auto max-w-md px-6 py-14">
        <div class="card p-8 flex flex-col gap-6">
            <div class="flex flex-col gap-1.5">
                <h1 class="text-3xl">{{ $title }}</h1>
                @if ($subtitle)<p class="text-muted">{{ $subtitle }}</p>@endif
            </div>
            {{ $slot }}
        </div>
    </div>
</x-layout>
