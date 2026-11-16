<?php

use App\Models\Animal;

function withPhoto(Animal $animal): Animal
{
    $animal->photos()->create(['path' => "animals/{$animal->id}/1.jpg", 'sort_order' => 0]);

    return $animal;
}

it('shows the «Хто мій друг?» game with animals that look for a home', function () {
    $waiting = withPhoto(Animal::factory()->create(['name' => 'Хмаринка']));
    $adopted = withPhoto(Animal::factory()->adopted()->create(['name' => 'Вдомашній']));
    Animal::factory()->create(['name' => 'Безфото']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Хто мій друг?')
        ->assertViewHas('lottery', fn (array $pool) => collect($pool)->pluck('name')->all() === ['Хмаринка']
            && $pool[0]['adopt'] === route('animals.adopt', $waiting, false));
});

it('hides the game when nobody with a photo is waiting', function () {
    Animal::factory()->create();

    $this->get('/')->assertOk()->assertDontSee('Хто мій друг?');
});
