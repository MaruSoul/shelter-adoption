<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Генерує нейтральні заглушки замість фото для демонстраційних даних
 * (кольорове тло з відбитком лапи). Справжні фото завантажує адміністратор.
 */
final class PlaceholderPhoto
{
    private const BACKGROUNDS = ['#E6E1FA', '#FFF1C2', '#DFF0EC', '#F6E0EA', '#ECE8FA'];

    public static function make(string $directory, int $seed, int $width = 1200, int $height = 900): string
    {
        $image = imagecreatetruecolor($width, $height);

        $bg = self::color($image, self::BACKGROUNDS[$seed % count(self::BACKGROUNDS)]);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        $paw = self::color($image, '#5546B8', 90);
        $cx = (int) ($width / 2);
        $cy = (int) ($height / 2) + 40;
        $scale = $height / 900;

        imagefilledellipse($image, $cx, $cy, (int) (220 * $scale), (int) (180 * $scale), $paw);
        foreach ([[-150, -120], [-60, -200], [60, -200], [150, -120]] as [$dx, $dy]) {
            imagefilledellipse($image, $cx + (int) ($dx * $scale), $cy + (int) ($dy * $scale), (int) (90 * $scale), (int) (105 * $scale), $paw);
        }

        ob_start();
        imagejpeg($image, null, 82);
        $bytes = ob_get_clean();
        imagedestroy($image);

        $path = trim($directory, '/').'/placeholder-'.$seed.'-'.$width.'.jpg';
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    private static function color(\GdImage $image, string $hex, int $alpha = 0): int
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        return imagecolorallocatealpha($image, $r, $g, $b, $alpha);
    }
}
