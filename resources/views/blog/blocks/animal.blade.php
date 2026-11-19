{{-- Блок «animal» статті на сайті. Дані готує App\Services\Blocks; $lang – позначка мови неперекладеного блоку. --}}
<div class="max-w-xs"><x-animal-card :animal="$block['animal']" /></div>
