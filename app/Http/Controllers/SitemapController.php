<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Post;
use App\Models\Species;
use Illuminate\Http\Response;

/**
 * Карта сайту для пошукових систем (NFR-08).
 * Кожна сторінка подається обома мовами з посиланнями hreflang одна на одну.
 * Стаття без англійського заголовка має лише українську версію: англійська сторінка показувала б той самий текст.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $url = app('url');
        $pages = collect();
        $add = function (string $name, array $parameters, $lastmod, bool $english = true) use ($url, $pages) {
            $pages->push([
                'uk' => $url->routeIn('uk', $name, $parameters),
                'en' => $english ? $url->routeIn('en', $name, $parameters) : null,
                'lastmod' => $lastmod,
            ]);
        };

        $add('home', [], now());
        $add('animals.index', [], now());
        Species::orderBy('id')->each(fn ($s) => $add('animals.species', [$s->slug], now()));
        $add('blog.index', [], now());
        $add('help', [], now());

        Animal::query()->visible()->select(['id', 'slug', 'updated_at'])->orderBy('id')
            ->each(fn ($a) => $add('animals.show', [$a], $a->updated_at));

        Post::published()->select(['id', 'slug', 'title_en', 'updated_at'])->orderBy('id')
            ->each(fn ($p) => $add('blog.show', [$p], $p->updated_at, filled($p->title_en)));

        // XML-оголошення додається тут, а не в шаблоні: рядок «<?xml» у Blade-шаблоні PHP з увімкненим
        // short_open_tag (типово в офіційному образі Docker) сприймає як відкривальний тег PHP – помилка 500
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', ['pages' => $pages])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
