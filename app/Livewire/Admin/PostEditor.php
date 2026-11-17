<?php

namespace App\Livewire\Admin;

use App\Enums\AnimalStatus;
use App\Enums\PostStatus;
use App\Livewire\Concerns\TransliteratesFields;
use App\Models\Animal;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PostBlocks;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Laravel\Facades\Image;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Блоковий конструктор статей (FR-15, погоджене ускладнення).
 * Блоки додаються, редагуються, переставляються й видаляються; зберігаються як JSON за схемою PostBlocks.
 */
class PostEditor extends Component
{
    use TransliteratesFields, WithFileUploads;

    public ?Post $post = null;

    public string $title = '';

    public string $title_en = '';

    public string $slug = '';

    public string $post_category_id = '';

    public string $excerpt = '';

    public string $excerpt_en = '';

    public array $blocks = [];

    public bool $preview = false;

    /** Мова попереднього перегляду: uk або en. */
    public string $previewLocale = 'uk';

    /** Нова обкладинка статті. */
    public $cover;

    /** Завантаження зображень для блоків за їхнім індексом. */
    public array $blockImages = [];

    public function mount(?Post $post = null): void
    {
        $this->post = $post?->exists ? $post : new Post;

        if ($this->post->exists) {
            $this->title = $this->post->title;
            $this->slug = $this->post->slug;
            $this->post_category_id = (string) $this->post->post_category_id;
            $this->title_en = (string) $this->post->title_en;
            $this->excerpt = (string) $this->post->excerpt;
            $this->excerpt_en = (string) $this->post->excerpt_en;
            $this->blocks = PostBlocks::withEnglishKeys($this->post->blocks ?? []);
        } else {
            $this->blocks = [PostBlocks::blank('paragraph')];
        }
    }

    public function updatedTitle(): void
    {
        if (! $this->post->exists || $this->slug === '') {
            $this->slug = Str::slug(Str::ascii($this->title));
        }
    }

    /* ----- Операції з блоками ----- */

    public function addBlock(string $type, ?int $after = null): void
    {
        abort_unless(array_key_exists($type, PostBlocks::TYPES), 422);
        if (count($this->blocks) >= PostBlocks::MAX_BLOCKS) {
            $this->addError('blocks', 'Досягнуто максимум блоків.');

            return;
        }

        $position = $after === null ? count($this->blocks) : $after + 1;
        array_splice($this->blocks, $position, 0, [PostBlocks::blank($type)]);
        $this->blockImages = [];
    }

    public function moveBlock(int $index, int $direction): void
    {
        $target = $index + ($direction < 0 ? -1 : 1);
        if (! isset($this->blocks[$index], $this->blocks[$target])) {
            return;
        }
        [$this->blocks[$index], $this->blocks[$target]] = [$this->blocks[$target], $this->blocks[$index]];
        $this->blockImages = [];
    }

    public function removeBlock(int $index): void
    {
        unset($this->blocks[$index]);
        $this->blocks = array_values($this->blocks);
        $this->blockImages = [];
    }

    public function addListItem(int $index): void
    {
        $this->blocks[$index]['data']['items'][] = '';
        $this->blocks[$index]['data']['items_en'][] = '';
    }

    public function removeListItem(int $index, int $item): void
    {
        foreach (['items', 'items_en'] as $key) {
            unset($this->blocks[$index]['data'][$key][$item]);
            $this->blocks[$index]['data'][$key] = array_values($this->blocks[$index]['data'][$key] ?? []);
        }
    }

    /** Зображення блоку зберігається одразу після вибору файлу. */
    public function updatedBlockImages($value, string $key): void
    {
        $index = (int) $key;
        $this->validate(["blockImages.$index" => ['image', 'mimes:jpeg,jpg,png,webp', 'max:10240']]);

        $this->blocks[$index]['data']['path'] = $this->storeImage($this->blockImages[$index], 1600);
        unset($this->blockImages[$index]);
    }

    /* ----- Збереження ----- */

    public function save(PostBlocks $schema, string $intent = 'draft')
    {
        $data = $this->validate([
            'title' => ['required', 'string', 'min:5', 'max:160'],
            'slug' => ['required', 'alpha_dash', 'max:190', Rule::unique('posts', 'slug')->ignore($this->post->id)],
            'post_category_id' => ['required', Rule::exists('post_categories', 'id')],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'title_en' => ['nullable', 'string', 'max:160'],
            'excerpt_en' => ['nullable', 'string', 'max:300'],
            'cover' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ], [], ['title' => 'заголовок', 'slug' => 'адреса', 'post_category_id' => 'рубрика', 'excerpt' => 'анонс', 'cover' => 'обкладинка',
            'title_en' => 'заголовок англійською', 'excerpt_en' => 'анонс англійською']);

        try {
            $clean = $schema->validate($this->blocks);
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->preview = false;

            return null;
        }

        if ($intent === 'publish' && $clean === []) {
            $this->addError('blocks', 'Не можна опублікувати порожню статтю.');

            return null;
        }

        $post = $this->post;
        $post->fill([
            'title' => trim($data['title']),
            'slug' => $data['slug'],
            'post_category_id' => (int) $data['post_category_id'],
            'excerpt' => trim($data['excerpt']) ?: null,
            'title_en' => trim($data['title_en']) ?: null,
            'excerpt_en' => trim($data['excerpt_en']) ?: null,
            'blocks' => $clean,
        ]);
        if ($this->cover) {
            $post->cover_path = $this->storeImage($this->cover, 1600, 1200, 630);
        }
        $post->author_id ??= auth()->id();

        match ($intent) {
            'publish' => [$post->status = PostStatus::Published, $post->published_at ??= now()],
            'unpublish' => [$post->status = PostStatus::Draft, $post->published_at = null],
            default => $post->status ??= PostStatus::Draft,
        };
        $post->save();

        session()->flash('status', match ($intent) {
            'publish' => 'Статтю опубліковано.',
            'unpublish' => 'Статтю знято з публікації.',
            default => 'Чернетку збережено.',
        });

        return $this->redirectRoute('admin.posts.edit', $post);
    }

    private function storeImage($file, int $maxSide, ?int $coverWidth = null, ?int $coverHeight = null): string
    {
        $image = Image::decodePath($file->getRealPath())->orient();
        $image = $coverWidth ? $image->cover($coverWidth, $coverHeight) : $image->scaleDown($maxSide, $maxSide);

        $path = 'posts/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, (string) $image->encode(new JpegEncoder(quality: 82, strip: true)));

        return $path;
    }

    public function render(PostBlocks $schema)
    {
        return view('livewire.admin.post-editor', [
            'categories' => PostCategory::orderBy('name')->get(),
            'types' => PostBlocks::TYPES,
            'animals' => Animal::where('status', AnimalStatus::Available)->orderBy('name')->get(['id', 'name']),
            'previewHtml' => $this->preview ? $this->renderPreview($schema) : null,
            'previewGaps' => $this->preview && $this->previewLocale === 'en' ? $this->previewGaps() : 0,
        ])->title($this->post->exists ? 'Редагування статті' : 'Нова стаття');
    }

    protected function canTransliterate(string $field): bool
    {
        return (bool) preg_match('/^blocks\.\d+\.data\.author$/', $field);
    }

    /**
     * Попередній перегляд обраною мовою. Рендериться з тимчасово перемкненою мовою застосунку,
     * щоб і вбудовані картки тварин виглядали так, як їх побачить відвідувач англійської версії.
     */
    private function renderPreview(PostBlocks $schema): string
    {
        $locale = $this->previewLocale === 'en' ? 'en' : 'uk';
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            $title = $locale === 'en' && trim($this->title_en) !== '' ? $this->title_en : $this->title;

            return view('livewire.admin.post-preview', [
                'title' => $title,
                'blocks' => $schema->forRender(PostBlocks::localize($this->blocksForPreview(), $locale)),
            ])->render();
        } finally {
            app()->setLocale($previous);
        }
    }

    /** Скільки фрагментів на англійському перегляді ще показано українською. */
    private function previewGaps(): int
    {
        [$done, $total] = PostBlocks::englishCoverage($this->blocksForPreview());

        return ($total - $done) + (int) (trim($this->title) !== '' && trim($this->title_en) === '');
    }

    /** Для попереднього перегляду беремо лише заповнені блоки. */
    private function blocksForPreview(): array
    {
        return array_values(array_filter($this->blocks, function ($b) {
            $d = $b['data'] ?? [];

            return match ($b['type']) {
                'image' => ! empty($d['path']),
                'animal' => ! empty($d['animal_id']),
                'list' => count(array_filter($d['items'] ?? [])) > 0,
                default => trim($d['text'] ?? '') !== '',
            };
        }));
    }
}
