{{-- Блок «quote» статті на сайті. Дані готує App\Services\Blocks; $lang – позначка мови неперекладеного блоку. --}}
<blockquote class="rounded-3xl bg-sun-soft px-7 py-6 flex flex-col gap-2" {{ $lang }}>
    <p class="font-display font-bold text-2xl leading-snug">{{ ($block['lang'] ?? app()->getLocale()) === 'en' ? '“' : '«' }}{{ $block['data']['text'] }}{{ ($block['lang'] ?? app()->getLocale()) === 'en' ? '”' : '»' }}</p>
    @if (! empty($block['data']['author']))<footer class="text-muted">– {{ $block['data']['author'] }}</footer>@endif
</blockquote>
