{{-- Спільні поля картки тварини. Очікує $species, $colors, $regions і форму $form. --}}
<x-bilingual :label="__('Кличка')" model="form.name" required translit optional
             :hint="__('англійською – необовʼязково: якщо не вписати, сайт покаже транслітерацію')"
             :en-placeholder="__('Наприклад, Felix')" />

<div class="grid gap-4 grid-cols-[repeat(auto-fit,minmax(220px,1fr))]">
    <label class="label">{{ __('Вид *') }}
        <select wire:model.live="form.species_id" class="field font-normal">
            <option value="">{{ __('Оберіть') }}</option>
            @foreach ($species as $s)<option value="{{ $s->id }}">{{ $s->label() }}</option>@endforeach
        </select>
        @error('form.species_id')<span class="error">{{ $message }}</span>@enderror
    </label>
    <label class="label">{{ __('Стать *') }}
        <select wire:model="form.sex" class="field font-normal">
            <option value="">{{ __('Оберіть') }}</option>
            @foreach (\App\Enums\Sex::cases() as $sex)<option value="{{ $sex->value }}">{{ $sex->label() }}</option>@endforeach
        </select>
        @error('form.sex')<span class="error">{{ $message }}</span>@enderror
    </label>
    <label class="label">{{ __('Орієнтовна дата народження *') }}
        <input type="date" wire:model="form.birth_date" max="{{ now()->toDateString() }}" class="field font-normal">
        @error('form.birth_date')<span class="error">{{ $message }}</span>@enderror
    </label>
    <label class="label">{{ __('Порода') }}
        <select wire:model="form.breed_id" class="field font-normal" @disabled(! $form->species_id)>
            <option value="">{{ __('Без породи / невідомо') }}</option>
            @foreach ($form->breeds() as $b)<option value="{{ $b->id }}">{{ $b->label() }}</option>@endforeach
        </select>
        @error('form.breed_id')<span class="error">{{ $message }}</span>@enderror
    </label>
    <label class="label">{{ __('Забарвлення') }}
        <select wire:model="form.color_id" class="field font-normal">
            <option value="">{{ __('Не вказано') }}</option>
            @foreach ($colors as $c)<option value="{{ $c->id }}">{{ $c->label() }}</option>@endforeach
        </select>
    </label>
    <label class="label">{{ __('Область *') }}
        <select wire:model.live="form.region_id" class="field font-normal">
            <option value="">{{ __('Оберіть') }}</option>
            @foreach ($regions as $r)<option value="{{ $r->id }}">{{ __($r->name) }}</option>@endforeach
        </select>
        @error('form.region_id')<span class="error">{{ $message }}</span>@enderror
    </label>
    <label class="label">{{ __('Населений пункт *') }}
        <select wire:model="form.locality_id" class="field font-normal" @disabled(! $form->region_id)>
            <option value="">{{ __('Оберіть') }}</option>
            @foreach ($form->localities() as $l)<option value="{{ $l->id }}">{{ \App\Support\Ukrainian::place($l->name) }}</option>@endforeach
        </select>
        @error('form.locality_id')<span class="error">{{ $message }}</span>@enderror
    </label>
</div>

<div class="flex flex-wrap gap-6">
    <label class="flex items-center gap-2.5 min-h-8"><input type="checkbox" wire:model="form.is_sterilized" class="size-4 accent-primary"> {{ __('Стерилізація проведена') }}</label>
    <label class="flex items-center gap-2.5 min-h-8"><input type="checkbox" wire:model="form.is_vaccinated" class="size-4 accent-primary"> {{ __('Вакцинація проведена') }}</label>
</div>

<x-bilingual :label="__('Де тварина зараз')" model="form.current_location" optional
             :placeholder="__('наприклад, «перетримка у волонтера, Одеса, Таїрова»')"
             :hint="__('для карток волонтерів показується на сайті')" />

@foreach (['story' => __('Коротка історія'), 'behavior' => __('Особливості поведінки'), 'adoption_terms' => __('Умови прилаштування')] as $field => $label)
    <x-bilingual :label="$label" model="form.{{ $field }}" type="textarea" :rows="4" class="pt-2" />
@endforeach
