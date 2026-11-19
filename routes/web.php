<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SetLocale;
use App\Livewire\Admin;
use App\Livewire\AdoptionForm;
use App\Livewire\AnimalCatalog;
use App\Livewire\Cabinet;
use Illuminate\Support\Facades\Route;

/* ---------- Публічна частина ---------- */

Route::get('/', HomeController::class)->name('home');

Route::get('/tvaryny', AnimalCatalog::class)->name('animals.index');
Route::get('/tvaryny/{animal}', [AnimalController::class, 'show'])->name('animals.show');
Route::get('/tvaryny/{animal}/vzyaty-dodomu', AdoptionForm::class)->name('animals.adopt');

Route::get('/obrani', [FavoriteController::class, 'index'])->name('favorites.index');
Route::get('/obrani/kartky', [FavoriteController::class, 'cards'])->name('favorites.cards')->middleware('throttle:60,1');
Route::middleware('auth')->group(function () {
    Route::post('/obrani/synkhronizuvaty', [FavoriteController::class, 'sync'])->name('favorites.sync');
    Route::post('/obrani/{animalId}', [FavoriteController::class, 'toggle'])->whereNumber('animalId')->name('favorites.toggle')->middleware('throttle:120,1');
});

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('blog.show');

Route::view('/polityka-konfidentsiinosti', 'pages.privacy')->name('privacy');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
// Перемикач мови: запамʼятовує вибір на рік і повертає на ту саму сторінку
Route::get('/mova/{locale}', function (string $locale) {
    abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);
    $back = url()->previous();
    if (! str_starts_with($back, url('/')) || str_contains($back, '/mova/')) {
        $back = route('home');
    }

    return redirect($back)->withCookie(cookie()->forever(SetLocale::COOKIE, $locale, httpOnly: false));
})->name('locale.switch');
// robots.txt формується застосунком, бо адреса sitemap має бути абсолютною (з доменом сайту)
Route::get('/robots.txt', fn () => response(implode("\n", [
    'User-agent: *',
    'Disallow: /admin',
    'Disallow: /kabinet',
    '',
    'Sitemap: '.route('sitemap'),
])."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']))->name('robots');

/* ---------- Кабінет користувача (волонтера) ---------- */

Route::middleware(['auth', 'verified'])->prefix('kabinet')->name('cabinet')->group(function () {
    Route::get('/', Cabinet\Dashboard::class);
    Route::get('/profil', Cabinet\Profile::class)->name('.profile');
    Route::get('/tvaryny/nova', Cabinet\AnimalForm::class)->name('.animals.create');
    Route::get('/tvaryny/{animal}/redaguvaty', Cabinet\AnimalForm::class)->name('.animals.edit');
});

/* ---------- Панель адміністратора притулку ---------- */

Route::middleware(['auth', 'verified', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Admin\Dashboard::class)->name('dashboard');

    Route::get('/ankety', Admin\ApplicationIndex::class)->name('applications.index');
    Route::get('/ankety/{application}', Admin\ApplicationShow::class)->name('applications.show');

    Route::get('/tvaryny', Admin\AnimalIndex::class)->name('animals.index');
    Route::get('/tvaryny/nova', Admin\AnimalEditor::class)->name('animals.create');
    Route::get('/tvaryny/{animal}', Admin\AnimalEditor::class)->name('animals.edit');
    Route::get('/moderatsiia', Admin\ModerationQueue::class)->name('moderation');

    Route::get('/statti', Admin\PostIndex::class)->name('posts.index');
    Route::get('/statti/nova', Admin\PostEditor::class)->name('posts.create');
    Route::get('/statti/{post}', Admin\PostEditor::class)->name('posts.edit');

    Route::get('/dovidnyky', Admin\Dictionaries::class)->name('dictionaries');
    Route::get('/nalashtuvannia', Admin\Settings::class)->name('settings');
});
