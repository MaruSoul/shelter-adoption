<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

/** Сторінки помилок показуються мовою сайту, а не стандартні англійські сторінки Laravel. */
it('показує сторінку 404 українською й англійською', function () {
    $this->get('/nemaie-takoi-storinky')->assertNotFound()->assertSee('Такої сторінки немає');
    $this->get('/en/no-such-page')->assertNotFound()->assertSee('There is no such page')->assertSee('lang="en"', false);
});

it('показує власну сторінку 403 волонтеру в панелі адміністратора', function () {
    $this->actingAs(volunteer())->get(route('admin.dashboard'))->assertForbidden()->assertSee('Немає доступу');
});

it('показує власну сторінку 500 без технічних подробиць', function () {
    config(['app.debug' => false]);
    Route::middleware('web')->get('/_test-error', fn () => throw new RuntimeException('секретна деталь'));

    $this->get('/_test-error')->assertStatus(500)->assertSee('Щось пішло не так')->assertDontSee('секретна деталь');
    $this->withUnencryptedCookie(SetLocale::COOKIE, 'en')->get('/_test-error')->assertSee('Something went wrong');
});

it('показує сторінки 419, 429 і 503', function (int $code, string $text) {
    Route::middleware('web')->get('/_test-'.$code, fn () => abort($code));

    $this->get('/_test-'.$code)->assertStatus($code)->assertSee($text);
})->with([
    [419, 'Сторінка застаріла'],
    [429, 'Забагато спроб'],
    [503, 'Сайт оновлюється'],
]);
