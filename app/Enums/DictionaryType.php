<?php

namespace App\Enums;

use App\Models\Breed;
use App\Models\Color;
use App\Models\PostCategory;
use App\Models\Species;
use Illuminate\Database\Eloquent\Model;

/**
 * Довідники, які адміністратор редагує в панелі (вкладки сторінки «Довідники»).
 *
 * Усе, чим довідники відрізняються, зібрано тут: модель, поля з парою «UA | EN», довжина назви,
 * чим довідник використовується (і тому не видаляється), чи має він постійну адресу (slug).
 * Компонент App\Livewire\Admin\Dictionaries працює лише через ці методи й не перевіряє назви вкладок.
 */
enum DictionaryType: string
{
    case Species = 'species';
    case Breeds = 'breeds';
    case Colors = 'colors';
    case Categories = 'categories';

    /** Назва вкладки. */
    public function label(): string
    {
        return match ($this) {
            self::Species => 'Види тварин',
            self::Breeds => 'Породи',
            self::Colors => 'Забарвлення',
            self::Categories => 'Рубрики блогу',
        };
    }

    /** Для заголовка «Додати …». */
    public function accusative(): string
    {
        return match ($this) {
            self::Species => 'вид',
            self::Breeds => 'породу',
            self::Colors => 'забарвлення',
            self::Categories => 'рубрику',
        };
    }

    /** @return class-string<Model> */
    public function model(): string
    {
        return match ($this) {
            self::Species => Species::class,
            self::Breeds => Breed::class,
            self::Colors => Color::class,
            self::Categories => PostCategory::class,
        };
    }

    /** Поля, що редагуються парою «UA | EN» (до кожного є поле з суфіксом _en). */
    public function fields(): array
    {
        return $this === self::Species ? ['name', 'name_plural'] : ['name'];
    }

    /** Назва має форму множини (заголовок розділу каталогу: «Котики»). */
    public function hasPluralName(): bool
    {
        return in_array('name_plural', $this->fields(), true);
    }

    public function maxLength(): int
    {
        return match ($this) {
            self::Breeds => 80,
            self::Categories => 60,
            default => 50,
        };
    }

    /** Звʼязок, через який записом користуються; використаний запис не видаляється. */
    public function usageRelation(): string
    {
        return $this === self::Categories ? 'posts' : 'animals';
    }

    /** Форми слова для лічильника використань: 1 тварина, 2 тварини, 5 тварин. */
    public function usageForms(): array
    {
        return $this === self::Categories ? ['стаття', 'статті', 'статей'] : ['тварина', 'тварини', 'тварин'];
    }

    /** Запис має постійну адресу (розділ каталогу, рубрика блогу), яка створюється один раз. */
    public function hasSlug(): bool
    {
        return in_array($this, [self::Species, self::Categories], true);
    }

    /** Породи існують у межах виду: список, унікальність назви й новий запис – для обраного виду. */
    public function isScopedBySpecies(): bool
    {
        return $this === self::Breeds;
    }

    /** Вид може повторювати назву з іншим словом у множині, тож унікальність назви – для решти довідників. */
    public function hasUniqueName(): bool
    {
        return $this !== self::Species;
    }

    /** Вид тварини містить породи: показуємо їх кількість і попереджаємо при видаленні. */
    public function hasBreeds(): bool
    {
        return $this === self::Species;
    }
}
