<?php

namespace App\Livewire\Admin;

use App\Enums\DonationType;
use App\Models\DonationMethod;
use App\Models\ShelterSetting;
use App\Rules\Iban;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Розділ «Допомога»: вступний текст, способи фінансової допомоги й посилання на звіти.
 *
 * Поля форми й перевірки беруться з App\Enums\DonationType, тому компонент не знає про конкретні
 * способи: новий спосіб оплати зʼявиться тут автоматично.
 */
class Donations extends Component
{
    /** Спосіб, що редагується: null – форма закрита, 0 – новий. */
    public ?int $editingId = null;

    public string $type = '';

    /** Спільні двомовні поля: назва вкладки й короткий опис. */
    public array $form = ['title' => '', 'title_en' => '', 'note' => '', 'note_en' => ''];

    /** Поля конкретного способу (IBAN, посилання…). */
    public array $details = [];

    /** Вступний текст і посилання на звіти. */
    public array $page = ['help_text' => '', 'help_text_en' => '', 'reports_url' => ''];

    public ?string $flash = null;

    public function mount(): void
    {
        $settings = ShelterSetting::current();
        $this->page = [
            'help_text' => (string) $settings->help_text,
            'help_text_en' => (string) $settings->help_text_en,
            'reports_url' => (string) $settings->reports_url,
        ];
    }

    #[Computed]
    public function methods()
    {
        return DonationMethod::query()->orderBy('position')->orderBy('id')->get();
    }

    public function create(string $type): void
    {
        $this->openForm(null, DonationType::from($type));
    }

    public function edit(int $id): void
    {
        $method = DonationMethod::findOrFail($id);
        $this->openForm($method, $method->type);
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $type = DonationType::from($this->type);

        $rules = [
            'form.title' => [$type->requiresTitle() ? 'required' : 'nullable', 'string', 'max:80'],
            'form.title_en' => ['nullable', 'string', 'max:80'],
            'form.note' => ['nullable', 'string', 'max:500'],
            'form.note_en' => ['nullable', 'string', 'max:500'],
        ];
        $attributes = ['form.title' => 'назва вкладки', 'form.title_en' => 'назва вкладки англійською', 'form.note' => 'опис', 'form.note_en' => 'опис англійською'];
        foreach ($type->fields() as $field) {
            $rules['details.'.$field->key] = $field->validationRules();
            $attributes['details.'.$field->key] = mb_strtolower($field->label);
        }
        $this->validate($rules, [], $attributes);

        $method = $this->editingId ? DonationMethod::findOrFail($this->editingId) : new DonationMethod([
            'type' => $type,
            'position' => (int) DonationMethod::max('position') + 1,
            'is_visible' => true,
        ]);
        $method->fill(array_map(fn ($v) => trim($v) === '' ? null : trim($v), $this->form));
        $method->details = $type->normalize(array_intersect_key($this->details, array_flip(array_map(fn ($f) => $f->key, $type->fields()))));
        $method->save();

        $this->editingId = null;
        $this->flash = 'Спосіб «'.$method->tabTitle().'» збережено.';
        unset($this->methods);
    }

    public function toggle(int $id): void
    {
        $method = DonationMethod::findOrFail($id);
        $method->update(['is_visible' => ! $method->is_visible]);
        $this->flash = $method->is_visible ? '«'.$method->tabTitle().'» знову показується на сайті.' : '«'.$method->tabTitle().'» сховано з сайту.';
        unset($this->methods);
    }

    /** Переміщує спосіб на одну позицію вгору (-1) чи вниз (1). */
    public function move(int $id, int $direction): void
    {
        $ids = $this->methods->pluck('id')->all();
        $from = array_search($id, $ids, true);
        $to = $from === false ? false : $from + ($direction < 0 ? -1 : 1);
        if ($from === false || ! isset($ids[$to])) {
            return;
        }
        [$ids[$from], $ids[$to]] = [$ids[$to], $ids[$from]];

        DB::transaction(function () use ($ids) {
            foreach ($ids as $position => $methodId) {
                DonationMethod::whereKey($methodId)->update(['position' => $position]);
            }
        });
        unset($this->methods);
    }

    public function delete(int $id): void
    {
        $method = DonationMethod::findOrFail($id);
        $method->delete();
        $this->flash = '«'.$method->tabTitle().'» видалено.';
        if ($this->editingId === $id) {
            $this->editingId = null;
        }
        unset($this->methods);
    }

    public function savePage(): void
    {
        $data = $this->validate([
            'page.help_text' => ['nullable', 'string', 'max:3000'],
            'page.help_text_en' => ['nullable', 'string', 'max:3000'],
            'page.reports_url' => ['nullable', 'url:https', 'max:255'],
        ], [], ['page.help_text' => 'вступний текст', 'page.reports_url' => 'посилання на звіти'])['page'];

        $settings = ShelterSetting::query()->first() ?? new ShelterSetting([
            'name' => config('app.name'), 'notification_email' => config('mail.from.address'),
        ]);
        $settings->fill(array_map(fn ($v) => trim((string) $v) === '' ? null : trim($v), $data))->save();
        $this->flash = 'Текст і посилання на звіти збережено.';
    }

    private function openForm(?DonationMethod $method, DonationType $type): void
    {
        $this->resetValidation();
        $this->flash = null;
        $this->editingId = $method?->id ?? 0;
        $this->type = $type->value;
        $this->form = collect(['title', 'title_en', 'note', 'note_en'])->mapWithKeys(fn ($f) => [$f => (string) $method?->{$f}])->all();
        // IBAN у формі – групами по 4 символи, як у банківських застосунках (зберігається без пробілів)
        $this->details = collect($type->fields())->mapWithKeys(function ($f) use ($method) {
            $value = (string) ($method?->details[$f->key] ?? '');

            return [$f->key => $f->key === 'iban' && $value !== '' ? Iban::format($value) : $value];
        })->all();
    }

    public function render()
    {
        return view('livewire.admin.donations', [
            'types' => DonationType::cases(),
            'editingType' => $this->type !== '' ? DonationType::from($this->type) : null,
        ])->title('Допомога');
    }
}
