<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // У розробці ловимо звертання до незавантажених звʼязків (N+1) і помилки mass assignment
        Model::shouldBeStrict(! $this->app->isProduction());

        Paginator::defaultView('components.pagination-links');

        // Доступ до адміністративної панелі
        Gate::define('admin', fn (User $user) => $user->isAdmin());
    }
}
