<?php

namespace App\Livewire;

use App\Enums\ContactChannel;
use App\Exceptions\DuplicateApplicationException;
use App\Models\Animal;
use App\Models\Region;
use App\Services\SubmitAdoptionApplication;
use App\Support\Phone;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Анкета «Взяти додому» (FR-03, FR-04). Реєстрація не потрібна.
 */
class AdoptionForm extends Component
{
    /** Скільки анкет можна надіслати з однієї IP-адреси за годину. */
    /** Типове обмеження; фактичне значення – config('shelter.adoption_max_per_hour'). */
    public const MAX_PER_HOUR = 5;

    #[Locked]
    public Animal $animal;

    public string $name = '';

    public string $email = '';

    public string $phone = '+380';

    public string $region_id = '';

    public string $social_url = '';

    public array $contact_channels = ['phone'];

    public string $comment = '';

    public bool $consent = false;

    /** Для користувача, який увійшов: зберегти телефон і область у профілі для наступних анкет. */
    public bool $rememberContacts = false;

    /** Поле-пастка для ботів: людина його не бачить і не заповнює. */
    public string $website = '';

    public bool $submitted = false;

    /** Анкету привʼязано до наявного акаунта з цією поштою – її вже видно в кабінеті після входу. */
    public bool $inCabinet = false;

    public function mount(Animal $animal): void
    {
        abort_unless($animal->isPublic(), 404);

        $this->animal = $animal;

        if (! $animal->acceptsApplications()) {
            session()->flash('status', $animal->foundHomePhrase().'. '.__('Подивіться інших тварин, які чекають на вас.'));
            $this->redirectRoute('animals.show', $animal);

            return;
        }

        if ($user = auth()->user()) {
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '+380';
            $this->region_id = (string) ($user->region_id ?? '');
            // Якщо в профілі ще порожньо – пропонуємо запамʼятати; якщо дані вже є, не перезаписуємо без згоди
            $this->rememberContacts = ! $user->phone || ! $user->region_id;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150'],
            'phone' => ['required', 'regex:/^\+380\d{9}$/'],
            'region_id' => ['required', 'integer', Rule::exists('regions', 'id')],
            'social_url' => ['nullable', 'url:http,https', 'max:255'],
            'contact_channels' => ['required', 'array', 'min:1'],
            'contact_channels.*' => [Rule::enum(ContactChannel::class)],
            'comment' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
        ];
    }

    protected function messages(): array
    {
        return [
            'phone.regex' => __('Вкажіть номер у форматі +380XXXXXXXXX.'),
            'contact_channels.required' => __('Оберіть хоча б один спосіб звʼязку.'),
            'contact_channels.min' => __('Оберіть хоча б один спосіб звʼязку.'),
            'consent.accepted' => __('Без згоди на обробку даних ми не зможемо звʼязатися з вами.'),
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => __('імʼя'), 'email' => __('електронна пошта'), 'phone' => __('телефон'),
            'region_id' => __('область'), 'social_url' => __('посилання на соцмережі'), 'comment' => __('коментар'),
        ];
    }

    public function updatedPhone(): void
    {
        $this->phone = Phone::normalize($this->phone);
    }

    public function submit(SubmitAdoptionApplication $service): void
    {
        // Бот заповнив пастку – вдаємо успіх, але нічого не зберігаємо
        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $data = $this->validate();

        $this->animal->refresh();
        if (! $this->animal->acceptsApplications()) {
            $this->addError('form', __('На жаль, :phrase. Подивіться інших тварин у каталозі.', ['phrase' => $this->animal->foundHomePhrase()]));

            return;
        }

        $key = 'adoption-form:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, (int) config('shelter.adoption_max_per_hour', self::MAX_PER_HOUR))) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);
            $this->addError('form', __('Забагато анкет за короткий час. Спробуйте ще раз через :minutes хв.', ['minutes' => $minutes]));

            return;
        }

        try {
            $application = $service->handle($this->animal, [
                'name' => trim($data['name']),
                'email' => $data['email'],
                'phone' => $data['phone'],
                'region_id' => (int) $data['region_id'],
                'social_url' => $data['social_url'] ?: null,
                'contact_channels' => array_values(array_unique($data['contact_channels'])),
                'comment' => trim($data['comment']) ?: null,
            ], auth()->user(), request()->ip());
        } catch (DuplicateApplicationException) {
            $this->addError('form', __('Ви вже надіслали анкету на :name з цієї адреси – вона в роботі. Ми обовʼязково звʼяжемося з вами.', ['name' => $this->animal->display_name]));

            return;
        }

        RateLimiter::hit($key, 3600);

        if ($this->rememberContacts && ($user = auth()->user())) {
            $user->forceFill(['phone' => $data['phone'], 'region_id' => (int) $data['region_id']])->save();
        }

        $this->inCabinet = $application->user_id !== null;
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.adoption-form', [
            'regions' => Region::alphabetical()->get(),
            'channels' => ContactChannel::cases(),
        ])->title(__('Взяти додому :name', ['name' => $this->animal->display_name]));
    }
}
