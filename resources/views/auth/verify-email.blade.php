<x-auth-card title="{{ __('Підтвердіть пошту') }}" subtitle="{{ __('Ми надіслали лист із посиланням на :email. Після підтвердження у кабінеті зʼявляться ваші анкети.', ['email' => auth()->user()->email]) }}">
    @if (session('status') === 'verification-link-sent')
        <p role="status" class="rounded-2xl bg-success-soft text-success px-4 py-3 font-medium">{{ __('Новий лист надіслано.') }}</p>
    @endif
    <p class="text-sm text-muted">{{ __('Не бачите листа? Зачекайте кілька хвилин і перевірте папку «Спам» або «Промоакції». Якщо лист там, позначте його як «Не спам» – тоді наступні листи від притулку приходитимуть у «Вхідні».') }}</p>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn-primary w-full min-h-12">{{ __('Надіслати лист ще раз') }}</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-ghost w-full">{{ __('Вийти') }}</button>
    </form>
</x-auth-card>
