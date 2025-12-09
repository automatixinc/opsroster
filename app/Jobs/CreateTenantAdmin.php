<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Str;

class CreateTenantAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Tenant $tenant,
    ) {}

    public function handle(): User
    {
        return $this->tenant->run(function ($tenant) {
            $data = $tenant->only(['name', 'email', 'password']);

            if (! array_filter($data)) {
                // Tenant is pending -- temporarily fill in dummy data
                $tenant->update([
                    'name' => $name = Str::random(),
                    'email' => $name . '@example.com',
                    'password' => Str::random(),
                ]);
            }

            $data = array_merge($tenant->only(['name', 'email', 'password']), [
                'password_confirmation' => $tenant->password,
                'terms' => true,
            ]);

            $user = app(CreatesNewUsers::class)->create($data);

            $tenant->update([
                // We don't need the name and password on the tenant anymore
                'name' => '',
                'password' => '',
            ]);

            return $user;
        });
    }
}
