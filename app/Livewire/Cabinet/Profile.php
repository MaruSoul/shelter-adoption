<?php

namespace App\Livewire\Cabinet;

use App\Models\Region;
use App\Support\Phone;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Профіль користувача: імʼя, телефон і область. Ці дані підставляються в анкету «Взяти додому».
 * Пошту тут не змінюємо: від неї залежить привʼязка анкет, а зміна потребувала б повторного підтвердження.
 */
class Profile extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $region_id = '';

    public ?string $flash = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->phone = $user->phone ?? '+380';
        $this->region_id = (string) ($user->region_id ?? '');
    }

    public function updatedPhone(): void
    {
        $this->phone = Phone::normalize($this->phone);
    }

    public function save(): void
    {
        $this->phone = $this->phone === '+380' || $this->phone === '+' ? '' : $this->phone;

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['nullable', 'regex:'.Phone::PATTERN],
            'region_id' => ['nullable', 'integer', Rule::exists('regions', 'id')],
        ], [
            'phone.regex' => 'Вкажіть номер у форматі +380XXXXXXXXX.',
        ], [
            'name' => 'імʼя', 'phone' => 'телефон', 'region_id' => 'область',
        ]);

        auth()->user()->forceFill([
            'name' => trim($data['name']),
            'phone' => $data['phone'] ?: null,
            'region_id' => $data['region_id'] ?: null,
        ])->save();

        $this->phone = $this->phone ?: '+380';
        $this->flash = 'Профіль збережено. Ці дані підставлятимуться в анкети.';
    }

    public function render()
    {
        return view('livewire.cabinet.profile', [
            'regions' => Region::alphabetical()->get(),
        ])->title('Мій профіль');
    }
}
