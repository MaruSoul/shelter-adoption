<?php

use App\Models\ShelterSetting;
use Illuminate\Support\Facades\Cache;

it('не падає, якщо в кеші лишились налаштування без нових полів', function () {
    // Стара версія кешу: ще немає facebook_url, tiktok_url, youtube_url
    Cache::forever('shelter_settings', ['id' => 1, 'name' => 'Домівка', 'instagram_url' => 'https://www.instagram.com/x/']);

    $settings = ShelterSetting::current();

    expect($settings->facebook_url)->toBeNull()
        ->and($settings->socialLinks())->toHaveKey('instagram')
        ->and($settings->socialLinks())->not->toHaveKey('facebook');
});
