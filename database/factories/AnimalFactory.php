<?php

namespace Database\Factories;

use App\Enums\AnimalSource;
use App\Enums\AnimalStatus;
use App\Enums\Sex;
use App\Models\Animal;
use App\Models\Breed;
use App\Models\Color;
use App\Models\Locality;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    protected $model = Animal::class;

    private const NAMES = [
        'male' => ['Бублик', 'Граф', 'Шерлок', 'Кекс', 'Тофу', 'Ведмедик', 'Пиріжок', 'Марсик', 'Барон', 'Сірко', 'Лютик', 'Персик', 'Рижик', 'Боцман', 'Сніжок', 'Вафлик'],
        'female' => ['Ліра', 'Маслинка', 'Зефірка', 'Бусинка', 'Мірта', 'Ласка', 'Ніка', 'Ромашка', 'Корица', 'Ягідка', 'Кнопка', 'Мальва', 'Вишенька', 'Лада', 'Пушинка', 'Ясочка'],
    ];

    public function definition(): array
    {
        $sex = fake()->randomElement(Sex::cases());

        return [
            'species_id' => Species::query()->inRandomOrder()->value('id') ?? Species::factory(),
            'breed_id' => null,
            'color_id' => Color::query()->inRandomOrder()->value('id'),
            'locality_id' => Locality::query()->inRandomOrder()->value('id'),
            'name' => fake()->randomElement(self::NAMES[$sex->value]),
            'sex' => $sex,
            'birth_date' => fake()->dateTimeBetween('-9 years', '-2 months')->format('Y-m-d'),
            'is_sterilized' => fake()->boolean(70),
            'is_vaccinated' => fake()->boolean(85),
            'story' => fake()->randomElement([
                'Знайшли волонтери біля ринку разом із братом. Зараз здорова, оброблена від паразитів і чекає на свою людину.',
                'Потрапив до притулку після того, як попередні господарі переїхали. Звик до людей і дуже сумує за домом.',
                'Підібрали взимку на зупинці. Пройшов лікування, тепер повністю здоровий і готовий до нової родини.',
                'Народилась у притулку. Знає лоток, привчена до кігтеточки та переноски.',
            ]),
            'behavior' => fake()->randomElement([
                'Лагідна і цікава, любить іграшки-вудочки. Спокійно ставиться до інших котів.',
                'Активний, любить довгі прогулянки й апортування. Добре ладнає з дітьми.',
                'Спокійний, трохи сором\'язливий на початку, але швидко довіряється.',
                'Грайлива, обожнює, коли її гладять, і муркоче на руках.',
            ]),
            'adoption_terms' => fake()->randomElement([
                'Тільки в квартиру або будинок без самовигулу, з сітками на вікнах.',
                'У будинок з подвірʼям або квартиру з щоденними прогулянками. Обовʼязковий звʼязок з притулком після переїзду.',
                'Відповідальній родині з досвідом утримання тварин. Консультуємо після прилаштування.',
            ]),
            'source' => AnimalSource::Shelter,
            'status' => AnimalStatus::Available,
            'published_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Animal $animal) {
            // Породу беремо того самого виду, що й тварина
            if ($animal->breed_id === null && $animal->species_id) {
                $animal->breed_id = Breed::query()->where('species_id', $animal->species_id)->inRandomOrder()->value('id');
            }
        });
    }

    public function adopted(): static
    {
        return $this->state(fn () => ['status' => AnimalStatus::Adopted, 'adopted_at' => now()->subDays(fake()->numberBetween(1, 30))]);
    }

    public function fromVolunteer(User $user, AnimalStatus $status = AnimalStatus::Moderation): static
    {
        return $this->state(fn () => [
            'source' => AnimalSource::Volunteer,
            'status' => $status,
            'created_by' => $user->id,
            'current_location' => 'Тимчасова перетримка у волонтера, Одеса, Таїрова',
            'published_at' => $status === AnimalStatus::Available ? now() : null,
        ]);
    }

    public function status(AnimalStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
