<x-mail::message>
# {{ __('Дякуємо, :name!', ['name' => $application->name]) }}

{{ __('Ми отримали вашу анкету на :animal. Адміністратор притулку звʼяжеться з вами найближчим часом: :channels.', ['animal' => $application->animal->name, 'channels' => $application->contact_channels->map->label()->join(', ')]) }}

{{ __('Щоб стежити за статусом анкети, створіть акаунт із цією ж адресою e-mail – після підтвердження адреси анкета зʼявиться у вашому кабінеті.') }}

<x-mail::button :url="route('register')">
{{ __('Створити акаунт') }}
</x-mail::button>

{{ __('З теплом,') }}<br>
{{ \App\Models\ShelterSetting::current()->name }}
</x-mail::message>
