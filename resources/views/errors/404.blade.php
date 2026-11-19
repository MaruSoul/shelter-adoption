@include('errors.locale')
<x-error-page code="404" :title="__('Такої сторінки немає')"
              :text="__('Можливо, тварина вже знайшла дім і її картку прибрали, або посилання застаріло. Лапа пропонує пошукати інших друзів.')">
    <a href="{{ route('animals.index') }}" class="btn-primary">{{ __('Знайти друга') }}</a>
    <a href="{{ url(app()->getLocale() === 'en' ? '/en' : '/') }}" class="btn-outline">{{ __('На головну') }}</a>
</x-error-page>
