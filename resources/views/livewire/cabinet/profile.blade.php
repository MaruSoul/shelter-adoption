<div class="mx-auto max-w-2xl px-6 py-10 flex flex-col gap-6">
    <a href="{{ route('cabinet') }}" class="font-semibold text-primary hover:underline">← До кабінету</a>
    <div class="flex flex-col gap-1">
        <h1 class="text-4xl">Мій профіль</h1>
        <p class="text-muted">Ці дані автоматично підставляються в анкету «Взяти додому». В анкеті їх завжди можна змінити.</p>
    </div>

    @if ($flash)
        <div class="rounded-2xl bg-success-soft text-success px-5 py-3 font-medium" role="status" wire:key="flash-{{ md5($flash) }}">{{ $flash }}</div>
    @endif

    <form wire:submit="save" class="card p-6 md:p-8 flex flex-col gap-5" novalidate>
        <label class="label">Імʼя *
            <input type="text" wire:model="name" autocomplete="name" class="field font-normal">
            @error('name')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Телефон
            <input type="tel" wire:model.blur="phone" autocomplete="tel" inputmode="tel" class="field font-normal">
            @error('phone')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Область
            <select wire:model="region_id" class="field font-normal">
                <option value="">Не вказано</option>
                @foreach ($regions as $region)<option value="{{ $region->id }}">{{ $region->name }}</option>@endforeach
            </select>
            @error('region_id')<span class="error">{{ $message }}</span>@enderror
        </label>
        <div class="label">Електронна пошта
            <span class="field font-normal flex items-center bg-surface text-muted">{{ auth()->user()->email }}</span>
            <span class="text-sm font-normal text-muted">Пошта привʼязана до ваших анкет, тому змінити її тут не можна.</span>
        </div>
        <button type="submit" class="btn-primary self-start min-h-12 px-8">Зберегти</button>
    </form>
</div>
