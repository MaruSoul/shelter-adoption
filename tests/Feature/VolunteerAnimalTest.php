<?php

use App\Enums\AnimalSource;
use App\Enums\AnimalStatus;
use App\Livewire\Admin\AnimalEditor;
use App\Livewire\Admin\ModerationQueue;
use App\Livewire\Cabinet\AnimalForm;
use App\Models\Animal;
use App\Models\Locality;
use App\Models\Region;
use App\Models\Species;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function animalFields($component)
{
    $locality = Locality::firstWhere('name', 'Одеса');

    return $component
        ->set('form.name', 'Знайда')
        ->set('form.species_id', (string) Species::firstWhere('slug', 'dog')->id)
        ->set('form.sex', 'female')
        ->set('form.birth_date', now()->subMonths(8)->toDateString())
        ->set('form.region_id', (string) $locality->region_id)
        ->set('form.locality_id', (string) $locality->id)
        ->set('form.current_location', 'Перетримка, Таїрова');
}

it('волонтер розміщує тварину, і вона йде на модерацію (FR-11)', function () {
    Storage::fake('public');
    $me = volunteer();

    animalFields(Livewire::actingAs($me)->test(AnimalForm::class))
        ->set('uploads', [UploadedFile::fake()->image('dog.jpg', 1200, 900)])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('cabinet'));

    $animal = Animal::sole();
    expect($animal->status)->toBe(AnimalStatus::Moderation)
        ->and($animal->source)->toBe(AnimalSource::Volunteer)
        ->and($animal->created_by)->toBe($me->id)
        ->and($animal->photos)->toHaveCount(1);
    Storage::disk('public')->assertExists($animal->photos->first()->path);
});

it('вимагає хоча б одне фото і відхиляє файли неправильного типу', function () {
    Storage::fake('public');

    animalFields(Livewire::actingAs(volunteer())->test(AnimalForm::class))
        ->call('save')
        ->assertHasErrors('uploads');

    Livewire::actingAs(volunteer())->test(AnimalForm::class)
        ->set('uploads', [UploadedFile::fake()->create('virus.pdf', 100, 'application/pdf')])
        ->assertHasErrors('uploads.0');

    expect(Animal::count())->toBe(0);
});

it('адміністратор схвалює картку, і вона зʼявляється в каталозі (FR-12)', function () {
    $animal = Animal::factory()->fromVolunteer(volunteer())->create();

    Livewire::actingAs(admin())->test(ModerationQueue::class)->call('approve', $animal->id);

    expect($animal->fresh()->status)->toBe(AnimalStatus::Available);
    $this->get(route('animals.show', $animal))->assertOk()->assertSee('Розміщено волонтером');
});

it('адміністратор виправляє картку з черги модерації без зміни статусу й повертається до черги', function () {
    $animal = Animal::factory()->fromVolunteer(volunteer())->create(['name' => 'Мурчик']);

    $this->actingAs(admin())->get(route('admin.moderation'))->assertSee(route('admin.animals.edit', $animal), false);

    Livewire::actingAs(admin())->test(AnimalEditor::class, ['animal' => $animal])
        ->set('form.name', 'Мурчик Великий')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.moderation'));

    expect($animal->fresh())->name->toBe('Мурчик Великий')->status->toBe(AnimalStatus::Moderation);
});

it('відхилення вимагає причину, яку бачить волонтер', function () {
    $author = volunteer();
    $animal = Animal::factory()->fromVolunteer($author)->create();

    Livewire::actingAs(admin())->test(ModerationQueue::class)
        ->call('reject', $animal->id)->assertHasErrors("reasons.{$animal->id}")
        ->set("reasons.{$animal->id}", 'Потрібне чітке фото')
        ->call('reject', $animal->id)->assertHasNoErrors();

    expect($animal->fresh())->status->toBe(AnimalStatus::Rejected)->moderation_note->toBe('Потрібне чітке фото');
    $this->actingAs($author)->get(route('cabinet', ['tab' => 'animals']))->assertOk();
});

it('редагування схваленої картки повертає її на модерацію', function () {
    Storage::fake('public');
    $author = volunteer();
    $animal = Animal::factory()->fromVolunteer($author, AnimalStatus::Available)->create();

    Livewire::actingAs($author)->test(AnimalForm::class, ['animal' => $animal])
        ->set('form.name', 'Нове імʼя')
        ->call('save')
        ->assertHasNoErrors();

    expect($animal->fresh())->status->toBe(AnimalStatus::Moderation)->name->toBe('Нове імʼя');
});

it('у формі є населені пункти будь-якої області, відсортовані за українським алфавітом', function () {
    $lviv = Region::firstWhere('name', 'Львівська');

    $names = Locality::where('region_id', $lviv->id)->alphabetical()->pluck('name');

    expect($names)->toContain('Львів', 'Дрогобич', 'Трускавець')
        ->and($names->count())->toBeGreaterThan(40)
        ->and(Region::alphabetical()->value('name'))->toBe('Автономна Республіка Крим');
});

it('обрані фото додаються до попередніх, показуються превʼю і їх можна прибрати до збереження', function () {
    Storage::fake('public');

    $form = Livewire::actingAs(volunteer())->test(AnimalForm::class)
        ->set('newUploads', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')])
        ->set('newUploads', [UploadedFile::fake()->image('c.jpg')])
        ->assertCount('uploads', 3)
        ->assertSee('Прибрати')
        ->call('discardUpload', 1)
        ->assertCount('uploads', 2);

    $form->set('newUploads', array_map(fn ($i) => UploadedFile::fake()->image("x$i.jpg"), range(1, 4)))
        ->assertHasErrors('newUploads')
        ->assertCount('uploads', 2);
});
