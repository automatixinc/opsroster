<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Features\UserImpersonation;
use App\Http\Controllers\Tenant as Controllers;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\OwnerOnly;

Route::middleware([
    'web',
    \Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain::class,
    \Stancl\Tenancy\Middleware\PreventAccessFromUnwantedDomains::class,
    \Stancl\Tenancy\Middleware\ScopeSessions::class,
])->group(function () {
    Route::name('tenant.')
        ->group(function () {
            Route::group([
                'namespace' => 'Laravel\Fortify\Http\Controllers',
                'domain' => config('fortify.domain', null),
                'prefix' => config('fortify.prefix'),
            ], base_path('vendor/laravel/fortify/routes/routes.php'));

            Route::group([
                'namespace' => 'Laravel\Jetstream\Http\Controllers\Livewire'
            ], base_path('vendor/laravel/jetstream/routes/livewire.php'));

            Route::get('/impersonate/{token}', function ($token) {
                return UserImpersonation::makeResponse($token);
            })->name('impersonate');

            Route::post('/ploi/webhook/certificateIssued', [Controllers\PloiWebhookController::class, 'certificateIssued'])->name('ploi.certificate.issued');
            Route::post('/ploi/webhook/certificateRevoked', [Controllers\PloiWebhookController::class, 'certificateRevoked'])->name('ploi.certificate.revoked');

            Route::get('/', function () {
                return view('welcome');
            })->name('home');

            Route::middleware([
                'auth:sanctum',
                config('jetstream.auth_session'),
                'verified',
            ])->group(function () {
                Route::get('/dashboard', function () {
                    return view('dashboard');
                })->name('dashboard');

                Route::middleware(CheckSubscription::class)->group(function () {
                    Route::get('/posts', [Controllers\PostController::class, 'index'])->name('posts.index');
                    Route::post('/posts', [Controllers\PostController::class, 'store'])->name('posts.store');
                    Route::get('/posts/create', [Controllers\PostController::class, 'create'])->name('posts.create');
                    Route::get('/posts/{post}', [Controllers\PostController::class, 'show'])->name('posts.show');
                    Route::post('/posts/delete/{post}', [Controllers\PostController::class, 'destroy'])->name('posts.delete');
                });

                Route::middleware(OwnerOnly::class)->group(function () {
                    Route::get('/settings/billing', [Controllers\BillingController::class, 'show'])->name('settings.billing');
                    Route::get('/settings/billing/invoice/{id}/download', [Controllers\BillingController::class, 'downloadInvoice'])->name('invoice.download');
                    Route::post('/settings/billing/update-address', [Controllers\BillingController::class, 'updateAddress'])->name('settings.billing.update-address');

                    Route::get('/settings/application', [Controllers\ApplicationSettingsController::class, 'show'])->name('settings.application');
                });
            });
        });
});
