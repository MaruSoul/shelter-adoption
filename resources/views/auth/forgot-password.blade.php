<x-auth-card title="{{ __('Відновлення пароля') }}" subtitle="{{ __('Вкажіть пошту – ми надішлемо посилання для встановлення нового пароля.') }}">
    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
        @csrf
        <label class="label">{{ __('Електронна пошта') }}
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="field font-normal">
            @error('email')<span class="error">{{ $message }}</span>@enderror
        </label>
        <button type="submit" class="btn-primary min-h-12">{{ __('Надіслати посилання') }}</button>
    </form>
</x-auth-card>
