<?php

namespace App\View\Components;

use App\Enums\AnimalStatus;
use App\Enums\ApplicationStatus;
use App\Models\AdoptionApplication;
use App\Models\Animal;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Меню панелі притулку з лічильниками нових анкет і карток на модерації.
 * Запити до бази виконуються тут, а не в шаблоні: шаблон лише показує готові дані.
 */
class AdminNav extends Component
{
    /** @var list<array{0: string, 1: string, 2: ?int}> маршрут, назва, лічильник */
    public array $items;

    public function __construct()
    {
        $this->items = [
            ['admin.dashboard', 'Огляд', null],
            ['admin.applications.index', 'Анкети', AdoptionApplication::where('status', ApplicationStatus::New)->count()],
            ['admin.animals.index', 'Тварини', null],
            ['admin.moderation', 'Модерація', Animal::where('status', AnimalStatus::Moderation)->count()],
            ['admin.posts.index', 'Статті', null],
            ['admin.dictionaries', 'Довідники', null],
            ['admin.donations', 'Допомога', null],
            ['admin.seo', 'SEO', null],
            ['admin.settings', 'Налаштування', null],
        ];
    }

    public function render(): View
    {
        return view('components.admin-nav');
    }
}
