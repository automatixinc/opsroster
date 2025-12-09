<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class PostController
{
    public function index()
    {
        return view('tenant.posts.index', [
            'posts' => Post::cursor(),
        ]);
    }

    public function show(Post $post)
    {
        return view('tenant.posts.show', [
            'post' => $post,
            'userCanDeletePost' => $post->author->is(auth()->user()) || auth()->user()?->is_owner
        ]);
    }

    public function create()
    {
        return view('tenant.posts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:256',
            'body' => 'required|string|max:2048',
        ]);

        /** @var User $user */
        $user = auth()->user();

        $post = $user->posts()->create($validated);

        return redirect(route('tenant.posts.show', [
            'post' => $post,
        ]));
    }

    public function destroy(Post $post)
    {
        if ($post->author->is(auth()->user()) || auth()->user()?->is_owner) {
            $post->delete();

            return redirect()->route('tenant.posts.index');
        }
    }
}
