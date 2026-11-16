<div>
    <x-admin-nav />
    <div class="mx-auto max-w-6xl px-6 py-8 flex flex-col gap-8">
        <h1 class="text-4xl">Огляд</h1>
        <div class="grid gap-4 grid-cols-[repeat(auto-fit,minmax(190px,1fr))]">
            @foreach ($stats as [$label, $value, $url, $urgent])
                <a href="{{ $url }}" @class(['card card-lift p-5 flex flex-col gap-1 group hover:border-primary', 'border-sun bg-sun-soft' => $urgent && $value > 0])>
                    <span class="flex items-center justify-between gap-2 text-sm text-muted">{{ $label }}
                        <span aria-hidden="true" class="text-primary transition-transform group-hover:translate-x-1">→</span>
                    </span>
                    <span class="font-display font-extrabold text-4xl">{{ $value }}</span>
                </a>
            @endforeach
        </div>
        <section class="flex flex-col gap-4">
            <div class="flex items-end justify-between gap-3">
                <h2 class="text-2xl">Нові анкети</h2>
                <a href="{{ route('admin.applications.index') }}" class="font-semibold text-primary hover:underline">Усі анкети →</a>
            </div>
            @forelse ($latest as $application)
                <a href="{{ route('admin.applications.show', $application) }}" class="card p-4 flex flex-wrap items-center justify-between gap-3 hover:border-primary">
                    <span><strong>{{ $application->name }}</strong> → {{ $application->animal->name }}</span>
                    <span class="text-sm text-muted">{{ $application->created_at->diffForHumans() }}</span>
                </a>
            @empty
                <p class="card p-6 text-muted">Нових анкет немає.</p>
            @endforelse
        </section>
    </div>
</div>
