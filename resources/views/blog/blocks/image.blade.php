{{-- Блок «image» статті на сайті. Дані готує App\Services\Blocks; $lang – позначка мови неперекладеного блоку. --}}
<figure class="flex flex-col gap-2" {{ $lang }}>
    <img src="{{ $block['data']['url'] }}" alt="{{ $block['data']['alt'] }}" loading="lazy" class="w-full rounded-3xl">
    @if (! empty($block['data']['caption']))<figcaption class="text-sm text-muted text-center">{{ $block['data']['caption'] }}</figcaption>@endif
</figure>
