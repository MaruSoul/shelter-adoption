@props(['count' => 22])
{{-- Короткий одноразовий салют навколо Лапи. Лише прикраса: прихований від читачів екрана і вимикається при «зменшити рух». --}}
@php
    $colors = ['#F6C945', '#9478F7', '#FF8FB1', '#5546B8', '#FFE47A', '#7DD3B0'];
    mt_srand(7);
@endphp
<span {{ $attributes->merge(['class' => 'confetti']) }} aria-hidden="true">
    @for ($i = 0; $i < $count; $i++)
        @php
            $angle = deg2rad(-90 + mt_rand(-80, 80));
            $dist = mt_rand(70, 150);
        @endphp
        <i style="--c: {{ $colors[$i % count($colors)] }}; --x: {{ round(cos($angle) * $dist) }}px; --y: {{ round(sin($angle) * $dist) }}px; --r: {{ mt_rand(-540, 540) }}deg; --d: {{ mt_rand(0, 160) }}ms; {{ $i % 3 === 0 ? 'border-radius: 50%; width: 8px; height: 8px;' : '' }}"></i>
    @endfor
</span>
