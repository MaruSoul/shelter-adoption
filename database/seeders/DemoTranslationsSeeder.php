<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\Post;
use App\Models\ShelterSetting;
use Database\Seeders\Support\DemoPets;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Англійська версія демонстраційних даних: тварини, статті й налаштування притулку.
 *
 * Заповнює лише порожні англійські поля, тож безпечно запускається на вже наповненій базі
 * (нічого не видаляє і не перезаписує переклади, які адміністратор уже вписав):
 *   php artisan db:seed --class=DemoTranslationsSeeder --force
 *
 * Свідомо без перекладу лишаються картка волонтера на модерації (cats/19) і чернетка
 * «Готуємось до зими» (перекладено лише заголовок) – щоб у демо було видно позначки «без EN».
 */
class DemoTranslationsSeeder extends Seeder
{
    private const UNTRANSLATED_PETS = ['cats/19'];

    private const POST_TITLES = [
        'Що купити до приїзду кошеняти: короткий список' => 'What to buy before your kitten arrives: a short list',
        'Вакцинація і стерилізація: навіщо і коли' => 'Vaccination and neutering: why and when',
        'Перші два тижні вдома: як допомогти собаці звикнути' => 'The first two weeks at home: helping your dog settle in',
        'Наші випускники: історії прилаштувань цієї осені' => 'Our graduates: this autumn’s adoption stories',
        'Готуємось до зими: що потрібно притулку' => 'Getting ready for winter: what the shelter needs',
    ];

    /** Статті, у яких перекладається лише заголовок. */
    private const TITLE_ONLY = ['Готуємось до зими: що потрібно притулку'];

    /** Український текст блоку або анонсу → англійський. */
    private const TEXTS = [
        'Коротко й по суті: досвід наших волонтерів і ветеринарів.' => 'Short and to the point: experience from our volunteers and vets.',
        'Перші дні в новому домі – важливий час і для тварини, і для людей. Ось що радять наші волонтери.' => 'The first days in a new home are an important time for both the animal and the people. Here is what our volunteers recommend.',
        'Підготуйте місце' => 'Prepare a space',
        'Лежанка в тихому куті' => 'A bed in a quiet corner',
        'Миски для води та корму' => 'Bowls for water and food',
        'Лоток або повідець залежно від виду' => 'A litter tray or a lead, depending on the species',
        'Терпіння в перші тижні окупається довірою на роки.' => 'Patience in the first weeks pays off in years of trust.',
        'Ветеринарка притулку' => 'Shelter vet',
    ];

    public function run(): void
    {
        $this->settings();
        $this->animals();
        $this->posts();
    }

    private function settings(): void
    {
        $settings = ShelterSetting::query()->first();
        if (! $settings) {
            return;
        }

        $settings->name_en ??= 'Domivka';
        $settings->tagline_en ??= 'Shelter for cats and dogs · Odesa';
        $settings->address_en ??= 'Odesa, [shelter address]';
        $settings->save();
    }

    private function animals(): void
    {
        $data = json_decode(file_get_contents(database_path('seeders/data/demo-pets-en.json')), true, flags: JSON_THROW_ON_ERROR);
        $sentences = $data['adoption_sentences'];

        foreach (DemoPets::all() as $pet) {
            $en = $data['pets'][$pet['key']] ?? null;
            if (! $en || in_array($pet['key'], self::UNTRANSLATED_PETS, true)) {
                continue;
            }

            $animals = Animal::withTrashed()
                ->where('name', $pet['info']['name'])
                ->whereHas('species', fn ($q) => $q->where('slug', $pet['species']))
                ->get();

            foreach ($animals as $animal) {
                $animal->name_en ??= $en['name'];
                $animal->story_en ??= $en['story'];
                $animal->behavior_en ??= $en['behavior'];
                $animal->adoption_terms_en ??= $this->adoptionTerms((string) $animal->adoption_terms, $sentences);
                $animal->current_location_en ??= $data['locations'][$animal->current_location] ?? null;
                $animal->saveQuietly();
            }
        }
    }

    /** Умови в демо складені з типових речень – перекладаємо кожне; якщо якесь невідоме, лишаємо без перекладу. */
    private function adoptionTerms(string $uk, array $sentences): ?string
    {
        $parts = preg_split('/(?<=\.)\s+/u', trim($uk), -1, PREG_SPLIT_NO_EMPTY);
        $en = [];
        foreach ($parts as $part) {
            if (! isset($sentences[$part])) {
                return null;
            }
            $en[] = $sentences[$part];
        }

        return $en ? implode(' ', $en) : null;
    }

    private function posts(): void
    {
        foreach (self::POST_TITLES as $uk => $en) {
            $post = Post::firstWhere('slug', Str::slug(Str::ascii($uk)));
            if (! $post) {
                continue;
            }

            $post->title_en ??= $en;
            if (! in_array($uk, self::TITLE_ONLY, true)) {
                $post->excerpt_en ??= self::TEXTS[$post->excerpt] ?? null;
                $post->blocks = $this->translateBlocks($post->blocks ?? []);
            }
            $post->save();
        }
    }

    private function translateBlocks(array $blocks): array
    {
        $t = fn (?string $text) => $text !== null ? (self::TEXTS[$text] ?? null) : null;

        return array_map(function (array $block) use ($t) {
            $data = $block['data'];
            foreach (['text', 'author', 'alt', 'caption'] as $field) {
                if (filled($data[$field] ?? null) && blank($data[$field.'_en'] ?? null) && ($en = $t($data[$field]))) {
                    $data[$field.'_en'] = $en;
                }
            }
            if (isset($data['items']) && blank(array_filter($data['items_en'] ?? []))) {
                $items = array_map(fn ($item) => $t($item) ?? '', $data['items']);
                if (array_filter($items)) {
                    $data['items_en'] = $items;
                }
            }
            $block['data'] = $data;

            return $block;
        }, $blocks);
    }
}
