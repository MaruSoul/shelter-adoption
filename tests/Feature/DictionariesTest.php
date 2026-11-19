<?php

use App\Livewire\Admin\Dictionaries;
use App\Livewire\Admin\Settings;
use App\Models\Animal;
use App\Models\Breed;
use App\Models\Color;
use App\Models\PostCategory;
use App\Models\ShelterSetting;
use App\Models\Species;
use Livewire\Livewire;

it('довідники доступні лише адміністратору', function () {
    $this->get(route('admin.dictionaries'))->assertRedirect(route('login'));
    $this->actingAs(volunteer())->get(route('admin.dictionaries'))->assertForbidden();
    $this->actingAs(admin())->get(route('admin.dictionaries'))->assertOk()->assertSee('Довідники');
});

it('адміністратор додає породу з англійською назвою, і вона показується англійською версією сайту', function () {
    $species = Species::firstOrFail();

    Livewire::actingAs(admin())->test(Dictionaries::class)
        ->set('tab', 'breeds')->set('speciesFilter', $species->id)
        ->set('new.name', 'Сфінкс')->set('new.name_en', 'Sphynx')
        ->call('add')->assertHasNoErrors();

    $breed = Breed::where('name', 'Сфінкс')->firstOrFail();
    expect($breed->species_id)->toBe($species->id)
        ->and($breed->label('name', 'en'))->toBe('Sphynx')
        ->and($breed->label('name', 'uk'))->toBe('Сфінкс');
});

it('не дає створити дублікат забарвлення', function () {
    Color::firstOrCreate(['name' => 'Руде']);

    Livewire::actingAs(admin())->test(Dictionaries::class)
        ->set('tab', 'colors')->set('new.name', 'Руде')
        ->call('add')->assertHasErrors(['new.name' => 'unique']);
});

it('перейменовує запис, який використовується, але не видаляє його', function () {
    $animal = Animal::factory()->create();
    $color = Color::create(['name' => 'Тестове сіре']);
    $animal->update(['color_id' => $color->id]);

    Livewire::actingAs(admin())->test(Dictionaries::class)
        ->set('tab', 'colors')
        ->call('startEdit', $color->id)->set('edit.name', 'Димчасте')->set('edit.name_en', 'Smoky')
        ->call('save')->assertHasNoErrors()
        ->call('delete', $color->id)->assertHasErrors('delete');

    expect($color->fresh())->name->toBe('Димчасте')->name_en->toBe('Smoky');
});

it('видаляє невикористаний запис', function () {
    $color = Color::create(['name' => 'Тестове тигрове']);

    Livewire::actingAs(admin())->test(Dictionaries::class)->set('tab', 'colors')->call('delete', $color->id)->assertHasNoErrors();

    expect(Color::find($color->id))->toBeNull();
});

it('новий вид і рубрика отримують постійну адресу', function () {
    Livewire::actingAs(admin())->test(Dictionaries::class)
        ->set('new.name', 'Кролик')->set('new.name_plural', 'Кролики')->set('new.name_en', 'Rabbit')->set('new.name_plural_en', 'Rabbits')
        ->call('add')->assertHasNoErrors()
        ->set('tab', 'categories')->set('new.name', 'Здоровʼя')
        ->call('add')->assertHasNoErrors();

    expect(Species::where('name', 'Кролик')->value('slug'))->toBe('rabbit')
        ->and(PostCategory::where('name', 'Здоровʼя')->value('slug'))->toBe('zdorovia');
});

it('показує блок «Як допомогти» і типові умови прилаштування з налаштувань', function () {
    $animal = Animal::factory()->create(['adoption_terms' => null]);

    $this->get(route('home'))->assertDontSee('Як ще допомогти притулку');

    Livewire::actingAs(admin())->test(Settings::class)
        ->set('form.name', 'Клуб хвостики')->set('form.notification_email', 'shelter@example.com')
        ->set('form.help_text', 'Потрібен корм для котят')
        ->set('form.default_adoption_terms', 'Знайомство в притулку й договір')
        ->call('save')->assertHasNoErrors();

    expect(ShelterSetting::current()->help_text)->toBe('Потрібен корм для котят');
    $this->get(route('home'))->assertSee('Як ще допомогти притулку')->assertSee('Потрібен корм для котят');
    $this->get(route('animals.show', $animal))->assertSee('Знайомство в притулку й договір');
});
