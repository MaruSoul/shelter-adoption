<x-mail::message>
# Дякуємо, {{ $application->name }}!

Ми отримали вашу анкету на **{{ $application->animal->name }}**. Адміністратор притулку звʼяжеться з вами найближчим часом: {{ $application->contact_channels->map->label()->join(', ') }}.

Щоб стежити за статусом анкети, створіть акаунт із цією ж адресою e-mail – після підтвердження адреси анкета зʼявиться у вашому кабінеті.

<x-mail::button :url="route('register')">
Створити акаунт
</x-mail::button>

З теплом,
{{ \App\Models\ShelterSetting::current()->name }}
</x-mail::message>
