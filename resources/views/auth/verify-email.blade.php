<x-auth-card title="Підтвердіть пошту" subtitle="Ми надіслали лист із посиланням на {{ auth()->user()->email }}. Після підтвердження у кабінеті зʼявляться ваші анкети.">
    @if (session('status') === 'verification-link-sent')
        <p role="status" class="rounded-2xl bg-success-soft text-success px-4 py-3 font-medium">Новий лист надіслано.</p>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn-primary w-full min-h-12">Надіслати лист ще раз</button>
    </form>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn-ghost w-full">Вийти</button>
    </form>
</x-auth-card>
