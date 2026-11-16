<?php

use App\Enums\AnimalStatus;
use App\Enums\ApplicationStatus;
use App\Livewire\Cabinet\Dashboard;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

it('волонтер повністю видаляє власну картку без анкет разом із фото', function () {
    Storage::fake('public');
    $me = volunteer();
    $animal = Animal::factory()->fromVolunteer($me, AnimalStatus::Available)->create(['name' => 'Алекс']);
    Storage::disk('public')->put("animals/{$animal->id}/1.jpg", 'x');
    Storage::disk('public')->put("animals/{$animal->id}/1_thumb.jpg", 'x');
    $animal->photos()->create(['path' => "animals/{$animal->id}/1.jpg", 'thumb_path' => "animals/{$animal->id}/1_thumb.jpg", 'sort_order' => 0]);

    Livewire::actingAs($me)->test(Dashboard::class)
        ->call('deleteAnimal', $animal->id)
        ->assertSet('flash', 'Картку «Алекс» видалено.')
        ->assertDontSee('Редагувати');

    expect(Animal::withTrashed()->find($animal->id))->toBeNull();
    Storage::disk('public')->assertMissing("animals/{$animal->id}/1.jpg");
    Storage::disk('public')->assertMissing("animals/{$animal->id}/1_thumb.jpg");
});

it('якщо анкети вже були, картка ховається, а активні анкети закриваються', function () {
    $me = volunteer();
    $animal = Animal::factory()->fromVolunteer($me, AnimalStatus::Available)->create();
    $active = AdoptionApplication::factory()->create(['animal_id' => $animal->id]);
    $declined = AdoptionApplication::factory()->status(ApplicationStatus::Declined)->create(['animal_id' => $animal->id]);

    Livewire::actingAs($me)->test(Dashboard::class)->call('deleteAnimal', $animal->id);

    expect(Animal::find($animal->id))->toBeNull()
        ->and(Animal::withTrashed()->find($animal->id)->trashed())->toBeTrue()
        ->and($active->fresh()->status)->toBe(ApplicationStatus::Closed)
        ->and($active->statusChanges()->latest('id')->first()->note)->toContain('прибрав картку')
        ->and($declined->fresh()->status)->toBe(ApplicationStatus::Declined);

    // Картки більше немає на сайті
    $this->get(route('animals.show', $animal))->assertNotFound();

    // Заявник у кабінеті бачить закриту анкету без помилок
    $applicant = volunteer(['email' => $active->email, 'email_verified_at' => now()]);
    Livewire::actingAs($applicant)->test(Dashboard::class)->assertOk();
});

it('чужу картку видалити не можна', function () {
    $animal = Animal::factory()->fromVolunteer(volunteer(), AnimalStatus::Available)->create();

    Livewire::actingAs(volunteer())->test(Dashboard::class)
        ->call('deleteAnimal', $animal->id)
        ->assertNotFound();

    expect(Animal::find($animal->id))->not->toBeNull();
});

it('прилаштовану тварину видалити не можна', function () {
    $me = volunteer();
    $animal = Animal::factory()->fromVolunteer($me, AnimalStatus::Adopted)->create();

    Livewire::actingAs($me)->test(Dashboard::class)
        ->assertDontSee('Видалити картку')
        ->call('deleteAnimal', $animal->id)
        ->assertForbidden();

    expect(Animal::find($animal->id))->not->toBeNull();
});
