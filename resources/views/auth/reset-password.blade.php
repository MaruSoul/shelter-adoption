<x-auth-card title="Новий пароль">
    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <label class="label">Електронна пошта
            <input type="email" name="email" value="{{ old('email', $request->email) }}" required class="field font-normal">
            @error('email')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Новий пароль
            <input type="password" name="password" required autocomplete="new-password" class="field font-normal">
            @error('password')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Повторіть пароль
            <input type="password" name="password_confirmation" required autocomplete="new-password" class="field font-normal">
        </label>
        <button type="submit" class="btn-primary min-h-12">Зберегти пароль</button>
    </form>
</x-auth-card>
