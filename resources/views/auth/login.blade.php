<x-auth-card title="Вхід" subtitle="Увійдіть, щоб бачити свої анкети або розмістити знайдену тварину.">
    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf
        <label class="label">Електронна пошта
            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field font-normal">
            @error('email')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Пароль
            <input type="password" name="password" required autocomplete="current-password" class="field font-normal">
            @error('password')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="flex items-center gap-2.5"><input type="checkbox" name="remember" class="size-4 accent-primary"> Запамʼятати мене</label>
        <button type="submit" class="btn-primary min-h-12">Увійти</button>
    </form>
    <div class="flex flex-wrap justify-between gap-2 text-sm">
        <a href="{{ route('password.request') }}" class="text-primary font-semibold hover:underline">Забули пароль?</a>
        <a href="{{ route('register') }}" class="text-primary font-semibold hover:underline">Створити акаунт</a>
    </div>
</x-auth-card>
