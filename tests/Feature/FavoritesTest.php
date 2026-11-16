<?php

use App\Enums\AnimalStatus;
use App\Models\Animal;

it('користувач додає тварину в обрані й прибирає її', function () {
    $me = volunteer(['email_verified_at' => now()]);
    $animal = Animal::factory()->create(['name' => 'Тигрик']);

    $this->actingAs($me)->postJson(route('favorites.toggle', $animal->id))->assertJson(['favorite' => true]);
    expect($me->favorites()->pluck('animals.id')->all())->toBe([$animal->id]);

    $this->get(route('favorites.index'))->assertOk()->assertSee('Тигрик');

    $this->postJson(route('favorites.toggle', $animal->id))->assertJson(['favorite' => false]);
    expect($me->favorites()->count())->toBe(0);
});

it('гість не може змінювати обрані на сервері, але бачить картки за id зі свого браузера', function () {
    $public = Animal::factory()->create(['name' => 'Публічна']);
    $hidden = Animal::factory()->fromVolunteer(volunteer())->create(['name' => 'Прихована']);

    $this->postJson(route('favorites.toggle', $public->id))->assertUnauthorized();

    $this->get(route('favorites.cards', ['ids' => "{$public->id},{$hidden->id},abc"]))
        ->assertOk()
        ->assertSee('Публічна')
        ->assertDontSee('Прихована');
});

it('після входу обрані з браузера переносяться в акаунт (лише публічні тварини)', function () {
    $me = volunteer(['email_verified_at' => now()]);
    $a = Animal::factory()->create();
    $b = Animal::factory()->adopted()->create();
    $hidden = Animal::factory()->fromVolunteer(volunteer(), AnimalStatus::Moderation)->create();

    $this->actingAs($me)
        ->postJson(route('favorites.sync'), ['ids' => [$a->id, $b->id, $hidden->id]])
        ->assertOk();

    expect($me->favorites()->pluck('animals.id')->sort()->values()->all())->toBe(collect([$a->id, $b->id])->sort()->values()->all());
});

it('не дає додати в обрані тварину, якої немає в каталозі', function () {
    $hidden = Animal::factory()->fromVolunteer(volunteer())->create();

    $this->actingAs(volunteer(['email_verified_at' => now()]))
        ->postJson(route('favorites.toggle', $hidden->id))
        ->assertNotFound();
});
