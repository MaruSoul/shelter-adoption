<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Post;
use Illuminate\Http\Response;

/** Карта сайту для пошукових систем (NFR-08). */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => now()],
            ['loc' => route('animals.index'), 'lastmod' => now()],
            ['loc' => route('blog.index'), 'lastmod' => now()],
        ]);

        Animal::query()->visible()->select(['id', 'slug', 'updated_at'])->orderBy('id')
            ->each(fn ($a) => $urls->push(['loc' => route('animals.show', $a), 'lastmod' => $a->updated_at]));

        Post::published()->select(['id', 'slug', 'updated_at'])->orderBy('id')
            ->each(fn ($p) => $urls->push(['loc' => route('blog.show', $p), 'lastmod' => $p->updated_at]));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
