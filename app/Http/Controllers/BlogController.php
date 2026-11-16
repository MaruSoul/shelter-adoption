<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PostBlocks;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->filled('rubric')
            ? PostCategory::where('slug', $request->string('rubric'))->first()
            : null;

        $posts = Post::published()
            ->with('category')
            ->when($category, fn ($q) => $q->where('post_category_id', $category->id))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'categories' => PostCategory::whereHas('posts', fn ($q) => $q->published())->orderBy('name')->get(),
            'current' => $category,
        ]);
    }

    /** Чернетка за прямою адресою – 404 для всіх, крім адміністратора (FR-14). */
    public function show(Request $request, Post $post)
    {
        if (! $post->isPublished() && ! $request->user()?->isAdmin()) {
            abort(404);
        }

        $post->load('category');

        return view('blog.show', [
            'post' => $post,
            'blocks' => app(PostBlocks::class)->forRender($post->blocks ?? []),
            'related' => $this->related($post),
        ]);
    }

    /**
     * «Читайте також»: спершу статті тієї ж рубрики, потім найсвіжіші з інших – разом до трьох.
     *
     * @return Collection<int, Post>
     */
    private function related(Post $post)
    {
        $sameRubric = Post::published()->with('category')
            ->where('post_category_id', $post->post_category_id)
            ->whereKeyNot($post->id)
            ->latest('published_at')->take(3)->get();

        $others = Post::published()->with('category')
            ->whereKeyNot([$post->id, ...$sameRubric->modelKeys()])
            ->latest('published_at')->take(3 - $sameRubric->count())->get();

        return $sameRubric->concat($others);
    }
}
