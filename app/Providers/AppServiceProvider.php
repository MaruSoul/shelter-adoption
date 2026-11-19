<?php

namespace App\Providers;

use App\Models\User;
use App\Routing\LocalizedUrlGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Генератор адрес, що сам підставляє англійські адреси (/en/...) на англійській версії сайту
        $this->app->extend('url', function (UrlGenerator $url, $app) {
            $localized = new LocalizedUrlGenerator($app['routes'], $url->getRequest(), $app['config']['app.asset_url']);
            $localized->setSessionResolver(fn () => $app['session'] ?? null);
            $localized->setKeyResolver(function () use ($app) {
                $config = $app->make('config');

                return [$config->get('app.key'), ...($config->get('app.previous_keys') ?? [])];
            });

            return $localized;
        });
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
