<?php

namespace App\Livewire\Admin;

use App\Models\Breed;
use App\Models\Color;
use App\Models\PostCategory;
use App\Models\Species;
use App\Support\Ukrainian;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Довідники сайту: види тварин, породи, забарвлення й рубрики блогу.
 *
 * Назви редагуються парою «UA | EN». Запис, який уже використовується (тварина чи стаття
 * посилається на нього), видалити не можна – лише перейменувати: так жодна картка не втратить
 * породу чи рубрику мовчки. Адреса (slug) виду й рубрики створюється один раз і далі не змінюється,
 * бо на неї вже можуть вести посилання.
 */
class Dictionaries extends Component
{
    public const TABS = [
        'species' => 'Види тварин',
        'breeds' => 'Породи',
        'colors' => 'Забарвлення',
        'categories' => 'Рубрики блогу',
    ];

    #[Url]
    public string $tab = 'species';

    /** Вид, породи якого показано на вкладці «Породи». */
    #[Url(as: 'vyd')]
    public ?int $speciesFilter = null;

    /** Запис, що редагується зараз: ['id' => …, поля…]. */
    public array $edit = [];

    /** Новий запис. */
    public array $new = [];

    public ?string $flash = null;

    public function mount(): void
    {
        $this->tab = array_key_exists($this->tab, self::TABS) ? $this->tab : 'species';
        $this->speciesFilter ??= Species::orderBy('id')->value('id');
        $this->resetNew();
    }

    public function updatedTab(): void
    {
        $this->tab = array_key_exists($this->tab, self::TABS) ? $this->tab : 'species';
        $this->edit = [];
        $this->flash = null;
        $this->resetValidation();
        $this->resetNew();
    }

    public function updatedSpeciesFilter(): void
    {
        $this->edit = [];
        $this->resetNew();
    }

    public function startEdit(int $id): void
    {
        $record = $this->query()->findOrFail($id);
        $this->resetValidation();
        $this->edit = ['id' => $record->id] + collect($this->fields())
            ->flatMap(fn ($f) => [$f => (string) $record->{$f}, $f.'_en' => (string) $record->{$f.'_en'}])->all();
    }

    public function cancelEdit(): void
    {
        $this->edit = [];
        $this->resetValidation();
    }

    public function save(): void
    {
        $record = $this->query()->findOrFail($this->edit['id'] ?? 0);
        $data = $this->validate($this->rules('edit', $record))['edit'];
        unset($data['id']);
        $record->fill($this->clean($data))->save();

        $this->edit = [];
        $this->flash = 'Зміни збережено.';
    }

    public function add(): void
    {
        $data = $this->clean($this->validate($this->rules('new'))['new']);
        $model = $this->modelClass();
        $record = new $model($data);

        if ($this->tab === 'breeds') {
            $record->species_id = $this->speciesFilter;
        }
        if (in_array($this->tab, ['species', 'categories'], true)) {
            $record->slug = $this->uniqueSlug($data['name_en'] ?? null ?: Ukrainian::transliterate($data['name']));
        }
        $record->save();

        $this->resetNew();
        $this->flash = '«'.$record->name.'» додано.';
    }

    public function delete(int $id): void
    {
        $record = $this->query()->withCount($this->usageRelation())->findOrFail($id);
        if ($record->{$this->usageRelation().'_count'} > 0) {
            $this->flash = null;
            $this->addError('delete', '«'.$record->name.'» використовується, тому його не можна видалити. Перейменуйте запис або спершу змініть його в картках.');

            return;
        }
        $record->delete();
        $this->flash = '«'.$record->name.'» видалено.';
    }

    /** Поля з парою «UA | EN» для поточної вкладки. */
    public function fields(): array
    {
        return $this->tab === 'species' ? ['name', 'name_plural'] : ['name'];
    }

    private function modelClass(): string
    {
        return match ($this->tab) {
            'species' => Species::class,
            'breeds' => Breed::class,
            'colors' => Color::class,
            'categories' => PostCategory::class,
        };
    }

    private function usageRelation(): string
    {
        return $this->tab === 'categories' ? 'posts' : 'animals';
    }

    private function query()
    {
        $query = ($this->modelClass())::query();

        return $this->tab === 'breeds' ? $query->where('species_id', $this->speciesFilter) : $query;
    }

    private function rules(string $prefix, ?Model $record = null): array
    {
        $length = ['species' => 50, 'breeds' => 80, 'colors' => 50, 'categories' => 60][$this->tab];
        $table = (new ($this->modelClass()))->getTable();
        $unique = Rule::unique($table, 'name')->ignore($record?->id);
        if ($this->tab === 'breeds') {
            $unique->where('species_id', $this->speciesFilter);
        }

        $rules = [];
        foreach ($this->fields() as $field) {
            $rules["$prefix.$field"] = ['required', 'string', 'max:'.$length];
            $rules["$prefix.{$field}_en"] = ['nullable', 'string', 'max:'.$length];
        }
        // Вид може повторювати назву з іншим словом у множині, тож унікальність перевіряємо для порід, забарвлень і рубрик
        if ($this->tab !== 'species') {
            $rules["$prefix.name"][] = $unique;
        }
        if ($this->tab === 'breeds' && $prefix === 'new') {
            $rules['speciesFilter'] = ['required', Rule::exists('species', 'id')];
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return collect(['edit', 'new'])->flatMap(fn ($p) => [
            "$p.name" => 'назва', "$p.name_en" => 'назва англійською',
            "$p.name_plural" => 'назва в множині', "$p.name_plural_en" => 'назва в множині англійською',
        ])->all() + ['speciesFilter' => 'вид'];
    }

    protected function messages(): array
    {
        return ['*.name.unique' => 'Такий запис уже є.'];
    }

    private function clean(array $data): array
    {
        return array_map(fn ($v) => is_string($v) && trim($v) === '' ? null : (is_string($v) ? trim($v) : $v), $data);
    }

    private function uniqueSlug(string $source): string
    {
        $base = Str::slug(Str::ascii($source)) ?: 'item';
        $slug = $base;
        for ($i = 2; ($this->modelClass())::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    private function resetNew(): void
    {
        $this->new = collect($this->fields())->flatMap(fn ($f) => [$f => '', $f.'_en' => ''])->all();
    }

    public function render()
    {
        $records = $this->query()->withCount($this->usageRelation())
            ->when($this->tab === 'species', fn ($q) => $q->withCount('breeds')->orderBy('id'), fn ($q) => $q->orderBy('name'))
            ->get();

        return view('livewire.admin.dictionaries', [
            'records' => $records,
            'speciesList' => Species::orderBy('id')->get(),
            'usage' => $this->usageRelation().'_count',
        ])->title('Довідники');
    }
}
