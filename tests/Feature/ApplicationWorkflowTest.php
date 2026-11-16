<?php

use App\Enums\AnimalStatus;
use App\Enums\ApplicationStatus;
use App\Exceptions\AnimalAlreadyAdoptedException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Livewire\Admin\ApplicationIndex;
use App\Livewire\Admin\ApplicationShow;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Services\ApplicationWorkflow;
use Illuminate\Database\UniqueConstraintViolationException;
use Livewire\Livewire;

it('переводить анкету за матрицею і пише історію (FR-07)', function () {
    $admin = admin();
    $application = AdoptionApplication::factory()->create();

    app(ApplicationWorkflow::class)->transition($application, ApplicationStatus::InProgress, $admin, 'Зателефонували');

    expect($application->fresh()->status)->toBe(ApplicationStatus::InProgress)
        ->and($application->statusChanges()->latest('id')->first())
        ->to_status->toBe(ApplicationStatus::InProgress)
        ->user_id->toBe($admin->id)
        ->note->toBe('Зателефонували');
});

it('відхиляє недозволений перехід і нічого не змінює', function () {
    $application = AdoptionApplication::factory()->create();

    expect(fn () => app(ApplicationWorkflow::class)->transition($application, ApplicationStatus::MeetingScheduled, admin()))
        ->toThrow(InvalidStatusTransitionException::class);

    expect($application->fresh()->status)->toBe(ApplicationStatus::New);
});

it('прилаштування закриває інші активні анкети й змінює статус тварини (FR-08)', function () {
    $animal = Animal::factory()->create();
    $winner = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::MeetingScheduled)->create();
    $other = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::InProgress)->create();
    $declined = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::Declined)->create();

    app(ApplicationWorkflow::class)->adopt($winner, admin());

    expect($winner->fresh()->status)->toBe(ApplicationStatus::Adopted)
        ->and($other->fresh()->status)->toBe(ApplicationStatus::Closed)
        ->and($declined->fresh()->status)->toBe(ApplicationStatus::Declined)
        ->and($animal->fresh()->status)->toBe(AnimalStatus::Adopted)
        ->and($animal->fresh()->adopted_at)->not->toBeNull();
});

it('не дозволяє прилаштувати тварину вдруге', function () {
    $animal = Animal::factory()->create();
    $first = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::InProgress)->create();
    $second = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::InProgress)->create(['email' => 'second@example.com']);

    app(ApplicationWorkflow::class)->adopt($first, admin());

    // Друга анкета вже автоматично закрита, тож перехід неможливий
    expect(fn () => app(ApplicationWorkflow::class)->adopt($second->fresh(), admin()))
        ->toThrow(InvalidStatusTransitionException::class);
});

it('СУБД сама не допускає двох анкет «Прилаштовано» на одну тварину (NFR-02)', function () {
    $animal = Animal::factory()->create();
    AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::Adopted)->create();
    $second = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::InProgress)->create(['email' => 'race@example.com']);

    // Імітація гонки: оминаємо перевірки застосунку і пишемо напряму в таблицю
    expect(fn () => AdoptionApplication::whereKey($second->id)->update(['status' => 'adopted']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('повідомляє про гонку зрозумілою помилкою', function () {
    $animal = Animal::factory()->create();
    $application = AdoptionApplication::factory()->for($animal)->status(ApplicationStatus::InProgress)->create();
    $animal->forceFill(['status' => AnimalStatus::Adopted])->save();

    expect(fn () => app(ApplicationWorkflow::class)->adopt($application, admin()))
        ->toThrow(AnimalAlreadyAdoptedException::class);
});

it('вимагає причину відмови в панелі адміністратора', function () {
    $application = AdoptionApplication::factory()->create();

    Livewire::actingAs(admin())
        ->test(ApplicationShow::class, ['application' => $application])
        ->call('changeStatus', 'declined')
        ->assertHasErrors('transitionNote')
        ->set('transitionNote', 'Немає умов для собаки')
        ->call('changeStatus', 'declined')
        ->assertHasNoErrors();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Declined);
});

it('адміністратор бачить усі анкети однієї людини і може відкрити кожну', function () {
    $a1 = AdoptionApplication::factory()->create(['email' => 'andrii@example.com', 'name' => 'Андрій']);
    $a2 = AdoptionApplication::factory()->create(['email' => 'andrii@example.com', 'name' => 'Андрій']);
    $other = AdoptionApplication::factory()->create(['email' => 'olena@example.com', 'name' => 'Олена']);

    Livewire::actingAs(admin())->test(ApplicationIndex::class)
        ->assertSee('Відкрити')
        ->assertSee('усього анкет: 2')
        ->set('email', 'andrii@example.com')
        ->assertSee(route('admin.applications.show', $a1))
        ->assertSee(route('admin.applications.show', $a2))
        ->assertDontSee(route('admin.applications.show', $other));

    Livewire::actingAs(admin())->test(ApplicationShow::class, ['application' => $a1])
        ->assertSee('Інші анкети цієї людини')
        ->assertSee($a2->animal->name);
});
