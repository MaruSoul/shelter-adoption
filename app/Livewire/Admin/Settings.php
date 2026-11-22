<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\TransliteratesFields;
use App\Models\ShelterSetting;
use Livewire\Component;

/** Назва, контакти й адреса для сповіщень редагуються без зміни коду (FR-16). */
class Settings extends Component
{
    use TransliteratesFields;

    public ?string $flash = null;

    public array $form = [];

    public function mount(): void
    {
        $settings = ShelterSetting::query()->first() ?? new ShelterSetting;
        $this->form = collect($settings->getFillable())->mapWithKeys(fn ($f) => [$f => (string) $settings->{$f}])->all();
    }

    public function save(): void
    {
        $data = $this->validate([
            'form.name' => ['required', 'string', 'max:100'],
            'form.tagline' => ['nullable', 'string', 'max:160'],
            'form.address' => ['nullable', 'string', 'max:255'],
            'form.name_en' => ['nullable', 'string', 'max:100'],
            'form.tagline_en' => ['nullable', 'string', 'max:160'],
            'form.address_en' => ['nullable', 'string', 'max:255'],
            'form.phone' => ['nullable', 'string', 'max:30'],
            'form.email' => ['nullable', 'email', 'max:150'],
            'form.notification_email' => ['required', 'email', 'max:150'],
            'form.instagram_url' => ['nullable', 'url:https', 'max:255'],
            'form.facebook_url' => ['nullable', 'url:https', 'max:255'],
            'form.telegram_url' => ['nullable', 'url:https', 'max:255'],
            'form.tiktok_url' => ['nullable', 'url:https', 'max:255'],
            'form.youtube_url' => ['nullable', 'url:https', 'max:255'],
            'form.default_adoption_terms' => ['nullable', 'string', 'max:3000'],
            'form.default_adoption_terms_en' => ['nullable', 'string', 'max:3000'],
        ], [], [
            'form.name' => 'назва', 'form.notification_email' => 'e-mail для сповіщень', 'form.email' => 'e-mail',
            'form.name_en' => 'назва англійською', 'form.tagline_en' => 'підзаголовок англійською', 'form.address_en' => 'адреса англійською',
            'form.default_adoption_terms' => 'типові умови прилаштування',
        ])['form'];

        $settings = ShelterSetting::query()->first() ?? new ShelterSetting;
        $settings->fill(array_map(fn ($v) => $v === '' ? null : $v, $data))->save();

        $this->flash = 'Налаштування збережено.';
    }

    protected function canTransliterate(string $field): bool
    {
        return in_array($field, ['form.name', 'form.address'], true);
    }

    public function render()
    {
        return view('livewire.admin.settings')->title('Налаштування');
    }
}
