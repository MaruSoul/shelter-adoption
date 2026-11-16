<a href="{{ route('favorites.index') }}" class="btn-ghost relative" aria-label="Обрані">
    <svg viewBox="0 0 24 24" class="size-5 text-danger" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 8 3.4 4.5 6.9 4.5c2 0 3.6 1.1 5.1 3 1.5-1.9 3.1-3 5.1-3 3.5 0 5.5 3.5 4.2 6.8-1.8 4.6-9.3 9.2-9.3 9.2z" fill="currentColor"/></svg>
    <span>Обрані</span>
    <span x-data x-cloak x-show="$store.favorites.ids.length" x-text="$store.favorites.ids.length"
          class="min-w-5 h-5 px-1.5 rounded-full bg-sun text-ink text-xs font-bold flex items-center justify-center"></span>
</a>
@auth
    @if (auth()->user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="btn-ghost">Панель притулку</a>
    @endif
    <a href="{{ route('cabinet') }}" class="btn-outline">Кабінет</a>
@else
    <a href="{{ route('login') }}" class="btn-outline">Увійти</a>
@endauth
