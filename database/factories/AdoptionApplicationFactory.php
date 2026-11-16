<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Enums\ContactChannel;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdoptionApplication>
 */
class AdoptionApplicationFactory extends Factory
{
    protected $model = AdoptionApplication::class;

    public function definition(): array
    {
        return [
            'animal_id' => Animal::factory(),
            'region_id' => Region::query()->inRandomOrder()->value('id'),
            'name' => fake()->firstName().' '.fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+380'.fake()->numerify('#########'),
            'social_url' => null,
            'contact_channels' => fake()->randomElements(ContactChannel::cases(), fake()->numberBetween(1, 3)),
            'comment' => fake()->randomElement([
                'Живемо у квартирі, є досвід з котами. Працюю віддалено, тож тварина не буде сама.',
                'Маємо приватний будинок з огородженим подвірʼям, двоє дітей 8 і 11 років.',
                null,
            ]),
            'consent_at' => now(),
            'status' => ApplicationStatus::New,
            'ip_address' => fake()->ipv4(),
        ];
    }

    public function status(ApplicationStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
