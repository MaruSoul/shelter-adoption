<?php

namespace App\Livewire\Admin;

use App\Enums\AnimalStatus;
use App\Enums\PostStatus;
use App\Models\Animal;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PostBlocks;
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
    use WithFileUploads;

    public ?Post $post = null;

    public string $title = '';

    public string $slug = '';

    public string $post_category_id = '';

    public string $excerpt = '';

    public array $blocks = [];

    public bool $preview = false;

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
            $this->excerpt = (string) $this->post->excerpt;
            $this->blocks = $this->post->blocks ?? [];
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
    }

    public function removeListItem(int $index, int $item): void
    {
        unset($this->blocks[$index]['data']['items'][$item]);
        $this->blocks[$index]['data']['items'] = array_values($this->blocks[$index]['data']['items']);
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
            'cover' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ], [], ['title' => 'заголовок', 'slug' => 'адреса', 'post_category_id' => 'рубрика', 'excerpt' => 'анонс', 'cover' => 'обкладинка']);

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
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, (string) $image->encode(new JpegEncoder(quality: 82, strip: true)));

        return $path;
    }

    public function render(PostBlocks $schema)
    {
        return view('livewire.admin.post-editor', [
            'categories' => PostCategory::orderBy('name')->get(),
            'types' => PostBlocks::TYPES,
            'animals' => Animal::where('status', AnimalStatus::Available)->orderBy('name')->get(['id', 'name']),
            'previewBlocks' => $this->preview ? $schema->forRender($this->blocksForPreview()) : [],
        ])->title($this->post->exists ? 'Редагування статті' : 'Нова стаття');
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
