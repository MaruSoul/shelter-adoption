<?php

namespace Database\Seeders;

use App\Enums\AnimalStatus;
use App\Enums\ApplicationStatus;
use App\Enums\PostStatus;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use App\Models\ApplicationStatusChange;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\DictionaryTranslations;
use App\Models\Region;
use App\Models\ShelterSetting;
use App\Models\User;
use Database\Seeders\Support\DemoPets;
use Database\Seeders\Support\PlaceholderPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Демонстраційний набір даних (синтетичний): звичайні записи, достатньо тварин для
 * фільтрів і пагінації, картки на модерації, кілька анкет на одну тварину,
 * анкети іншого користувача, прилаштовані тварини, чернетка статті.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DictionarySeeder::class);

        ShelterSetting::query()->firstOrCreate([], [
            'name' => 'Клуб хвостики',
            'tagline' => 'Притулок для котів і собак · Одеса',
            'address' => 'м. Одеса, вул. [адреса притулку]',
            'phone' => '+380 00 000 00 00',
            'email' => 'hello@domivka.local',
            // Демо: головні сторінки мереж; справжні посилання притулок вставляє в адмінці
            'instagram_url' => 'https://www.instagram.com/',
            'facebook_url' => 'https://www.facebook.com/',
            'telegram_url' => 'https://telegram.org/',
            'tiktok_url' => 'https://www.tiktok.com/',
            'youtube_url' => 'https://www.youtube.com/',
            'notification_email' => 'admin@domivka.local',
        ]);

        $admin = User::factory()->admin()->create([
            'name' => 'Адміністратор притулку',
            'email' => 'admin@domivka.local',
            'password' => 'admin-password',
        ]);

        $volunteer = User::factory()->create([
            'name' => 'Олена Волонтерка',
            'email' => 'volunteer@domivka.local',
            'password' => 'user-password',
            // Профіль заповнено – в анкеті все підставиться автоматично
            'phone' => '+380671234567',
            'region_id' => Region::firstWhere('name', 'Одеська')?->id,
        ]);

        $otherUser = User::factory()->create([
            'name' => 'Андрій Коваль',
            'email' => 'andrii@domivka.local',
            'password' => 'user-password',
        ]);

        // 40 демо-тварин з анкет і серій фото (database/seeders/photos/pets)
        $adoptedKeys = ['cats/17', 'cats/18', 'dogs/17', 'dogs/18', 'dogs/19'];
        $volunteerKeys = ['dogs/20' => AnimalStatus::Available, 'cats/19' => AnimalStatus::Moderation, 'cats/20' => AnimalStatus::Rejected];

        $available = collect();
        $adopted = collect();
        $volunteerAnimals = collect();

        // Коти й собаки впереміш, щоб на головній і в каталозі було різноманіття
        $pets = collect(DemoPets::all())->groupBy('species');
        $mixed = $pets['cat']->zip($pets['dog'])->flatten(1)->filter()->values();

        foreach ($mixed as $i => $pet) {
            $attributes = DemoPets::attributes($pet);

            if (isset($volunteerKeys[$pet['key']])) {
                $status = $volunteerKeys[$pet['key']];
                $animal = Animal::factory()->fromVolunteer($volunteer, $status)->create($attributes + [
                    'published_at' => $status === AnimalStatus::Available ? now()->subDays($i + 1) : null,
                ]);
                $volunteerAnimals->push($animal);
            } elseif (in_array($pet['key'], $adoptedKeys, true)) {
                $animal = Animal::factory()->adopted()->create($attributes + ['published_at' => now()->subDays(60 + $i)]);
                $adopted->push($animal);
            } else {
                $animal = Animal::factory()->create($attributes + ['published_at' => now()->subDays($i + 1)]);
                $available->push($animal);
            }

            DemoPets::attachPhotos($animal, $pet);
        }

        $volunteerAnimals->last()->forceFill(['moderation_note' => 'Додайте, будь ласка, контакти перетримки і кілька слів про характер.'])->save();

        // Анкети: кілька на одну тварину, у різних статусах, з історією переходів
        $popular = $available->first();
        $this->application($popular, ApplicationStatus::New, null);
        $this->application($popular, ApplicationStatus::InProgress, $admin);
        $this->application($popular, ApplicationStatus::MeetingScheduled, $admin, ['email' => $volunteer->email, 'user_id' => $volunteer->id, 'name' => $volunteer->name]);

        foreach ($available->slice(1, 8) as $animal) {
            $this->application($animal, ApplicationStatus::New, null);
        }
        $this->application($available[10], ApplicationStatus::Declined, $admin, ['email' => $otherUser->email, 'user_id' => $otherUser->id, 'name' => $otherUser->name]);

        foreach ($adopted as $animal) {
            $this->application($animal, ApplicationStatus::Adopted, $admin);
        }

        $this->seedBlog($admin, $available->take(2));

        // Англійська версія тварин, статей і налаштувань
        $this->call(DemoTranslationsSeeder::class);
        // Англійські назви видів, порід, забарвлень і рубрик – з lang/en.json (далі редагуються в «Довідниках»)
        DictionaryTranslations::backfill();
    }

    private function application(Animal $animal, ApplicationStatus $final, ?User $admin, array $attributes = []): void
    {
        $application = AdoptionApplication::factory()
            ->for($animal)
            ->status($final)
            ->create($attributes);

        $path = match ($final) {
            ApplicationStatus::New => [],
            ApplicationStatus::InProgress => [ApplicationStatus::InProgress],
            ApplicationStatus::MeetingScheduled => [ApplicationStatus::InProgress, ApplicationStatus::MeetingScheduled],
            ApplicationStatus::Adopted => [ApplicationStatus::InProgress, ApplicationStatus::MeetingScheduled, ApplicationStatus::Adopted],
            ApplicationStatus::Declined => [ApplicationStatus::InProgress, ApplicationStatus::Declined],
            ApplicationStatus::Closed => [ApplicationStatus::Closed],
        };

        ApplicationStatusChange::create(['adoption_application_id' => $application->id, 'from_status' => null, 'to_status' => ApplicationStatus::New]);
        $from = ApplicationStatus::New;
        foreach ($path as $to) {
            ApplicationStatusChange::create([
                'adoption_application_id' => $application->id,
                'user_id' => $admin?->id,
                'from_status' => $from,
                'to_status' => $to,
                'note' => $to === ApplicationStatus::MeetingScheduled ? 'Домовились про зустріч у суботу о 12:00.' : null,
            ]);
            $from = $to;
        }
    }

    private function seedBlog(User $admin, $animals): void
    {
        $tips = PostCategory::create(['name' => 'Корисні поради', 'slug' => 'porady']);
        $health = PostCategory::create(['name' => 'Здоровʼя та виховання', 'slug' => 'zdorovya']);
        $news = PostCategory::create(['name' => 'Новини притулку', 'slug' => 'novyny']);

        $posts = [
            [$tips, 'Що купити до приїзду кошеняти: короткий список', PostStatus::Published],
            [$health, 'Вакцинація і стерилізація: навіщо і коли', PostStatus::Published],
            [$tips, 'Перші два тижні вдома: як допомогти собаці звикнути', PostStatus::Published],
            [$news, 'Наші випускники: історії прилаштувань цієї осені', PostStatus::Published],
            [$news, 'Готуємось до зими: що потрібно притулку', PostStatus::Draft],
        ];

        foreach ($posts as $i => [$category, $title, $status]) {
            $post = new Post([
                'post_category_id' => $category->id,
                'title' => $title,
                'slug' => Str::slug(Str::ascii($title)),
                'excerpt' => 'Коротко й по суті: досвід наших волонтерів і ветеринарів.',
                'cover_path' => PlaceholderPhoto::make('posts', 10 + $i, 1200, 630),
                'blocks' => [
                    ['type' => 'paragraph', 'data' => ['text' => 'Перші дні в новому домі – важливий час і для тварини, і для людей. Ось що радять наші волонтери.']],
                    ['type' => 'heading', 'data' => ['text' => 'Підготуйте місце', 'level' => 2]],
                    ['type' => 'list', 'data' => ['style' => 'bullet', 'items' => ['Лежанка в тихому куті', 'Миски для води та корму', 'Лоток або повідець залежно від виду']]],
                    ['type' => 'quote', 'data' => ['text' => 'Терпіння в перші тижні окупається довірою на роки.', 'author' => 'Ветеринарка притулку']],
                    ['type' => 'animal', 'data' => ['animal_id' => $animals[$i % 2]->id]],
                ],
            ]);
            $post->author_id = $admin->id;
            $post->status = $status;
            $post->published_at = $status === PostStatus::Published ? now()->subDays(3 * ($i + 1)) : null;
            $post->save();
        }

        // Замість заглушок – справжні фото
        $this->call(BlogCoverSeeder::class);
    }
}
