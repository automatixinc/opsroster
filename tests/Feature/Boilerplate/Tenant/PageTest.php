<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::first());
});

test('welcome page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('tenant.home'))->assertOk();
});

test('dashboard is accessible', function() {
    $this->withoutExceptionHandling()->get(route('tenant.dashboard'))->assertOk();
});

test('post index page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('tenant.posts.index'))->assertOk();
});

test('post create page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('tenant.posts.create'))->assertOk();
});

test('post show page is accessible', function() {
    $post = User::first()->posts()->create([
        'title' => 'Post',
        'body' => 'Body',
    ]);

    $this->withoutExceptionHandling()->get(route('tenant.posts.show', $post))->assertOk();
});

test('billing page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('tenant.settings.billing'))->assertOk();
});

test('application settings page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('tenant.settings.application'))->assertOk();
});
