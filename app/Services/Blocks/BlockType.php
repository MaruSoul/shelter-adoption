<?php

namespace App\Services\Blocks;

/**
 * Контракт типу блоку статті (FR-15).
 *
 * Кожен тип блоку (заголовок, абзац, зображення…) описується окремим класом:
 * які дані він зберігає, як вони перевіряються й нормалізуються, які тексти мають англійську пару
 * і як блок готується до показу. Схема статті PostBlocks працює лише через цей контракт,
 * тому новий тип блоку додається новим класом без змін у наявних (принцип відкритості/закритості).
 *
 * Шаблони блоку беруться за ключем типу:
 *   resources/views/blog/blocks/{key}.blade.php                     – показ на сайті;
 *   resources/views/livewire/admin/post-editor/blocks/{key}.blade.php – редагування в конструкторі.
 */
interface BlockType
{
    /** Ключ типу, що зберігається в JSON статті: heading, paragraph… */
    public function key(): string;

    /** Назва на кнопці «Додати блок». */
    public function label(): string;

    /** Дані нового порожнього блоку для конструктора. */
    public function blank(): array;

    /** Правила валідації даних блоку (Laravel Validator). */
    public function rules(): array;

    /** Чисті дані для збереження: обрізані пробіли, без порожніх перекладів. */
    public function normalize(array $data): array;

    /** Чи заповнено блок настільки, щоб показати його в попередньому перегляді. */
    public function isFilled(array $data): bool;

    /** Доповнює дані порожніми англійськими полями, щоб конструктор показав пару «UA | EN». */
    public function withEnglishKeys(array $data): array;

    /**
     * Підставляє англійський текст там, де він є.
     *
     * @return array{0: array, 1: bool} [дані для показу, чи лишився неперекладений текст]
     */
    public function localize(array $data): array;

    /** @return array{0: int, 1: int} [перекладено фрагментів, усього фрагментів з українським текстом] */
    public function englishCoverage(array $data): array;

    /** Готує блок {type, data, lang?} до шаблону; null – блок не показується. */
    public function forRender(array $block): ?array;
}
