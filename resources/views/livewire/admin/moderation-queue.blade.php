<div>
    <x-admin-nav />
    <x-flash :message="$flash" />
    <div class="mx-auto max-w-6xl px-6 py-8 flex flex-col gap-6">
        <div class="flex flex-col gap-1">
            <h1 class="text-4xl">Модерація</h1>
            <p class="text-muted">Картки, які розмістили волонтери. Після схвалення тварина зʼявиться в каталозі з позначкою «Розміщено волонтером».</p>
        </div>
        @forelse ($animals as $animal)
            <article class="card p-6 flex flex-wrap gap-6" wire:key="m-{{ $animal->id }}">
                <div class="grid grid-cols-3 gap-2 grow basis-72 max-w-sm self-start">
                    @foreach ($animal->photos->take(3) as $photo)
                        <img src="{{ $photo->thumbUrl() }}" alt="{{ $photo->alt }}" class="aspect-square rounded-xl object-cover">
                    @endforeach
                </div>
                <div class="grow-[999] basis-96 min-w-0 flex flex-col gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl">{{ $animal->name }}</h2>
                        <span class="text-muted">{{ $animal->species->name }} · {{ $animal->sex->label() }} · {{ $animal->ageLabel() }} · {{ $animal->locality->name }}</span>
                    </div>
                    <p class="text-sm text-muted">Розмістив(-ла): {{ $animal->author?->name }} ({{ $animal->author?->email }}) · {{ $animal->updated_at->format('d.m.Y H:i') }}</p>
                    @if ($animal->current_location)<p><strong>Де зараз:</strong> {{ $animal->current_location }}</p>@endif
                    @if ($animal->story)<p class="text-muted whitespace-pre-line">{{ $animal->story }}</p>@endif
                    <div class="flex flex-wrap items-end gap-3 pt-2">
                        <button type="button" wire:click="approve({{ $animal->id }})" class="btn-primary">Схвалити</button>
                        <label class="label grow basis-64 text-sm">Причина відхилення
                            <input type="text" wire:model="reasons.{{ $animal->id }}" class="field font-normal" placeholder="Що потрібно виправити">
                            @error('reasons.'.$animal->id)<span class="error">{{ $message }}</span>@enderror
                        </label>
                        <button type="button" wire:click="reject({{ $animal->id }})" class="btn border-[1.5px] border-danger text-danger">Відхилити</button>
                    </div>
                </div>
            </article>
        @empty
            <p class="card p-8 text-muted">Черга порожня – усі картки перевірено.</p>
        @endforelse
    </div>
</div>
