<div>
    <x-admin-nav />
    <div class="mx-auto max-w-6xl px-6 py-8 flex flex-col gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h1 class="text-4xl">Статті</h1>
            <a href="{{ route('admin.posts.create') }}" class="btn-primary">Нова стаття</a>
        </div>
        <div class="card overflow-x-auto">
            <table class="w-full text-left">
                <thead class="text-sm text-muted border-b border-line"><tr><th class="p-4 font-semibold">Заголовок</th><th class="p-4 font-semibold">Рубрика</th><th class="p-4 font-semibold">Статус</th><th class="p-4 font-semibold">Оновлено</th></tr></thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr class="border-b border-line last:border-0 hover:bg-bg" wire:key="p-{{ $post->id }}">
                            <td class="p-4"><a href="{{ route('admin.posts.edit', $post) }}" class="font-semibold text-primary hover:underline">{{ $post->title }}</a></td>
                            <td class="p-4">{{ $post->category->name }}</td>
                            <td class="p-4"><span @class(['chip', 'bg-success-soft text-success' => $post->isPublished(), 'bg-sun text-sun-ink' => ! $post->isPublished()])>{{ $post->status->label() }}</span></td>
                            <td class="p-4 text-sm text-muted">{{ $post->updated_at->format('d.m.Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-8 text-center text-muted">Статей ще немає.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $posts->links('components.pagination') }}
    </div>
</div>
