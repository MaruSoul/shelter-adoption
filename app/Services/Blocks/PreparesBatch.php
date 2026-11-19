<?php

namespace App\Services\Blocks;

/**
 * Тип блоку, якому перед показом потрібно одним запитом завантажити дані для всіх своїх блоків статті
 * (наприклад, картки тварин), а не робити окремий запит на кожен блок.
 */
interface PreparesBatch
{
    /** @param  array<int, array>  $blocks  усі блоки цього типу в статті */
    public function prepare(array $blocks): void;
}
