<?php

use App\Support\SimpleMarkup;

it('робить посилання на сторінки сайту й зовнішні адреси', function () {
    expect((string) SimpleMarkup::inline('Дивіться [наших котиків](/tvaryny/cat) і [фонд](https://example.org/help).'))
        ->toContain('<a href="/tvaryny/cat">наших котиків</a>')
        ->toContain('<a href="https://example.org/help" rel="noopener" target="_blank">фонд</a>');
});

it('екранує HTML і не пропускає небезпечні посилання (XSS)', function (string $text) {
    $html = (string) SimpleMarkup::inline($text);

    // Текст лишається видимим як є, але жодного тегу чи атрибута з нього не зʼявляється
    expect($html)->not->toContain('<script')->not->toContain('<a href="javascript')->not->toContain('href="javascript')->not->toContain('onmouseover="');
})->with([
    '<script>alert(1)</script>',
    '[клік](javascript:alert(1))',
    '[клік](/x" onmouseover="alert(1))',
    '<a href="javascript:alert(1)">клік</a>',
]);

it('розбирає заголовки, списки й абзаци SEO-тексту', function () {
    $html = (string) SimpleMarkup::block("## Як узяти кота\n\nПерший абзац з **важливим**.\n\n- корм\n- лоток");

    expect($html)->toContain('<h2>Як узяти кота</h2>')
        ->toContain('<p>Перший абзац з <strong>важливим</strong>.</p>')
        ->toContain('<ul><li>корм</li><li>лоток</li></ul>');
});

it('прибирає позначки для мета-опису', function () {
    expect(SimpleMarkup::plain('Візьміть [кота](/tvaryny/cat) **сьогодні**'))->toBe('Візьміть кота сьогодні');
});

it('не вважає адресу «//інший-сайт» внутрішнім посиланням', function () {
    expect((string) SimpleMarkup::inline('[сюди](//evil.example/x)'))->not->toContain('<a ');
});
