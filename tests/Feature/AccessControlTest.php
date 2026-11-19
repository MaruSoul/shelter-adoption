<?php

use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\User;

it('пускає в панель притулку лише адміністратора (NFR-01)', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->actingAs(volunteer())->get(route('admin.dashboard'))->assertForbidden();
    $this->actingAs(volunteer())->get(route('admin.applications.index'))->assertForbidden();
    $this->actingAs(admin())->get(route('admin.dashboard'))->assertOk();
});

it('вимагає підтвердженої пошти для кабінету', function () {
    $this->actingAs(volunteer(['email_verified_at' => null]))->get(route('cabinet'))->assertRedirect(route('verification.notice'));
});

it('показує в кабінеті лише власні анкети (FR-10)', function () {
    $me = volunteer();
    AdoptionApplication::factory()->for(Animal::factory()->create(['name' => 'МояТварина']))->create(['user_id' => $me->id, 'email' => $me->email]);
    AdoptionApplication::factory()->for(Animal::factory()->create(['name' => 'ЧужаТварина']))->create(['user_id' => volunteer()->id]);

    $this->actingAs($me)->get(route('cabinet'))->assertOk()->assertSee('МояТварина')->assertDontSee('ЧужаТварина');
});

it('не дає волонтеру редагувати чужу картку', function () {
    $animal = Animal::factory()->fromVolunteer(volunteer())->create();

    $this->actingAs(volunteer())->get(route('cabinet.animals.edit', $animal))->assertForbidden();
});

it('не дає отримати роль адміністратора через реєстрацію', function () {
    $this->post(route('register'), [
        'name' => 'Хитрун', 'email' => 'h@example.com', 'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'admin',
    ])->assertRedirect();

    expect(User::firstWhere('email', 'h@example.com')->isAdmin())->toBeFalse();
});
