{{-- Блок «paragraph» статті на сайті. Дані готує App\Services\Blocks; $lang – позначка мови неперекладеного блоку. --}}
<p class="whitespace-pre-line" {{ $lang }}>{{ \App\Support\SimpleMarkup::inline($block['data']['text']) }}</p>
