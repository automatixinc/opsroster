<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use Illuminate\Config\Repository;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class AuthGuardBootstrapper implements TenancyBootstrapper
{
    protected array|null $centralGuard = null;

    public function __construct(
        protected Repository $config,
    ) {}

    public function bootstrap(Tenant $tenant): void
    {
        $this->centralGuard = [
            'guard' => 'admin',
            'passwords' => 'admins',
        ];

        // Make sure the 'web' guard is the default guard in the tenant app
        $this->config->set('auth.defaults', [
            'guard' => 'web',
            'passwords' => 'users',
        ]);
    }

    public function revert(): void
    {
        // Make sure the 'admin' guard is the default guard in the central app
        $this->config->set('auth.defaults', $this->centralGuard);
    }
}
