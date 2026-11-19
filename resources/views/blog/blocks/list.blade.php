{{-- Блок «list» статті на сайті. Дані готує App\Services\Blocks; $lang – позначка мови неперекладеного блоку. --}}
@if ($block['data']['style'] === 'ordered')
    <ol class="list-decimal pl-6 flex flex-col gap-1.5" {{ $lang }}>@foreach ($block['data']['items'] as $item)<li>{{ \App\Support\SimpleMarkup::inline($item) }}</li>@endforeach</ol>
@else
    <ul class="list-disc pl-6 flex flex-col gap-1.5 marker:text-primary" {{ $lang }}>@foreach ($block['data']['items'] as $item)<li>{{ \App\Support\SimpleMarkup::inline($item) }}</li>@endforeach</ul>
@endif
