<x-mail::message>
# {{ __('Дякуємо, :name!', ['name' => $application->name]) }}

{{ __('Ми отримали вашу анкету на :animal. Адміністратор притулку звʼяжеться з вами найближчим часом: :channels.', ['animal' => $application->animal->display_name, 'channels' => $application->contact_channels->map->label()->join(', ')]) }}

@if ($application->user_id)
{{-- Пошта належить підтвердженому акаунту – анкета вже в кабінеті --}}
{{ __('Анкета вже у вашому кабінеті: там видно її статус та історію змін.') }}

<x-mail::button :url="route('cabinet')">
{{ __('Відкрити кабінет') }}
</x-mail::button>
@else
{{ __('Щоб стежити за статусом анкети, створіть акаунт із цією ж адресою e-mail – після підтвердження адреси анкета зʼявиться у вашому кабінеті.') }}

<x-mail::button :url="route('register')">
{{ __('Створити акаунт') }}
</x-mail::button>
@endif

{{ __('З теплом,') }}<br>
{{ \App\Models\ShelterSetting::current()->tr('name') }}
</x-mail::message>
