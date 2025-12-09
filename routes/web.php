<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central as Controllers;
use App\Http\Controllers\Admin;
use App\Livewire\Admin\Tenants\Edit;

foreach (config('tenancy.identification.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::view('/', 'central.landing')->name('home');

        Route::name('central.')->group(function () {
            Route::get('/register', [Controllers\RegisterTenantController::class, 'show'])->name('register');
            Route::post('/register/submit', [Controllers\RegisterTenantController::class, 'submit'])->name('register.submit')
                ->middleware('throttle:create-tenant');

            Route::get('/login', [Controllers\LoginTenantController::class, 'show'])->name('login');
            Route::post('/login/submit', [Controllers\LoginTenantController::class, 'submit'])->name('login.submit');
        });

        Route::name('admin.')->group(function () {
            Route::redirect('/admin', '/admin/login');
            Route::get('/admin/login', [Admin\AuthController::class, 'show'])->name('login');
            Route::post('/admin/login/submit', [Admin\AuthController::class, 'login'])->name('login.submit');

            Route::middleware('auth:admin')->group(function () {
                Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');

                Route::name('tenants.')->group(function () {
                    Route::get('/admin/tenants', [Admin\TenantController::class, 'index'])->name('index');
                    Route::get('/admin/tenants/create', [Admin\TenantController::class, 'create'])->name('create');
                    Route::post('/admin/tenants/store', [Admin\TenantController::class, 'store'])->name('store');
                    Route::get('/admin/tenants/{tenant}/edit', Edit::class)->name('edit');
                    Route::put('/admin/tenants/{tenant}/update', [Admin\TenantController::class, 'update'])->name('update');
                    Route::get('/admin/tenants/{tenant}/destroy', [Admin\TenantController::class, 'destroy'])->name('destroy');

                    Route::name('billing.')->group(function () {
                        Route::get('/admin/tenants/{tenant}/billing', [Admin\BillingController::class, 'show'])->name('show');
                    });

                    Route::post('/ploi/request-certificate/{tenant}/{domain}', [Admin\DomainController::class, 'requestCertificate'])->name('ploi.certificate.request');
                    Route::post('/ploi/revoke-certificate/{tenant}/{domain}', [Admin\DomainController::class, 'revokeCertificate'])->name('ploi.certificate.revoke');

                    Route::name('domain.')->group(function () {
                        Route::post('/domain/store/{tenant}', [Admin\DomainController::class, 'store'])->name('store');
                        Route::post('/domain/destroy/{domain}', [Admin\DomainController::class, 'destroy'])->name('destroy');
                        Route::post('/domain/make-primary/{domain}', [Admin\DomainController::class, 'makePrimary'])->name('make-primary');
                        Route::post('/domain/update-fallback/{tenant}', [Admin\DomainController::class, 'storeFallback'])->name('store-fallback');
                    });
                });
            });
        });
    });
}
