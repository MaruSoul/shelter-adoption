<?php

namespace App\Livewire\Admin;

use App\Models\Post;
use Livewire\Component;
use Livewire\WithPagination;

class PostIndex extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.admin.post-index', [
            'posts' => Post::with('category')->latest('updated_at')->paginate(20),
        ])->title('Статті');
    }
}
