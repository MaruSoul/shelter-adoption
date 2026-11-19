@include('errors.locale')
<x-error-page code="403" :title="__('Немає доступу')"
              :text="__('Ця сторінка доступна лише її автору або адміністратору притулку. Якщо це ваша анкета чи картка, увійдіть до кабінету.')">
    <a href="{{ route('login') }}" class="btn-primary">{{ __('Увійти') }}</a>
    <a href="{{ url(app()->getLocale() === 'en' ? '/en' : '/') }}" class="btn-outline">{{ __('На головну') }}</a>
</x-error-page>
