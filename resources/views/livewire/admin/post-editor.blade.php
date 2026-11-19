<div>
    <x-admin-nav />
    <div class="mx-auto max-w-5xl px-6 py-8 flex flex-col gap-6" data-bi-scope>
        <a href="{{ route('admin.posts.index') }}" class="font-semibold text-primary hover:underline">← Усі статті</a>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <h1 class="text-4xl">{{ $post->exists ? 'Редагування статті' : 'Нова стаття' }}</h1>
                @if ($post->exists)<span @class(['chip', 'bg-success-soft text-success' => $post->isPublished(), 'bg-sun-soft text-accent' => ! $post->isPublished()])>{{ $post->status->label() }}</span>@endif
            </div>
            <div class="flex gap-1 rounded-full bg-surface p-1" role="tablist">
                <button type="button" role="tab" aria-selected="{{ $preview ? 'false' : 'true' }}" wire:click="$set('preview', false)" @class(['btn min-h-10', 'bg-card shadow-sm' => ! $preview, 'text-muted' => $preview])>Редактор</button>
                <button type="button" role="tab" aria-selected="{{ $preview ? 'true' : 'false' }}" wire:click="$set('preview', true)" @class(['btn min-h-10', 'bg-card shadow-sm' => $preview, 'text-muted' => ! $preview])>Попередній перегляд</button>
            </div>
        </div>

        @if ($errors->isNotEmpty())
            <div role="alert" class="rounded-2xl bg-danger-soft text-danger px-4 py-3 font-medium">Перевірте позначені поля – статтю не збережено.</div>
        @endif

        @if ($preview)
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-semibold">Мова перегляду:</span>
                <div class="flex gap-1 rounded-full bg-surface p-1" role="radiogroup" aria-label="Мова перегляду">
                    @foreach (['uk' => 'Українська', 'en' => 'English'] as $code => $name)
                        <button type="button" role="radio" aria-checked="{{ $previewLocale === $code ? 'true' : 'false' }}" wire:click="$set('previewLocale', '{{ $code }}')"
                                @class(['btn min-h-9', 'bg-card shadow-sm' => $previewLocale === $code, 'text-muted' => $previewLocale !== $code])>{{ $name }}</button>
                    @endforeach
                </div>
                @if ($previewGaps > 0)
                    <span class="chip bg-sun-soft text-accent">Без перекладу: {{ $previewGaps }} – відвідувачі побачать ці фрагменти українською</span>
                @endif
            </div>
            {!! $previewHtml !!}
        @else
            <section class="card p-6 flex flex-col gap-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-muted max-w-xl">Структура статті одна для обох мов: блоки, фото й картки тварин спільні, двома мовами пишеться лише текст.</p>
                    <x-translation-meter />
                </div>
                <x-bilingual label="Заголовок" model="title" required live input-class="text-lg" />
                <div class="grid gap-4 grid-cols-[repeat(auto-fit,minmax(220px,1fr))]">
                    <label class="label">Адреса сторінки *
                        <span class="flex items-center gap-1 font-normal"><span class="text-muted text-sm">/blog/</span><input type="text" wire:model="slug" class="field font-normal"></span>
                        @error('slug')<span class="error">{{ $message }}</span>@enderror
                    </label>
                    <label class="label">Рубрика *
                        <select wire:model="post_category_id" class="field font-normal">
                            <option value="">Оберіть</option>
                            @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                        @error('post_category_id')<span class="error">{{ $message }}</span>@enderror
                    </label>
                </div>
                <x-bilingual label="Анонс" model="excerpt" type="textarea" :rows="2" hint="показується в картці статті й у пошуковиках" />
                <div class="flex flex-wrap items-center gap-4">
                    {{-- Нова обкладинка показується одразу, до збереження статті --}}
                    @if ($cover && method_exists($cover, 'isPreviewable') && $cover->isPreviewable())
                        <span class="relative">
                            <img src="{{ $cover->temporaryUrl() }}" alt="Нова обкладинка" class="h-24 aspect-[1200/630] object-cover rounded-xl border-2 border-dashed border-primary/40">
                            <span class="chip absolute right-1.5 top-1.5 bg-primary text-primary-ink text-xs">Нова</span>
                        </span>
                        <button type="button" wire:click="$set('cover', null)" class="btn-ghost text-danger">Скасувати</button>
                    @elseif ($post->coverUrl())
                        <img src="{{ $post->coverUrl() }}" alt="" class="h-24 aspect-[1200/630] object-cover rounded-xl">
                    @endif
                    <label class="btn-outline cursor-pointer">{{ $post->cover_path || $cover ? 'Замінити обкладинку' : 'Додати обкладинку' }}
                        <input type="file" wire:model="cover" accept="image/jpeg,image/png,image/webp" class="sr-only">
                    </label>
                    <span wire:loading wire:target="cover" class="text-sm text-muted">Завантажуємо…</span>
                    @error('cover')<span class="error">{{ $message }}</span>@enderror
                </div>
            </section>

            <section class="flex flex-col gap-3" aria-label="Блоки статті">
                @error('blocks')<p class="error">{{ $message }}</p>@enderror
                @foreach ($blocks as $i => $block)
                    <div class="card p-5 flex flex-col gap-3" wire:key="block-{{ $i }}-{{ $block['type'] }}">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="chip bg-surface text-primary">{{ $types[$block['type']] }}</span>
                            <div class="flex gap-1">
                                <button type="button" wire:click="moveBlock({{ $i }}, -1)" class="btn-ghost px-3 min-h-9" aria-label="Перемістити вгору" @disabled($i === 0)>↑</button>
                                <button type="button" wire:click="moveBlock({{ $i }}, 1)" class="btn-ghost px-3 min-h-9" aria-label="Перемістити вниз" @disabled($loop->last)>↓</button>
                                <button type="button" wire:click="removeBlock({{ $i }})" wire:confirm="Видалити блок?" class="btn-ghost px-3 min-h-9 text-danger">Видалити</button>
                            </div>
                        </div>

                        @switch($block['type'])
                            @case('heading')
                                <x-bilingual model="blocks.{{ $i }}.data.text" placeholder="Текст заголовка" input-class="font-display font-bold text-xl" />
                                <select wire:model="blocks.{{ $i }}.data.level" class="field w-auto self-start" aria-label="Рівень заголовка"><option value="2">Розділ</option><option value="3">Підрозділ</option></select>
                                @break
                            @case('paragraph')
                                <x-bilingual model="blocks.{{ $i }}.data.text" type="textarea" :rows="5" placeholder="Текст абзацу" />
                                <p class="text-sm text-muted">{{ \App\Support\SimpleMarkup::HINT }}. Внутрішні посилання на тварин і статті допомагають сайту в пошуку.</p>
                                @break
                            @case('quote')
                                <x-bilingual model="blocks.{{ $i }}.data.text" type="textarea" :rows="2" placeholder="Цитата" />
                                <x-bilingual model="blocks.{{ $i }}.data.author" placeholder="Автор (необовʼязково)" compact translit optional />
                                @break
                            @case('list')
                                <select wire:model="blocks.{{ $i }}.data.style" class="field w-auto self-start" aria-label="Вигляд списку"><option value="bullet">Маркований</option><option value="ordered">Нумерований</option></select>
                                <div class="hidden md:grid grid-cols-2 gap-3 pr-12 text-sm font-semibold text-muted" aria-hidden="true">
                                    <span class="bi-tag"><span class="bi-flag">UA</span>Українською</span>
                                    <span class="bi-tag"><span class="bi-flag bi-flag-en">EN</span>Англійською</span>
                                </div>
                                @foreach ($block['data']['items'] as $j => $item)
                                    <div class="flex gap-2 items-start" wire:key="b{{ $i }}-item-{{ $j }}">
                                        <x-bilingual model="blocks.{{ $i }}.data.items.{{ $j }}" en-model="blocks.{{ $i }}.data.items_en.{{ $j }}" placeholder="Пункт {{ $j + 1 }}" compact class="grow" />
                                        <button type="button" wire:click="removeListItem({{ $i }}, {{ $j }})" class="btn-ghost px-3 text-danger" aria-label="Видалити пункт {{ $j + 1 }}">×</button>
                                    </div>
                                @endforeach
                                <button type="button" wire:click="addListItem({{ $i }})" class="btn-ghost self-start">+ Пункт</button>
                                @break
                            @case('image')
                                @php($pending = $blockImages[$i] ?? null)
                                @if ($pending && method_exists($pending, 'isPreviewable') && $pending->isPreviewable())
                                    <img src="{{ $pending->temporaryUrl() }}" alt="" class="max-h-64 rounded-2xl self-start border-2 border-dashed border-primary/40">
                                @elseif (! empty($block['data']['path']))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($block['data']['path']) }}" alt="" class="max-h-64 rounded-2xl self-start">
                                @endif
                                <span wire:loading wire:target="blockImages.{{ $i }}" class="text-sm text-muted">Завантажуємо…</span>
                                <label class="btn-outline cursor-pointer self-start">{{ empty($block['data']['path']) ? 'Обрати зображення' : 'Замінити' }}
                                    <input type="file" wire:model="blockImages.{{ $i }}" accept="image/jpeg,image/png,image/webp" class="sr-only">
                                </label>
                                @error('blockImages.'.$i)<span class="error">{{ $message }}</span>@enderror
                                <x-bilingual model="blocks.{{ $i }}.data.alt" placeholder="Опис зображення для незрячих *" compact />
                                <x-bilingual model="blocks.{{ $i }}.data.caption" placeholder="Підпис (необовʼязково)" compact />
                                @break
                            @case('animal')
                                <select wire:model="blocks.{{ $i }}.data.animal_id" class="field" aria-label="Тварина">
                                    <option value="">Оберіть тварину з каталогу</option>
                                    @foreach ($animals as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
                                </select>
                                <p class="text-sm text-muted">Картка тварини перекладається автоматично з її сторінки.</p>
                                @break
                            @case('video')
                                <input type="url" wire:model.live.blur="blocks.{{ $i }}.data.url" class="field" inputmode="url"
                                       placeholder="https://www.youtube.com/watch?v=…" aria-label="Посилання на відео">
                                <p class="text-sm text-muted">Скопіюйте адресу відео з YouTube (звичайне відео або Shorts) чи Vimeo – вставку сайт зробить сам.</p>
                                @if ($video = \App\Services\PostBlocks::parseVideo($block['data']['url'] ?? ''))
                                    @if ($video['thumb'])<img src="{{ $video['thumb'] }}" alt="" class="max-h-40 rounded-2xl self-start">@endif
                                @endif
                                <x-bilingual model="blocks.{{ $i }}.data.caption" placeholder="Підпис (необовʼязково)" compact />
                                @break
                        @endswitch

                        {{-- Помилки текстових полів показує сама пара UA/EN; тут – решта (зображення, тварина, список) --}}
                        @foreach ($errors->getMessages() as $key => $messages)
                            @if (preg_match('/^blocks\.'.$i.'\.data\.(path|animal_id|items|level|style|url)$/', $key))
                                @foreach ($messages as $message)<span class="error">{{ $message }}</span>@endforeach
                            @endif
                        @endforeach
                        @error('blocks.'.$i)<span class="error">{{ $message }}</span>@enderror
                    </div>
                @endforeach

                <div class="rounded-3xl border-2 border-dashed border-line p-4 flex flex-wrap items-center gap-2">
                    <span class="font-semibold mr-2">Додати блок:</span>
                    @foreach ($types as $type => $label)
                        <button type="button" wire:click="addBlock('{{ $type }}')" class="btn border-[1.5px] border-line bg-card min-h-10">{{ $label }}</button>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="flex flex-wrap items-center gap-3 sticky bottom-0 bg-bg/90 backdrop-blur py-4 border-t border-line">
            <button type="button" wire:click="save('draft')" class="btn-outline min-h-12">Зберегти чернетку</button>
            @if ($post->exists && $post->isPublished())
                <button type="button" wire:click="save('publish')" class="btn-primary min-h-12">Зберегти</button>
                <button type="button" wire:click="save('unpublish')" class="btn-ghost min-h-12">Зняти з публікації</button>
                <a href="{{ route('blog.show', $post) }}" target="_blank" class="btn-ghost min-h-12">Відкрити на сайті ↗</a>
            @else
                <button type="button" wire:click="save('publish')" class="btn-primary min-h-12">Опублікувати</button>
            @endif
            @unless ($preview)<x-translation-meter class="ml-auto" />@endunless
        </div>
    </div>
</div>
