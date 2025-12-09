<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;

test('posts can be created', function() {
    auth()->loginUsingId(1);

    $this->withoutExceptionHandling()->post(tenant()->route('tenant.posts.store'), [
        'title' => 'Foo',
        'body' => 'Bar',
    ]);

    expect(Post::where('title', 'Foo')->where('body', 'Bar')->exists())->toBeTrue();
});

test('posts appear on the post index', function() {
    auth()->loginUsingId(1);

    auth()->user()->posts()->create([
        'title' => 'Foo post',
        'body' => 'Bar',
    ]);

    $this->withoutExceptionHandling()->get(tenant()->route('tenant.posts.index'))
        ->assertSee('Foo post');
});

test('each post has a detail page', function() {
    auth()->loginUsingId(1);

    $this->withoutExceptionHandling()->post(tenant()->route('tenant.posts.store'), [
        'title' => 'Foo post',
        'body' => 'Bar',
    ]);

    $this->withoutExceptionHandling()->get(tenant()->route('tenant.posts.show', ['post' => Post::firstWhere('title', 'Foo post')]))
        ->assertSee('Foo post');
});

test('posts can be deleted by their author or by the app owner', function() {
    // Delete the sample posts created by the tenant db seeder
    Post::all()->each->delete();

    $user = User::factory()->create();
    $user2 = User::factory()->create();

    $post = $user->posts()->create([
        'title' => 'Foo post',
        'body' => 'Foo',
    ]);

    $post2 = $user->posts()->create([
        'title' => 'Bar post',
        'body' => 'Bar',
    ]);

    expect(Post::count())->toBe(2);

    $this->actingAs($user2)->post(tenant()->route('tenant.posts.delete', ['post' => $post]));

    // Post wasn't deleted -- the user wasn't the author or the owner
    expect(Post::count())->toBe(2);

    $this->actingAs($user)->post(tenant()->route('tenant.posts.delete', ['post' => $post]));

    // Post was deleted -- the user was the author
    expect(Post::count())->toBe(1);

    session()->flush();

    $this->actingAs(User::first())->post(tenant()->route('tenant.posts.delete', ['post' => $post2]));

    // Post was deleted -- the user was the app owner
    expect(Post::count())->toBe(0);
});
