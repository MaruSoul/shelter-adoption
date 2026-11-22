<?php

use App\Enums\DonationType;
use App\Livewire\Admin\Donations;
use App\Models\DonationMethod;
use App\Models\ShelterSetting;
use Livewire\Livewire;

function donation(DonationType $type, array $details, array $attributes = []): DonationMethod
{
    return DonationMethod::create(['type' => $type, 'details' => $details, 'position' => DonationMethod::count()] + $attributes);
}

it('розділ «Допомога» доступний лише адміністратору', function () {
    $this->actingAs(volunteer())->get(route('admin.donations'))->assertForbidden();
    $this->actingAs(admin())->get(route('admin.donations'))->assertOk()->assertSee('Додати спосіб');
});

it('адміністратор додає банку Monobank, і вона зʼявляється вкладкою на сторінці «Як допомогти»', function () {
    Livewire::actingAs(admin())->test(Donations::class)
        ->call('create', 'monobank')
        ->set('details.url', 'https://send.monobank.ua/jar/4RWAU3uWen')
        ->set('form.title', 'На корм на зиму')
        ->call('save')->assertHasNoErrors();

    $this->get(route('help'))->assertOk()
        ->assertSee('На корм на зиму')
        ->assertSee('Поповнити банку')
        ->assertSee('href="https://send.monobank.ua/jar/4RWAU3uWen"', false);
});

it('перевіряє дані кожного способу', function (string $type, array $details, string $errorField) {
    Livewire::actingAs(admin())->test(Donations::class)
        ->call('create', $type)
        ->set('details', $details)
        ->call('save')
        ->assertHasErrors($errorField);
})->with([
    'банка не з Monobank' => ['monobank', ['url' => 'https://evil.example/jar/123456'], 'details.url'],
    'IBAN з помилкою' => ['requisites', ['recipient' => 'ФОП Іваненко', 'iban' => 'UA213223130000026007233566002', 'code' => '1234567890'], 'details.iban'],
    'код не з 8 чи 10 цифр' => ['requisites', ['recipient' => 'ФОП Іваненко', 'iban' => 'UA213223130000026007233566001', 'code' => '123'], 'details.code'],
    'PayPal не адреса й не посилання' => ['paypal', ['account' => 'просто текст'], 'details.account'],
    'посилання без https' => ['link', ['url' => 'http://example.com'], 'details.url'],
]);

it('для іншого посилання назва вкладки обовʼязкова', function () {
    Livewire::actingAs(admin())->test(Donations::class)
        ->call('create', 'link')->set('details.url', 'https://portmone.com.ua/x')
        ->call('save')->assertHasErrors('form.title');
});

it('зберігає IBAN без пробілів і показує реквізити з кнопками «Копіювати»', function () {
    Livewire::actingAs(admin())->test(Donations::class)
        ->call('create', 'requisites')
        ->set('details.recipient', 'ФОП Іваненко Іван')
        ->set('details.iban', 'ua21 3223 1300 0002 6007 2335 6600 1')
        ->set('details.code', '1234567890')
        ->call('save')->assertHasNoErrors();

    expect(DonationMethod::first()->detail('iban'))->toBe('UA213223130000026007233566001');
    $this->get(route('help'))
        ->assertSee('UA21 3223 1300 0002 6007 2335 6600 1')
        ->assertSee('Копіювати всі реквізити')
        ->assertSee('За реквізитами');
});

it('сховані способи не показуються, порядок вкладок задається в панелі', function () {
    $first = donation(DonationType::Monobank, ['url' => 'https://send.monobank.ua/jar/AAAAAAAA'], ['title' => 'Перша банка']);
    $second = donation(DonationType::Link, ['url' => 'https://portmone.com.ua/x'], ['title' => 'Portmone']);
    donation(DonationType::PayPal, ['account' => 'shelter@example.com'], ['is_visible' => false]);

    Livewire::actingAs(admin())->test(Donations::class)->call('move', $second->id, -1);

    $this->get(route('help'))
        ->assertSeeInOrder(['Portmone', 'Перша банка'])
        ->assertDontSee('shelter@example.com');
});

it('без способів допомоги вкладок немає, а англійська версія показує англійські назви', function () {
    $this->get(route('help'))->assertOk()->assertDontSee('role="tablist"', false);

    donation(DonationType::Requisites, ['recipient' => 'ФОП Іваненко', 'iban' => 'UA213223130000026007233566001', 'code' => '1234567890']);
    $this->get(enRoute('help'))->assertSee('Bank transfer')->assertSee('Copy all details');
});

it('посилання на звіти зʼявляється в підвалі й на сторінці «Як допомогти»', function () {
    $this->get(route('home'))->assertDontSee('Переглянути звіти');

    Livewire::actingAs(admin())->test(Donations::class)
        ->set('page.reports_url', 'https://drive.google.com/reports')
        ->set('page.help_text', 'Потрібен корм для котят')
        ->call('savePage')->assertHasNoErrors();

    expect(ShelterSetting::current()->reports_url)->toBe('https://drive.google.com/reports');
    $this->get(route('home'))->assertSee('href="https://drive.google.com/reports"', false)->assertSee('Потрібен корм для котят');
    $this->get(route('help'))->assertSee('Переглянути звіти')->assertSee('Потрібен корм для котят');
});
