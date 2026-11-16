<x-auth-card title="Створити акаунт" subtitle="Якщо ви вже подавали анкету, зареєструйтеся з тією ж поштою – після підтвердження адреси анкета зʼявиться в кабінеті.">
    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-4">
        @csrf
        <label class="label">Імʼя
            <input type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="field font-normal">
            @error('name')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Електронна пошта
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="field font-normal">
            @error('email')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Пароль <span class="font-normal text-sm text-muted">щонайменше 8 символів</span>
            <input type="password" name="password" required autocomplete="new-password" class="field font-normal">
            @error('password')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="label">Повторіть пароль
            <input type="password" name="password_confirmation" required autocomplete="new-password" class="field font-normal">
        </label>
        <button type="submit" class="btn-primary min-h-12">Зареєструватися</button>
    </form>
    <p class="text-sm">Вже маєте акаунт? <a href="{{ route('login') }}" class="text-primary font-semibold hover:underline">Увійти</a></p>
</x-auth-card>
