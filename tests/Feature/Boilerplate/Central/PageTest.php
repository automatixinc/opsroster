<?php

declare(strict_types=1);

test('landing page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('home'))->assertOk();
});

test('register page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('central.register'))->assertOk();
});

test('login page is accessible', function() {
    $this->withoutExceptionHandling()->get(route('central.login'))->assertOk();
});
