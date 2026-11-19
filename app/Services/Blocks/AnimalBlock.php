<?php

namespace App\Services\Blocks;

use App\Models\Animal;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Картка тварини з каталогу. Текстів не зберігає: кличка й опис беруться з картки тварини,
 * тож перекладаються разом із нею. Тварина, прихована з каталогу, у статті не показується.
 */
class AnimalBlock extends AbstractBlock implements PreparesBatch
{
    /** @var Collection<int, Animal> тварини поточної статті, завантажені одним запитом */
    private Collection $animals;

    public function __construct()
    {
        $this->animals = new Collection;
    }

    public function key(): string
    {
        return 'animal';
    }

    public function label(): string
    {
        return 'Картка тварини';
    }

    public function blank(): array
    {
        return ['animal_id' => null];
    }

    public function rules(): array
    {
        return ['animal_id' => ['required', 'integer', Rule::exists('animals', 'id')]];
    }

    public function normalize(array $data): array
    {
        return ['animal_id' => (int) $data['animal_id']];
    }

    public function isFilled(array $data): bool
    {
        return ! empty($data['animal_id']);
    }

    public function prepare(array $blocks): void
    {
        $ids = collect($blocks)->pluck('data.animal_id')->filter()->all();

        $this->animals = Animal::query()->visible()->whereKey($ids)
            ->with(['species', 'locality', 'coverPhoto'])->get()->keyBy('id');
    }

    public function forRender(array $block): ?array
    {
        $animal = $this->animals->get($block['data']['animal_id'] ?? 0);

        return $animal ? ['type' => 'animal', 'animal' => $animal] : null;
    }
}
