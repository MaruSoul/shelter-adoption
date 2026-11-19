<?php

namespace App\Livewire\Admin;

use App\Enums\DictionaryType;
use App\Models\Species;
use App\Support\Ukrainian;
use Illuminate\Database\Eloquent\Builder;
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
 *
 * Відмінності між довідниками описано в App\Enums\DictionaryType; тут – лише спільні операції.
 */
class Dictionaries extends Component
{
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
        $this->tab = $this->type()->value;
        $this->speciesFilter ??= Species::orderBy('id')->value('id');
        $this->resetNew();
    }

    public function updatedTab(): void
    {
        $this->tab = $this->type()->value;
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

    /** Поточний довідник; невідома вкладка з адреси стає «Видами тварин». */
    public function type(): DictionaryType
    {
        return DictionaryType::tryFrom($this->tab) ?? DictionaryType::Species;
    }

    public function startEdit(int $id): void
    {
        $record = $this->query()->findOrFail($id);
        $this->resetValidation();
        $this->edit = ['id' => $record->id] + collect($this->type()->fields())
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
        $type = $this->type();
        $data = $this->clean($this->validate($this->rules('new'))['new']);
        $model = $type->model();
        $record = new $model($data);

        if ($type->isScopedBySpecies()) {
            $record->species_id = $this->speciesFilter;
        }
        if ($type->hasSlug()) {
            $record->slug = $this->uniqueSlug($data['name_en'] ?? null ?: Ukrainian::transliterate($data['name']));
        }
        $record->save();

        $this->resetNew();
        $this->flash = '«'.$record->name.'» додано.';
    }

    public function delete(int $id): void
    {
        $usage = $this->type()->usageRelation();
        $record = $this->query()->withCount($usage)->findOrFail($id);
        if ($record->{$usage.'_count'} > 0) {
            $this->flash = null;
            $this->addError('delete', '«'.$record->name.'» використовується, тому його не можна видалити. Перейменуйте запис або спершу змініть його в картках.');

            return;
        }
        $record->delete();
        $this->flash = '«'.$record->name.'» видалено.';
    }

    private function query(): Builder
    {
        $type = $this->type();
        $query = ($type->model())::query();

        return $type->isScopedBySpecies() ? $query->where('species_id', $this->speciesFilter) : $query;
    }

    private function rules(string $prefix, ?Model $record = null): array
    {
        $type = $this->type();
        $rules = [];
        foreach ($type->fields() as $field) {
            $rules["$prefix.$field"] = ['required', 'string', 'max:'.$type->maxLength()];
            $rules["$prefix.{$field}_en"] = ['nullable', 'string', 'max:'.$type->maxLength()];
        }
        if ($type->hasUniqueName()) {
            $unique = Rule::unique((new ($type->model()))->getTable(), 'name')->ignore($record?->id);
            if ($type->isScopedBySpecies()) {
                $unique->where('species_id', $this->speciesFilter);
            }
            $rules["$prefix.name"][] = $unique;
        }
        if ($type->isScopedBySpecies() && $prefix === 'new') {
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
        $model = $this->type()->model();
        $base = Str::slug(Str::ascii($source)) ?: 'item';
        $slug = $base;
        for ($i = 2; $model::where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    private function resetNew(): void
    {
        $this->new = collect($this->type()->fields())->flatMap(fn ($f) => [$f => '', $f.'_en' => ''])->all();
    }

    public function render()
    {
        $type = $this->type();
        $records = $this->query()->withCount($type->usageRelation())
            ->when($type->hasBreeds(), fn ($q) => $q->withCount('breeds')->orderBy('id'), fn ($q) => $q->orderBy('name'))
            ->get();

        return view('livewire.admin.dictionaries', [
            'type' => $type,
            'records' => $records,
            'speciesList' => Species::orderBy('id')->get(),
            'usage' => $type->usageRelation().'_count',
        ])->title('Довідники');
    }
}
