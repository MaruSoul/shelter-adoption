<x-auth-card title="{{ __('Відновлення пароля') }}" subtitle="{{ __('Вкажіть пошту – ми надішлемо посилання для встановлення нового пароля.') }}">
    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
        @csrf
        <label class="label">{{ __('Електронна пошта') }}
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="field font-normal">
            @error('email')<span class="error">{{ $message }}</span>@enderror
        </label>
        <button type="submit" class="btn-primary min-h-12">{{ __('Надіслати посилання') }}</button>
    </form>
    @if (session('status'))
        <p class="text-sm text-muted">{{ __('Не бачите листа? Зачекайте кілька хвилин і перевірте папку «Спам» або «Промоакції». Якщо лист там, позначте його як «Не спам» – тоді наступні листи від притулку приходитимуть у «Вхідні».') }}</p>
    @endif
</x-auth-card>
