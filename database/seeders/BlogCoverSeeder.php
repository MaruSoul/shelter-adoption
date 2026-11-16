<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Обкладинки для демонстраційних статей блогу (database/seeders/photos/blog, джерела – CREDITS.md).
 * Можна запускати окремо на вже заповненій базі:
 *   php artisan db:seed --class=BlogCoverSeeder --force
 */
class BlogCoverSeeder extends Seeder
{
    /** Назва статті → файл обкладинки. */
    public const COVERS = [
        'Що купити до приїзду кошеняти: короткий список' => 'krista-mangulsone-9gz3wfHr65U.jpg',
        'Вакцинація і стерилізація: навіщо і коли' => 'james-barker-v3-zcCWMjgM.jpg',
        'Перші два тижні вдома: як допомогти собаці звикнути' => 'berkay-gumustekin-ngqyo2AYYnE.jpg',
        'Наші випускники: історії прилаштувань цієї осені' => 'alvan-nee-T-0EW-SEbsE.jpg',
        'Готуємось до зими: що потрібно притулку' => 'joe-caione-qO-PIF84Vxg.jpg',
    ];

    public function run(): void
    {
        foreach (self::COVERS as $title => $file) {
            $post = Post::firstWhere('slug', Str::slug(Str::ascii($title)));

            if (! $post) {
                continue;
            }

            $path = 'posts/'.$file;
            Storage::disk('public')->put($path, file_get_contents(database_path('seeders/photos/blog/'.$file)));

            $post->cover_path = $path;
            $post->save();
        }
    }
}
