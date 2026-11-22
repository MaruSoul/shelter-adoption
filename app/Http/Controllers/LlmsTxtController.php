<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Post;
use App\Models\ShelterSetting;
use App\Models\Species;
use Illuminate\Http\Response;

/**
 * llms.txt – коротка довідка про сайт для ШІ-асистентів (формат llmstxt.org): хто ми, де каталог,
 * як узяти тварину, свіжі статті. Формується автоматично з даних сайту, вести його вручну не треба.
 */
class LlmsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $shelter = ShelterSetting::current();
        $url = app('url');
        $name = $shelter->tr('name', 'uk');
        $count = Animal::query()->visible()->where('status', 'available')->count();

        $lines = [
            '# '.$name.($shelter->name_en ? ' ('.$shelter->name_en.')' : ''),
            '',
            '> '.trim(($shelter->tr('tagline', 'uk') ?? 'Притулок для тварин').'. Сайт для прилаштування котів і собак: каталог тварин, які шукають дім, анкета «Взяти додому» без реєстрації, блог із порадами.'),
            '',
            'Зараз дім шукають тварин: '.$count.'. Анкету можна подати на сторінці будь-якої тварини; реєстрація не потрібна. Сайт доступний українською та англійською (/en/).',
        ];
        if ($shelter->address || $shelter->phone || $shelter->email) {
            $lines[] = 'Контакти: '.collect([$shelter->address, $shelter->phone, $shelter->email])->filter()->implode(' · ').'.';
        }

        $lines = [...$lines, '', '## Каталог', '',
            '- ['.'Усі тварини'.']('.$url->routeIn('uk', 'animals.index').'): каталог із фільтрами за видом, віком, статтю, містом',
        ];
        foreach (Species::orderBy('id')->get() as $species) {
            $lines[] = '- ['.$species->name_plural.']('.$url->routeIn('uk', 'animals.species', $species->slug).')';
        }
        $lines[] = '- [English version]('.$url->routeIn('en', 'animals.index').')';

        $posts = Post::published()->latest('published_at')->limit(20)->get(['id', 'slug', 'title', 'excerpt']);
        if ($posts->isNotEmpty()) {
            $lines = [...$lines, '', '## Статті блогу', ''];
            foreach ($posts as $post) {
                $lines[] = '- ['.$post->title.']('.$url->routeIn('uk', 'blog.show', $post).')'.($post->excerpt ? ': '.$post->excerpt : '');
            }
        }

        $lines = [...$lines, '', '## Інше', '',
            '- [Як допомогти притулку]('.$url->routeIn('uk', 'help').'): банка Monobank, реквізити, PayPal, звіти',
            '- [Блог]('.$url->routeIn('uk', 'blog.index').')',
            '- [Політика конфіденційності]('.$url->routeIn('uk', 'privacy').')',
            '- [Карта сайту]('.route('sitemap').')',
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
