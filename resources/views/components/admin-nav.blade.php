@php
    $newCount = \App\Models\AdoptionApplication::where('status', \App\Enums\ApplicationStatus::New)->count();
    $moderationCount = \App\Models\Animal::where('status', \App\Enums\AnimalStatus::Moderation)->count();
    $items = [
        ['admin.dashboard', 'Огляд', null],
        ['admin.applications.index', 'Анкети', $newCount],
        ['admin.animals.index', 'Тварини', null],
        ['admin.moderation', 'Модерація', $moderationCount],
        ['admin.posts.index', 'Статті', null],
        ['admin.dictionaries', 'Довідники', null],
        ['admin.seo', 'SEO', null],
        ['admin.settings', 'Налаштування', null],
    ];
@endphp
<nav aria-label="Панель притулку" class="bg-surface border-b border-line">
    <div class="mx-auto max-w-6xl px-6 flex flex-wrap gap-1">
        @foreach ($items as [$route, $label, $badge])
            @php($active = request()->routeIs($route) || request()->routeIs(str_replace('.index', '.*', $route)))
            <a href="{{ route($route) }}" @class(['px-4 py-3 border-b-2 -mb-px font-semibold inline-flex items-center gap-2', 'border-primary text-primary' => $active, 'border-transparent text-muted hover:text-ink' => ! $active]) @if($active) aria-current="page" @endif>
                {{ $label }}
                @if ($badge)<span class="chip bg-sun text-sun-ink text-xs px-2 py-0.5">{{ $badge }}</span>@endif
            </a>
        @endforeach
    </div>
</nav>
