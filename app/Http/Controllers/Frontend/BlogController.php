<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Theme;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $theme = Theme::first();
        $posts = BlogPost::where('is_published', true)
            ->latest()
            ->paginate(12);

        $store = Store::first();
        $meta = (object) [
            'title' => 'Blog - ' . ($store->name ?? config('app.name')),
            'description' => $store->meta_description ?? null,
        ];

        return view($theme->thene . '.pages.blog.index', [
            'posts' => $posts,
            'meta' => $meta
        ]);
    }

    public function show(int $id, ?string $slug = null): View|\Illuminate\Http\RedirectResponse
    {
        $post = BlogPost::where('is_published', true)->findOrFail($id);

        if ($slug !== $post->slug) {
            return redirect()->route('blog.show', [$post->id, $post->slug]);
        }

        $theme = Theme::first();
        $store = Store::first();
        
        $meta = (object) [
            'title' => $post->title . ' - ' . ($store->name ?? config('app.name')),
            'description' => $post->excerpt ?? $store->meta_description ?? null,
        ];

        return view($theme->thene . '.pages.blog.show', [
            'post' => $post,
            'meta' => $meta
        ]);
    }
}

