@auth
    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Панель притулку</a>
    @endif
    <a href="{{ route('cabinet') }}" class="btn-outline">Кабінет</a>
@else
    <a href="{{ route('login') }}" class="btn-outline">Увійти</a>
@endauth
