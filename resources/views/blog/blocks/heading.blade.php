{{-- Блок «heading» статті на сайті. Дані готує App\Services\Blocks; $lang – позначка мови неперекладеного блоку. --}}
@if (($block['data']['level'] ?? 2) === 3)
    <h3 class="text-2xl pt-2" {{ $lang }}>{{ $block['data']['text'] }}</h3>
@else
    <h2 class="text-3xl pt-3" {{ $lang }}>{{ $block['data']['text'] }}</h2>
@endif
